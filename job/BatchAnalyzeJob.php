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
use yii\queue\RetryableJobInterface;

/**
 * Class BatchAnalyzeJob
 * @package rhoone\spider\job
 */
class BatchAnalyzeJob extends BaseObject implements RetryableJobInterface
{
    use DestinationTrait;

    /**
     * @var int the attempts limit. not recommended to be greater than 5.
     */
    public $attemptsLimit = 5;

    /**
     * @param \yii\queue\Queue $queue
     * @return int
     */
    public function execute($queue) : int
    {
        // TODO: Implement execute() method.
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
