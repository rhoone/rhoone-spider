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

use yii\base\BaseObject;
use yii\queue\RetryableJobInterface;

/**
 * Class DownloadJob
 * @package rhoone\spider\job
 */
class DownloadJob extends BaseObject implements RetryableJobInterface
{

}