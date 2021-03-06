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

/**
 * Interface IDownloadedContent
 * @package rhoone\spider\destinations\mongodb
 */
interface IDownloadedContent
{
    /**
     * @param string $content the downloaded content to be saved.
     */
    public function setDownloadedContent(string $content);
}