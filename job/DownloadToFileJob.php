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

use rhoone\spider\destinations\file\Destination;
use yii\base\InvalidConfigException;
use yii\di\Instance;

/**
 * Class DownloadToFileJob
 * @property null|string|array|Destination $destination
 * @package rhoone\spider\job
 */
class DownloadToFileJob extends DownloadJob
{
    /**
     * @var null|string The destination where the downloaded content will be
     * saved.
     */
    public $destinationClass = Destination::class;

    /**
     * @var string File name template.
     */
    public $filenameTemplate = null;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->filenameTemplate === null) {
            $this->filenameTemplate = "{%$this->keyAttribute}.html";
        }
    }

    /**
     * Execute the download process.
     * @param \yii\queue\Queue $queue
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    public function execute($queue)
    {
        parent::execute($queue);
        $this->destination = [
            'keyAttribute' => $this->keyAttribute,
            'key' => $this->key,
            'filenameTemplate' => $this->filenameTemplate,
        ];
        return (strlen($this->downloadedContent) == $this->destination->export($this->downloadedContent)) ? 0 : 1;
    }
}