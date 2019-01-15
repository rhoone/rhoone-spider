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
 * @property string|null $url
 * @property null|string|array|Destination $destination
 * @package rhoone\spider\job
 */
class DownloadJob extends BaseObject implements RetryableJobInterface
{
    /**
     * @var int
     */
    public $attemptsLimit = 5;

    /**
     * @var string
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
     * @var null|string|Destination
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
     * @var string The key for the downloaded content.
     */
    public $key;

    /**
     * @var string
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
     * @param $destination
     */
    public function setDestination($destination)
    {
        $this->_destination = Instance::ensure($destination, $this->destinationClass);
    }

    /**
     * @return string|null
     */
    public function getDestination()
    {
        return $this->_destination;
    }

    /**
     * Download according to the specified URL.
     * @param string $url The URL of the page to be downloaded.
     * @param bool $use_include_path
     * @param null $context
     * @param int $offset
     * @param null $maxlen
     * @return false|string downloaded content.
     */
    protected function download(string $url)
    {
        file_put_contents("php://stdout", "filename: $url\n");
        $downloadedContent = file_get_contents($url);
        file_put_contents("php://stdout", "result[len: " . strlen($result) ."]\n");
        return $downloadedContent;
    }

    /**
     * @param \yii\queue\Queue $queue
     * @return int
     */
    public function execute($queue)
    {
        $this->downloadedContent = $this->download($this->generateUrl());
        return 0;
    }

    /**
     * @return int
     */
    public function getTtr()
    {
        return 3;
    }

    /**
     * @param int $attempt
     * @param \Exception|\Throwable $error
     * @return bool|void
     */
    public function canRetry($attempt, $error)
    {
        return ($attempt < $this->attemptsLimit && $error instanceof \Exception);
    }
}
