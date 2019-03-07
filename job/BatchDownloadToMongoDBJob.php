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
 * Batch Download Job, which contains a batch of tasks to download.
 *
 * Basic Usage:
 * How to send a task into the queue:
 * ```php
 * Yii::$app->queue->push(new BatchDownloadJob([
 *     'urlTemplate' => 'https://blog.vistart.me/{%alias}/',
 *     'urlParameters' => [
 *         [ '{%alias}' => 'why-bitcoin-cannot-become-a-currency' ],
 *         ...
 *     ],
 * ]));
 * ```
 *
 * It is best not to exceed 1,000 or not less than 10 for each batch of job.
 * If there are too many tasks in a batch job, they will degenerate into serial tasks;
 * if there are too few tasks in a batch job, competing for job will consume a lot of resources.
 *
 * @package rhoone\spider\job
 */
class BatchDownloadToMongoDBJob extends BatchDownloadJob
{
    /**
     * @var null|string
     */
    public $destinationClass = Destination::class;

    /**
     * @var string
     */
    public $modelClass;

    /**
     * @param string $keyAttribute
     * @param $key
     * @param $modelClass
     * @param $result
     * @return int
     */
    protected function export(string $keyAttribute, $key, $modelClass, $result) : int
    {
        $this->destination = [
            'keyAttribute' => $keyAttribute,
            'key' => $key,
            'modelClass' => $modelClass,
        ];
        return $this->destination->export($result);
    }

    /**
     * @return int
     */
    protected function batchExport() : int
    {
        foreach ($this->results as $key => $result)
        {
            $this->export($this->keyAttribute, $key, $this->modelClass, $result);
        }
        return 0;
    }

    /**
     * @param \yii\queue\Queue $queue
     * @return int|void
     */
    public function execute($queue) : int
    {
        parent::execute($queue);
        return $this->batchExport();
    }
}