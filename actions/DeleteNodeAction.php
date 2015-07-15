<?php

namespace voskobovich\nestedsets\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use voskobovich\nestedsets\behaviors\NestedSetsBehavior;
use yii\db\ActiveRecord;
use yii\web\Response;

/**
 * Class MoveNodeAction
 * @package voskobovich\nestedsets\actions
 */
class DeleteNodeAction extends Action
{
    /**
     * Class to use to locate the supplied data ids
     * @var string
     */
    public $modelClass;

    /**
     * Behavior key in list all behaviors on model
     * @var string
     */
    public $behaviorName = 'nestedSetsBehavior';

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (null == $this->modelClass) {
            throw new InvalidConfigException('Param "modelClass" must be contain model name with namespace.');
        }
    }

    /**
     * Move a node (model) below the parent and in between left and right
     *
     * @param integer $id the primaryKey of the moved node
     * @return array
     * @throws InvalidConfigException
     */
    public function run($id)
    {
        /** @var ActiveRecord $model */
        $model = new $this->modelClass;
        /** @var NestedSetsBehavior $behavior */
        $behavior = $model->getBehavior($this->behaviorName);

        if ($behavior == null) {
            throw new InvalidConfigException('Behavior "' . $this->behaviorName . '" not found');
        }

        if (!$behavior instanceof NestedSetsBehavior) {
            throw new InvalidConfigException('Behavior must be implemented "voskobovich\nestedsets\behaviors\NestedSetsBehavior"');
        }

        /*
         * Locate the supplied model, left, right and parent models
         */
        $pkAttribute = $model->getTableSchema()->primaryKey[0];

        /** @var ActiveRecord|NestedSetsBehavior $currentModel */
        $currentModel = $model::find()->where([$pkAttribute => $id])->one();

        /*
         * Response will be in JSON format
         */
        Yii::$app->response->format = Response::FORMAT_JSON;

        /*
         * Report new position
         */
        return [
            'id' => $currentModel->getPrimaryKey(),
            'status' => $currentModel->deleteWithChildren() > 0
        ];
    }
}