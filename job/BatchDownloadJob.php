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

use yii\base\BaseObject;
use yii\queue\JobInterface;
use yii\queue\Queue;

/**
 * Batch Download Job, which contains a batch of tasks to download.
 *
 * Basic Usage:
 * How to send a task into the queue:
 * ```php
 * Yii::$app->queue->push(new BatchDownloadJob([
 *     'urlTemplate' => 'https://blog.vistart.me/{$alias}/',
 *     'urlParameters' => [
 *         [ '{$alias}' => 'why-bitcoin-cannot-become-a-currency' ],
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
     * @var array
     */
    public $results = [];

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
    protected function replace($template, $parameters)
    {
        $result = $template;
        foreach ($parameters as $key => $parameter)
        {
            $result = str_replace($key, $result, $parameter);
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
    public function setUrls($urls)
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
     * @param $filename
     * @param bool $use_include_path
     * @param null $context
     * @param int $offset
     * @param null $maxlen
     * @return false|string
     */
    protected function download($filename, $use_include_path = false, $context = null, $offset = 0, $maxlen = null)
    {
        return file_get_contents($filename, $use_include_path = false, $context = null, $offset = 0, $maxlen = null);
    }

    /**
     * @param Queue $queue
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
