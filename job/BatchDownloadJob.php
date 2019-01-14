<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.me/
 * @copyright Copyright (c) 2016 - 2019 vistart
 * @license https://vistart.me/license/
 */

namespace rhoone\spider\job;

use rhoone\spider\destinations\Destination;
use Yii;
use yii\base\BaseObject;
use yii\di\Instance;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * Batch Download Job, which contains a batch of tasks to download.
 *
 * Basic Usage:
 * How to send a task into the queue:
 * ```php
 * Yii::$app->queue->push(new BatchDownloadJob([
 *     'urlTemplate' => 'https://blog.vistart.me/{%alias}/',
 *     'urlParameters' => [
 *         [ '{%alias}' => 'why-bitcoin-cannot-become-a-currency' ],
 *     ],
 * ]));
 * ```
 *
 * It is best not to exceed 1,000 or not less than 10 for each batch of job.
 * If there are too many tasks in a batch job, they will degenerate into serial tasks;
 * if there are too few tasks in a batch job, competing for job will consume a lot of resources.
 *
 * @property array|null $urls URLs to be downloaded.
 * @package rhoone\spider\job
 */
class BatchDownloadJob extends BaseObject implements JobInterface
{
    /**
     * @var null|array URLs to be downloaded.
     */
    private $_urls = null;

    /**
     * @var null|string URL template to be replaced.
     * If you have URL list ready and don't want to use a template, you need to set this property to null.
     */
    public $urlTemplate = null;

    /**
     * @var array Parameters used to replace the template.
     */
    public $urlParameters = [];

    /**
     * @var array downloaded contents.
     * It is best to specify an explicit key for each array element, as the key will be used to uniquely identify the
     * downloaded content.
     * By default, the key of the result array element is the same as the key of the corresponding element of the URL
     * array. Therefore, you only need to guarantee the uniqueness of the key of the URL array element.
     */
    protected $results = [];

    /**
     * The destination where the content is saved.
     * If you don't want to save, please set it to null.
     * @var null|array|Destination
     */
    public $destination = null;

    /**
     * Initialize the Job.
     */
    public function init()
    {
        parent::init();
        $this->destination = Instance::ensure($this->destination, Destination::class);
    }
    /**
     * Replace the name in the template with the appropriate value.
     *
     * @param $template string the template to be replaced.
     * @param $parameters array the array of name-value pairs.
     * The format is as follows:
     * [
     *     'key1' => 'value1',
     *     'key2' => 'value2',
     *     ...
     * ]
     * It is best to name the "key" to be replaced with the criteria for "PHP variables" and wrap it in curly braces.
     * E.g: "{$title}"
     * @return string Replacement result.
     */
    protected function replace(string $template, array $parameters)
    {
        $result = $template;
        foreach ($parameters as $key => $parameter)
        {
            $result = str_replace($key, $parameter, $result);
        }
        return $result;
    }

    /**
     * Generate a list of URLs based on template and parameters.
     *
     * @return array|null Generated URL list.
     * The key of urls is the same as the key of the parameters.
     * If `urlTemplate` and `urls` both set to be null, null returned.
     */
    protected function generateUrls()
    {
        $this->_urls = [];
        foreach ($this->urlParameters as $key => $parameters)
        {
            $this->_urls[$key] = $this->replace($this->urlTemplate, $parameters);
        }
        return $this->_urls;
    }

    /**
     * Set URLs.
     * @param $urls
     */
    public function setUrls(array $urls)
    {
        $this->_urls = $urls;
    }

    /**
     * Get URLs.
     * @return array|null URLs to be downloaded.
     * If `urlTemplate` set to be null, property `urls` returned.
     */
    public function getUrls()
    {
        if ($this->urlTemplate === null)
        {
            return $this->_urls;
        }
        return $this->generateUrls();
    }

    /**
     * Download according to the specified URL.
     * @param $url The URL of the page to be downloaded.
     * @return false|string downloaded content.
     */
    protected function download(string $url)
    {
        file_put_contents("php://stdout", "filename: $url\n");
        $result = file_get_contents($url);
        file_put_contents("php://stdout", "result[len: " . strlen($result) ."]\n");
        return $result;
    }

    /**
     * Execute the download process.
     * @param Queue $queue
     * @return int
     */
    public function execute($queue)
    {
        if (!is_array($this->results))
        {
            $this->results = [];
        }
        foreach ($this->urls as $key => $url)
        {
            $this->results[$key] = $this->download($url);
        }
        return 0;
    }
}
