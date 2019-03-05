<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.name/
 * @copyright Copyright (c) 2016 vistart
 * @license https://vistart.name/license/
 */

namespace rhoone\spider\job;

use rhoone\spider\destinations\Destination;
use yii\base\BaseObject;
use yii\di\Instance;
use yii\queue\RetryableJobInterface;

/**
 * Class DownloadJob
 * This class is used to describe the single download process and is the base class for all other download jobs.
 * This class introduces a retryable mechanism. By default, you can retry up to 5 times and wait 3 seconds before each
 * retry. If you don't want to use the retryable mechanism, you can set `getTTR()` to return only `0`, and `canRetry()` to
 * return only `false`.
 * @property string|null $url Get or set url to be downloaded.
 * @property null|string|array|Destination $destination Get or set the destination where the downloaded content is
 * exported.
 * @package rhoone\spider\job
 */
class DownloadJob extends BaseObject implements RetryableJobInterface
{
    /**
     * @var int the attempts limit. not recommended to be greater than 5.
     */
    public $attemptsLimit = 5;

    /**
     * @var string the downloaded content.
     */
    protected $downloadedContent;

    /**
     * The destination where the content is saved.
     * The value of this property can be either the Destination model or an array describing the model.
     * If you don't want to save, please set it to null.
     * @var null|string
     */
    private $_destination = null;

    /**
     * @var null|string the name of destination class. If null, type check will not be performed.
     */
    public $destinationClass;

    /**
     * @var null|string URL to be downloaded.
     */
    private $_url = null;

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
     * @var string the key for the downloaded content.
     */
    public $key;

    /**
     * @var string the name of the attribute that refers to the key.
     */
    public $keyAttribute = 'key';

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
     * Generate a URL based on template and parameters.
     *
     * @return string|null Generated URL.
     * If `urlTemplate` and `url` both set to be null, null returned.
     */
    protected function generateUrl()
    {
        $this->_url = $this->replace($this->urlTemplate, $this->urlParameters);
        return $this->_url;
    }

    /**
     * Set URL.
     * @param string $url
     */
    public function setUrl(string $url)
    {
        $this->_url = $url;
    }

    /**
     * Get URL.
     * @return string|null URL to be downloaded.
     * If `urlTemplate` set to be null, property `url` returned.
     */
    public function getUrl()
    {
        if ($this->urlTemplate === null)
        {
            return $this->_url;
        }
        return $this->generateUrl();
    }

    /**
     * Set destination instance.
     * Resolves the specified reference into the actual destination model and makes sure it is of the specified
     * destination type.
     * @param null|string|array|Destination $destination
     */
    public function setDestination($destination)
    {
        $this->_destination = Instance::ensure($destination, $this->destinationClass);
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
     * Download according to the specified URL.
     * @param string $url The URL of the page to be downloaded.
     * @return false|string downloaded content.
     */
    protected function download(string $url)
    {
        file_put_contents("php://stdout", "filename: $url\n");
        $downloadedContent = file_get_contents($url);
        file_put_contents("php://stdout", "result[len: " . strlen($downloadedContent) ."]\n");
        return $downloadedContent;
    }

    /**
     * Execute the download process.
     * @param \yii\queue\Queue $queue
     * @return int
     */
    public function execute($queue)
    {
        $this->downloadedContent = $this->download($this->generateUrl());
        return 0;
    }

    /**
     * Get TTR.
     * @return int
     */
    public function getTtr()
    {
        return 3;
    }

    /**
     * Determine if it could retry.
     * @param int $attempt
     * @param \Exception|\Throwable $error
     * @return bool|void
     */
    public function canRetry($attempt, $error)
    {
        return ($attempt < $this->attemptsLimit && $error instanceof \Exception);
    }
}
