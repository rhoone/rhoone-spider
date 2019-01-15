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

namespace rhoone\spider\destinations\file;

use yii\base\InvalidConfigException;

/**
 * File Destination
 * @package rhoone\spider\destinations\file
 */
class Destination extends \rhoone\spider\destinations\Destination
{
    /**
     * @var string The path where the download file is stored.
     * The end of the attribute should not contain a directory separator.
     */
    public $path = null;

    /**
     * @var string The file name where the downloaded content is saved.
     */
    public $filename = null;

    /**
     * @var string
     */
    public $filenameTemplate = null;

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if ($this->path === null)
        {
            $this->path = dirname(__FILE__);
        }
        if ($this->filenameTemplate === null)
        {
            $this->filenameTemplate = "{%$this->keyAttribute}.html";
        }
        if ($this->filename === null)
        {
            $this->filename = str_replace("{%$this->keyAttribute}", $this->key, $this->filenameTemplate);
        }
    }

    /**
     * Export content to specified file.
     * @param string $content
     * @return int|bool
     */
    public function export(string $content)
    {
        if (!is_string($this->filename) || empty($this->filename))
        {
            throw new InvalidConfigException("Filename not valid.");
        }
        return file_put_contents($this->path . DIRECTORY_SEPARATOR . $this->filename, $content);
    }
}