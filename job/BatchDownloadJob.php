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
use yii\base\InvalidConfigException;
use yii\base\InvalidValueException;
use yii\di\Instance;
use yii\queue\Queue;
use yii\queue\RetryableJobInterface;

/**
 * Batch Download Job, which contains a batch of tasks to download.
 *
 * Basic Usage:
 * How to send a task into the queue:
 * ```php
 * Yii::$app->queue->push(new BatchDownloadJob([
 *     'urlTemplate' => 'https://blog.vistart.me/{%alias}/',
 *     'urlParameters' => [
 *         '<key>' => [ '{%alias}' => 'why-bitcoin-cannot-become-a-currency' ],
 *         ...
 *     ],
 * ]));
 * ```
 *
 * It is best not to exceed 1,000 or not less than 10 for each batch of job.
 * If there are too many tasks in a batch job, they will degenerate into serial tasks;
 * if there are too few tasks in a batch job, competing for job will consume a lot of resources.
 *
 * @property array|null $urls URLs to be downloaded.
 * @property-read int $urlsCount Get the count of urls in this batch.
 * @property null|string|array|Destination $destination Get or set the destination where the downloaded content is
 * exported.
 * @package rhoone\spider\job
 */
class BatchDownloadJob extends BaseObject implements RetryableJobInterface
{
    /**
     * @var int the attempts limit. not recommended to be greater than 5.
     */
    public $attemptsLimit = 5;

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
     * The first dimension of the array is the key of the download task.
     * The second dimension of the array is the list of parameters for the download task, and the array value is
     * the value of the parameter.
     * E.g. $urlParameters['0000000001']['{%marc_no}'] = ['0000000001'];
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
     * @var string the name of the attribute that refers to the key.
     */
    public $keyAttribute;

    /**
     * The destination where the content is saved.
     * The value of this property can be either the Destination model or an array describing the model.
     * If you don't want to save, please set it to null.
     * @var null|string
     */
    private $_destination = null;

    /**
     * The destination where the content is saved.
     * If you don't want to save, please set it to null.
     * @var null|array|Destination
     */
    public $destinationClass = null;

    /**
     * Set destination instance.
     * Resolves the specified reference into the actual destination model and makes sure it is of the specified
     * destination type.
     * @param null|string|array|Destination $destination
     */
    public function setDestination($destination)
    {
        try {
            $this->_destination = Instance::ensure($destination, $this->destinationClass);
        } catch (\Exception $ex) {
            file_put_contents("php://stderr", $ex->getMessage() . "\n");
        }
    }

    /**
     * Get destination instance.
     * @return Destination
     */
    public function getDestination()
    {
        return $this->_destination;
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
     * Get the number of the URLs.
     * @return int
     */
    public function getUrlsCount() : int
    {
        return count($this->urls);
    }

    /**
     * Get URLs.
     * If the URL template is null, return the property `url` array directly, otherwise call generateUrls().
     *
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
        if ($result === false)
        {
            throw new InvalidValueException("An error occured while downloading the page.");
        }
        file_put_contents("php://stdout", "  result[len: " . strlen($result) ."]\n");
        return $result;
    }

    /**
     * Batch download.
     * @return int
     */
    protected function batchDownload() : int
    {
        list($usec, $sec) = explode(" ", microtime());
        $start = ((float)$usec + (float)$sec);

        $total = 0;
        foreach ($this->urls as $key => $url)
        {
            try {
                $this->results[$key] = $this->download($url);
            } catch (InvalidValueException $ex) {
                // Record the current error in the log.
                file_put_contents("php://stderr", $ex->getMessage() ."]\n");
                continue;
            }
            $total++;
        }

        list($usec, $sec) = explode(" ", microtime());
        $duration = ((float)$usec + (float)$sec) - $start;

        file_put_contents("php://stdout", "result[$total task(s) finished. $duration second(s) elapsed.]\n\n");
        return 0;
    }

    /**
     * Execute the download process.
     * @param Queue $queue
     */
    public function execute($queue) : int
    {
        if (!is_array($this->results))
        {
            $this->results = [];
        }
        $this->batchDownload();
        return 0;
    }

    /**
     * @return int time to reserve in seconds
     */
    public function getTtr() : int
    {
        return $this->getUrlsCount() < 3 ? 3 : $this->getUrlsCount();
    }

    /**
     * @param int $attempt number
     * @param \Exception|\Throwable $error from last execute of the job
     * @return bool
     */
    public function canRetry($attempt, $error) : bool
    {
        return ($attempt < $this->attemptsLimit && $error instanceof \Exception);
    }
}
