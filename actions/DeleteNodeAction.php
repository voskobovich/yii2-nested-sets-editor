<?php

namespace voskobovich\nestedsets\actions;

use Yii;
use voskobovich\nestedsets\behaviors\NestedSetsBehavior;
use yii\db\ActiveRecord;
use yii\web\NotFoundHttpException;

/**
 * Class DeleteNodeAction
 * @package voskobovich\nestedsets\actions
 */
class DeleteNodeAction extends BaseAction
{
    /**
     * Move a node (model) below the parent and in between left and right
     *
     * @param integer $id the primaryKey of the moved node
     * @return array
     * @throws NotFoundHttpException
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

        $model->deleteWithChildren();

        return null;
    }
}