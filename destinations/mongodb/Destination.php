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

namespace rhoone\spider\destinations\mongodb;

use yii\mongodb\Connection;

/**
 * Class Destination
 * @package rhoone\spider\destinations\mongodb
 */
class Destination extends \rhoone\spider\destinations\Destination
{
    /**
     * @var string|array|Connection
     */
    public $mongo;

    /**
     * Export content to specified mongodb.
     * @param string $content
     * @return mixed|void
     */
    public function export(string $content)
    {
        // TODO: Implement export() method.
    }
}