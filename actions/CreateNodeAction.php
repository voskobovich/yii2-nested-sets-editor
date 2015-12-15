<?php

namespace voskobovich\nestedsets\actions;

use Yii;
use yii\base\InvalidConfigException;
use voskobovich\nestedsets\behaviors\NestedSetsBehavior;
use yii\db\ActiveRecord;
use yii\web\HttpException;

/**
 * Class CreateNodeAction
 * @package voskobovich\nestedsets\actions
 */
class CreateNodeAction extends BaseAction
{
    /**
     * Attribute for name in model
     * @var string
     */
    public $nameAttribute = 'name';

    /**
     * @return null
     * @throws HttpException
     */
    public function run()
    {
        $post = Yii::$app->request->post();

        /** @var ActiveRecord|NestedSetsBehavior $model */
        $model = new $this->modelClass;
        $model->load($post);

        if ($model->validate()) {
            $roots = $model::find()->roots()->all();

            if (isset($roots[0])) {
                $model->appendTo($roots[0]);
            } else {
                $model->makeRoot();
            }
        }

        return null;
    }
}