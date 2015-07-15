<?php

namespace voskobovich\nestedsets\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use voskobovich\nestedsets\behaviors\NestedSetsBehavior;
use yii\db\ActiveRecord;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;

/**
 * Class CreateNodeAction
 * @package voskobovich\nestedsets\actions
 */
class CreateNodeAction extends Action
{
    /**
     * Class to use to locate the supplied data ids
     * @var string
     */
    public $modelClass;

    /**
     * Attribute for name in model
     * @var string
     */
    public $nameAttribute = 'name';

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
     * @return null
     * @throws HttpException
     */
    public function run()
    {
        $name = Yii::$app->request->post('name');

        /** @var ActiveRecord|NestedSetsBehavior $model */
        $model = new $this->modelClass;
        $model->{$this->nameAttribute} = $name;

        $roots = $model::find()->roots()->all();

        if (isset($roots[0])) {
            $model->appendTo($roots[0]);
        } else {
            $model->makeRoot();
        }

        return null;
    }
}