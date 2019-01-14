<?php

/**
 *  _   __ __ _____ _____ ___  ____  _____
 * | | / // // ___//_  _//   ||  __||_   _|
 * | |/ // /(__  )  / / / /| || |     | |
 * |___//_//____/  /_/ /_/ |_||_|     |_|
 * @link https://vistart.name/
 * @copyright Copyright (c) 2016 - 2019 vistart
 * @license https://vistart.name/license/
 */

namespace rhoone\spider\job;

use rhoone\spider\destinations\file\Destination;

/**
 * Class BatchDownloadToFileJob
 * @package rhoone\spider\job
 */
class BatchDownloadToFileJob extends BatchDownloadJob
{
    /**
     * @var array|Destination The destination where the downloaded content will be saved.
     */
    public $destination;

    /**
     * @var string File name template.
     */
    public $filenameTemplate = '{%key}.html';

    /**
     * Execute the download process.
     * @param \yii\queue\Queue $queue
     * @return int
     * @throws \yii\base\InvalidConfigException
     */
    public function execute($queue)
    {
        parent::execute($queue);
        foreach ($this->results as $key => $result)
        {
            $this->destination->filename = $this->replace($this->filenameTemplate, ['{%key}' => $key]);
            $this->destination->export($result);
        }
        return 0;
    }
}