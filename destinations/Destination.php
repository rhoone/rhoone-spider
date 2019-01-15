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

namespace rhoone\spider\destinations;

use yii\base\BaseObject;

/**
 * Abstract Class Destination
 * @package rhoone\spider\destinations
 */
abstract class Destination extends BaseObject
{
    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $keyAttribute;

    /**
     * Export content to specified destination.
     * @param string $content the content to be exported.
     * @return mixed result.
     */
    abstract public function export(string $content);
}