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

use rhoone\spider\destinations\IDownloadedContent;
use rhosocial\base\models\models\BaseMongoEntityModel;
use rhosocial\base\models\queries\BaseEntityQuery;
use yii\base\InvalidArgumentException;
use yii\di\Instance;
use yii\mongodb\Connection;

/**
 * Class Destination
 * @property IDownloadedContent $downloadedContent
 * @package rhoone\spider\destinations\mongodb
 */
class Destination extends \rhoone\spider\destinations\Destination
{
    /**
     * @var string|array|Connection
     */
    public $db = 'mongodb';

    /**
     * @var string
     */
    public $downloadedContentClass;

    /**
     * @var
     */
    private $_downloadedContent;

    /**
     * @var
     */
    public $downloadedContentKey;

    /**
     * @var string
     */
    public $downloadedContentKeyAttribute = 'key';

    /**
     * @var string
     */
    public $marcNoClass;

    /**
     * @var
     */
    private $_marcNo;

    /**
     * @var
     */
    public $marcNoKey;

    /**
     * @var string
     */
    public $marcNoKeyAttribute = 'key';

    /**
     * @var string
     */
    public $marcInfoClass;

    /**
     * @var
     */
    private $_marcInfo;

    /**
     * @var
     */
    public $marcInfoKey;

    /**
     * @var string
     */
    public $marcInfoKeyAttribute = 'key';

    /**
     * @var string
     */
    public $marcCopyClass;

    /**
     * @var
     */
    private $_marcCopy;

    /**
     * @var
     */
    public $marcCopyKey;

    /**
     * @var string
     */
    public $marcCopyKeyAttribute = 'key';

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
    }

    /**
     * @param string $name
     * @return mixed|IDownloadedContent
     * @throws \yii\base\UnknownPropertyException
     */
    public function __get($name)
    {
        if (in_array(strtolower($name), ['downloadedcontent', 'marcno', 'marcinfo', 'marccopy'])) {
            $keyAttribute = $name . 'KeyAttribute';
            $key = $name . 'Key';
            $model = $this->getModel($name, $this->{$keyAttribute}, $this->{$key});
            return $model;
        }
        return parent::__get($name);
    }

    /**
     * @param string $name
     * @param mixed $value
     * @throws \yii\base\UnknownPropertyException
     */
    public function __set($name, $value)
    {
        if (in_array(strtolower($name), ['downloadedcontent', 'marcno', 'marcinfo', 'marccopy'])) {
            return $this->setModel($modelClass, $value);
        }
        return parent::__set($name, $value);
    }

    /**
     * @param string $modelClass
     * @param string $keyAttribute
     * @param $key
     * @return mixed
     */
    public function getModel(string $modelClass, string $keyAttribute, $key)
    {
        if ($this->{'_' . $modelClass} == null) {
            $this->{'_' . $modelClass} = $this->findOrCreateOne($this->{$modelClass . 'Class'}, $keyAttribute, $key);
        }
        return $this->{'_' . $modelClass};
    }

    /**
     * @param $model mixed
     */
    public function setModel($modelClass, $model)
    {
        $this->{'_' . $modelClass} = $model;
    }

    /**
     * @param string $class
     * @param string $keyAttribute
     * @param mixed $key
     * @return mixed
     */
    protected function findOrCreateOne(string $class, string $keyAttribute, $key)
    {
        if ($keyAttribute == NULL) {
            throw new InvalidArgumentException("Key attribute not specified.");
        }
        if ($key == NULL) {
            throw new InvalidArgumentException("Scalar value required, null given.");
        }
        $model = $class::find()->where([$keyAttribute => $key])->one();
        if (!$model) {
            $model = new $class([$keyAttribute => $key]);
        }
        return $model;
    }

    /**
     * Export content to specified mongodb.
     * @param string $content
     * @return mixed|void
     */
    public function export(string $content)
    {
        $this->downloadedContent->setDownloadedContent($content);
        return $this->downloadedContent->save();
    }
}
