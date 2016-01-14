<?php

namespace voskobovich\nestedsets\actions;

use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use voskobovich\nestedsets\behaviors\NestedSetsBehavior;
use yii\db\ActiveRecord;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * Class UpdateNodeAction
 * @package voskobovich\nestedsets\actions
 */
class UpdateNodeAction extends BaseAction
{
    /**
     * Attribute for name in model
     * @var string
     */
    public $nameAttribute = 'name';

    /**
     * Move a node (model) below the parent and in between left and right
     *
     * @param integer $id the primaryKey of the moved node
     * @return array
     * @throws InvalidConfigException
     * @throws Exception
     */
    public function run($id)
    {
        /** @var ActiveRecord $model */
        $model = new $this->modelClass;

        /*
         * Locate the supplied model, left, right and parent models
         */
        $pkAttribute = $model->getTableSchema()->primaryKey[0];

        /** @var ActiveRecord|NestedSetsBehavior $model */
        $model = $model::find()->where([$pkAttribute => $id])->one();

        if ($model == null) {
            throw new NotFoundHttpException('Node not found');
        }

        $name = Yii::$app->request->post('name');
        $model->{$this->nameAttribute} = $name;
        if (!$model->validate()) {
            throw new HttpException($model->getFirstError($this->nameAttribute));
        }
        $model->update(true, [$this->nameAttribute]);

        return null;
    }
}