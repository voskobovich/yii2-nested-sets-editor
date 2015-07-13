<?php

namespace voskobovich\nestedsets\actions;

use Yii;
use yii\base\Action;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use voskobovich\nestedsets\behaviors\NestedSetsBehavior;

/**
 * Class NodeMoveAction
 * @package voskobovich\nestedsets\actions
 */
class NodeMoveAction extends Action
{
    /** @var string class to use to locate the supplied data ids */
    public $modelClass;

    /** @vars string the attribute names of the model that hold these attributes */
    private $leftAttribute;
    private $rightAttribute;
    private $treeAttribute;
    private $depthAttribute;

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

        /* response will be in JSON format */
        Yii::$app->response->format = 'json';

        /* Locate the supplied model, left, right and parent models */
        $model = Yii::createObject(ActiveQuery::className(), [$this->modelClass])->where(['id' => $id])->one();
        $lft = Yii::createObject(ActiveQuery::className(), [$this->modelClass])->where(['id' => $lft])->one();
        $rgt = Yii::createObject(ActiveQuery::className(), [$this->modelClass])->where(['id' => $rgt])->one();
        $par = Yii::createObject(ActiveQuery::className(), [$this->modelClass])->where(['id' => $par])->one();

        /* Get attribute names from model behaviour config */
        foreach ($model->behaviors as $behavior) {
            if ($behavior instanceof NestedSetsBehavior) {
                $this->leftAttribute = $behavior->leftAttribute;
                $this->rightAttribute = $behavior->rightAttribute;
                $this->treeAttribute = $behavior->treeAttribute;
                $this->depthAttribute = $behavior->depthAttribute;
                break;
            }
        }

        /* attach our bahaviour to be able to call the moveNode() function of the NestedSetsBehavior */
        $model->attachBehavior('nestable', [
            'class' => NestedSetsBehavior::className(),
            'leftAttribute' => $this->leftAttribute,
            'rightAttribute' => $this->rightAttribute,
            'treeAttribute' => $this->treeAttribute,
            'depthAttribute' => $this->depthAttribute,
        ]);

        /* Calculate the depth change */
        if (null == $par) {
            $depthDelta = -1;
        } else if (null == ($parent = $model->parents(1)->one())) {
            $depthDelta = 0;
        } else if ($parent->id != $par->id) {
            $depthDelta = $par->{$this->depthAttribute} - $model->{$this->depthAttribute} + 1;
        } else {
            $depthDelta = 0;
        }

        /* Calculate the left/right change */
        if (null == $lft) {
            $model->nodeMove((($par ? $par->{$this->leftAttribute} : 0) + 1), $depthDelta);
        } else if (null == $rgt) {
            $model->nodeMove((($lft ? $lft->{$this->rightAttribute} : 0) + 1), $depthDelta);
        } else {
            $model->nodeMove(($rgt ? $rgt->{$this->leftAttribute} : 0), $depthDelta);
        }

        /* report new position */
        return [
            'updated' => [
                'id' => $model->id,
                'depth' => $model->{$this->depthAttribute},
                'lft' => $model->{$this->leftAttribute},
                'rgt' => $model->{$this->rightAttribute},
            ]
        ];
    }
}