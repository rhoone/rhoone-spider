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

/**
 * Class BatchIndexJob
 * @package rhoone\spider\job
 */
class BatchIndexJob extends BaseObject implements JobInterface
{
    use DestinationTrait;

    /**
     * @param $model
     * @return int
     */
    public function index($model) : int
    {

    }

    /**
     * @return int
     */
    public function batchIndex() : int
    {

    }

    /**
     * @param \yii\queue\Queue $queue
     * @return int
     */
    public function execute($queue) : int
    {
        return $this->batchIndex();
    }
}
