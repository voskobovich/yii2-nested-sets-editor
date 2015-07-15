<?php

namespace voskobovich\nestedsets\actions;

use voskobovich\nestedsets\forms\MoveNodeForm;
use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\web\BadRequestHttpException;
use yii\web\HttpException;

/**
 * Class MoveNodeAction
 * @package voskobovich\nestedsets\actions
 */
class MoveNodeAction extends Action
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
     * @throws HttpException
     * @throws InvalidConfigException
     */
    public function run($id)
    {
        $params = Yii::$app->request->post();

        $form = new MoveNodeForm();
        $form->id = $id;
        $form->setAttributes($params);

        if (!$form->validate()) {
            throw new BadRequestHttpException();
        }

        $form->moveNode($this->modelClass, $this->behaviorName);

        return null;
    }
}