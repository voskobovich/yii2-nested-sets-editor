<?php

namespace voskobovich\nestedsets\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use voskobovich\nestedsets\behaviors\NestedSetsBehavior;
use yii\db\ActiveRecord;
use yii\web\Response;

/**
 * Class NodeMoveAction
 * @package voskobovich\nestedsets\actions
 */
class NodeMoveAction extends Action
{
    /**
     * Class to use to locate the supplied data ids
     * @var string
     */
    public $modelClass;

    /**
     * @var string
     */
    public $behaviorName;

    /**
     * Move a node (model) below the parent and in between left and right
     *
     * @param integer $id the primaryKey of the moved node
     * @param integer $lft the primaryKey of the node left of the moved node
     * @param integer $rgt the primaryKey of the node right to the moved node
     * @param integer $par the primaryKey of the parent of the moved node
     * @return array
     * @throws InvalidConfigException
     */
    public function run($id = 0, $lft = 0, $rgt = 0, $par = 0)
    {
        if (null == $this->modelClass) {
            throw new InvalidConfigException("No 'modelClass' supplied on action initialization.");
        }

        if (null == $this->behaviorName) {
            throw new InvalidConfigException("No 'behaviorName' supplied on action initialization.");
        }

        /** @var ActiveRecord $model */
        $model = new $this->modelClass;
        /** @var NestedSetsBehavior $behavior */
        $behavior = $model->getBehavior($this->behaviorName);

        if ($behavior == null) {
            throw new InvalidConfigException("No 'behaviorName' supplied on action initialization.");
        }

        /* Locate the supplied model, left, right and parent models */
        $currentModel = Yii::createObject($this->modelClass)->where(['id' => $id])->one();
        $lftModel = Yii::createObject($this->modelClass)->where(['id' => $lft])->one();
        $rgtModel = Yii::createObject($this->modelClass)->where(['id' => $rgt])->one();
        $parentModel = Yii::createObject($this->modelClass)->where(['id' => $par])->one();

        /* Calculate the depth change */
        if (null == $parentModel) {
            $depthDelta = -1;
        } else if (null == ($parent = $currentModel->parents(1)->one())) {
            $depthDelta = 0;
        } else if ($parent->id != $parentModel->id) {
            $depthDelta = $parentModel->{$behavior->depthAttribute} - $currentModel->{$behavior->depthAttribute} + 1;
        } else {
            $depthDelta = 0;
        }

        /* Calculate the left/right change */
        if (null == $lftModel) {
            $currentModel->nodeMove((($parentModel ? $parentModel->{$behavior->leftAttribute} : 0) + 1), $depthDelta);
        } else if (null == $rgtModel) {
            $currentModel->nodeMove((($lftModel ? $lftModel->{$behavior->rightAttribute} : 0) + 1), $depthDelta);
        } else {
            $currentModel->nodeMove(($rgtModel ? $rgtModel->{$behavior->leftAttribute} : 0), $depthDelta);
        }

        /* Response will be in JSON format */
        Yii::$app->response->format = Response::FORMAT_JSON;

        /* Report new position */
        return [
            'updated' => [
                'id' => $currentModel->id,
                'depth' => $currentModel->{$behavior->depthAttribute},
                'lft' => $currentModel->{$behavior->leftAttribute},
                'rgt' => $currentModel->{$behavior->rightAttribute},
            ]
        ];
    }
}