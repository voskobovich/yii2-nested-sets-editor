<?php

namespace voskobovich\nestedsets\forms;

use yii\base\Model;
use Yii;
use yii\base\InvalidConfigException;
use voskobovich\nestedsets\behaviors\NestedSetsBehavior;
use yii\db\ActiveRecord;

/**
 * Class MoveNodeForm
 * @package voskobovich\nestedsets\forms
 */
class MoveNodeForm extends Model
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var integer
     */
    public $left;

    /**
     * @var integer
     */
    public $right;

    /**
     * @var integer
     */
    public $parent;

    /**
     * @return array
     */
    public function rules()
    {
        return [
            [['id', 'left', 'right', 'parent'], 'required']
        ];
    }

    /**
     * @param $modelClass
     * @param $behaviorName
     * @throws InvalidConfigException
     */
    public function moveNode($modelClass, $behaviorName)
    {
        /** @var ActiveRecord $model */
        $model = new $modelClass;
        /** @var NestedSetsBehavior $behavior */
        $behavior = $model->getBehavior($behaviorName);

        if ($behavior == null) {
            throw new InvalidConfigException('Behavior "' . $behaviorName . '" not found');
        }

        if (!$behavior instanceof NestedSetsBehavior) {
            throw new InvalidConfigException('Behavior must be implemented "voskobovich\nestedsets\behaviors\NestedSetsBehavior"');
        }

        /*
         * Locate the supplied model, left, right and parent models
         */
        $pkAttribute = $model->getTableSchema()->primaryKey[0];

        /** @var ActiveRecord|NestedSetsBehavior $currentModel */
        $currentModel = $model::find()->where([$pkAttribute => $this->id])->one();
        $lftModel = $model::find()->where([$pkAttribute => $this->left])->one();
        $rgtModel = $model::find()->where([$pkAttribute => $this->right])->one();
        $parentModel = $model::find()->where([$pkAttribute => $this->parent])->one();

        /*
         * Calculate the depth change
         */
        if (null == $parentModel) {
            $depthDelta = -1;
        } else if (null == ($parent = $currentModel->parents(1)->one())) {
            $depthDelta = 0;
        } else if ($parent->getPrimaryKey() != $parentModel->getPrimaryKey()) {
            $depthDelta = $parentModel->{$behavior->depthAttribute} - $currentModel->{$behavior->depthAttribute} + 1;
        } else {
            $depthDelta = 0;
        }

        /*
         * Calculate the left/right change
         */
        if (null == $lftModel) {
            $currentModel->moveNode((($parentModel ? $parentModel->{$behavior->leftAttribute} : 0) + 1), $depthDelta);
        } else if (null == $rgtModel) {
            $currentModel->moveNode((($lftModel ? $lftModel->{$behavior->rightAttribute} : 0) + 1), $depthDelta);
        } else {
            $currentModel->moveNode(($rgtModel ? $rgtModel->{$behavior->leftAttribute} : 0), $depthDelta);
        }
    }
}