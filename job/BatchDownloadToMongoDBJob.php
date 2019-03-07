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

use rhoone\spider\destinations\mongodb\Destination;

/**
 * Class BatchDownloadToMongoDBJob
 * @package rhoone\spider\job
 */
class BatchDownloadToMongoDBJob extends BatchDownloadJob
{
    /**
     * The destination where the content is saved.
     * If you don't want to save, please set it to null.
     * @var null|array|Destination
     */
    public $destinationClass = Destination::class;

    /**
     * @var null|string
     */
    public $modelClass = null;

    /**
     * @param \yii\queue\Queue $queue
     * @return int|void
     */
    public function execute($queue)
    {
        parent::execute($queue);

        foreach ($this->urls as $key => $url)
        {
            $this->destination = [
                'keyAttribute' => $this->keyAttribute,
                'key' => $this->key,
                'modelClass' => $this->modelClass,
            ];
            $this->destination->export($this->results[$key]);
        }
    }
}