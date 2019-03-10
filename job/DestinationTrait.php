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

use yii\di\Instance;
use rhoone\spider\destinations\Destination;

/**
 * Trait DestinationTrait
 * @property null|string|array|Destination $destination Get or set the destination where the downloaded content is
 * @package rhoone\spider\job
 */
trait DestinationTrait
{
    /**
     * @var string
     */
    public $destinationClass;

    /**
     * The destination where the content is saved.
     * If you don't want to save, please set it to null.
     * @var null|array|Destination
     */
    private $_destination;

    /**
     * Set destination instance.
     * Resolves the specified reference into the actual destination model and makes sure it is of the specified
     * destination type.
     * @param null|string|array|Destination $destination
     */
    public function setDestination($destination)
    {
        try {
            $this->_destination = Instance::ensure($destination, $this->destinationClass);
        } catch (\Exception $ex) {
            file_put_contents("php://stderr", $ex->getMessage() . "\n");
        }
    }

    /**
     * Get destination instance.
     * @return Destination
     */
    public function getDestination()
    {
        return $this->_destination;
    }
}
