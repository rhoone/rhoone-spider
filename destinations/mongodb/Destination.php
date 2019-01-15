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

use rhoone\spider\destinations\IDestinationModel;
use rhosocial\base\models\models\BaseMongoEntityModel;
use rhosocial\base\models\queries\BaseEntityQuery;
use yii\di\Instance;
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
    public $db = 'mongodb';

    /**
     * @var string the class of destination model implemented the `setDownloadedContent()` method.
     */
    public $modelClass;

    /**
     * @var IDestinationModel
     */
    public $model;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $keyAttribute = 'key';

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {
        parent::init();
        $this->db = Instance::ensure($this->db, Connection::class);
        $this->prepareDestinationModel();
    }

    /**
     *
     */
    protected function prepareDestinationModel()
    {
        $this->model = $this->findOrCreateOne($this->modelClass, $this->keyAttribute, $this->key);
    }

    /**
     * @param string $class
     * @param string $keyAttribute
     * @param mixed $key
     * @return IDestinationModel
     */
    protected function findOrCreateOne(string $class, string $keyAttribute, $key)
    {
        $model = $class::find()->where([$keyAttribute => $key])->one();
        /* @var $model IDestinationModel */
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
        $this->model->setDownloadedContent($content);
        return $this->model->save();
    }
}