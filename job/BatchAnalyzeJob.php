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
     * @var array
     * $htmls['<key>'] = '<html>';
     */
    public $htmls;

    /**
     * @var array
     */
    public $results;

    /**
     * @param string $html
     */
    public function analyze(string $html)
    {
        throw new NotSupportedException("This method has not been implemented yet. Please implement your analysis method.");
    }

    /**
     * @return int
     */
    public function batchAnalyze() : int
    {
        $this->results = [];
        foreach ($this->htmls as $key => $html)
        {
            $this->results[$key] = $this->analyze($html);
        }
        return 0;
    }

    /**
     * @param \yii\queue\Queue $queue
     * @return int
     */
    public function execute($queue) : int
    {
        list($usec, $sec) = explode(" ", microtime());
        $start = ((float)$usec + (float)$sec);
        try {
            $this->batchAnalyze();
        } catch (\Exception $ex) {
            file_put_contents("php://stderr", $ex->getMessage() . "\n");
        }
        list($usec, $sec) = explode(" ", microtime());
        $duration = ((float)$usec + (float)$sec) - $start;

        file_put_contents("php://stdout", "result[$duration second(s) elapsed.]\n\n");
        return 0;
    }

    /**
     * @return int time to reserve in seconds
     */
    public function getTtr() : int
    {
        return $this->htmls < 3 ? 3 : count($this->htmls);
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
