<?php

namespace voskobovich\nestedsets\widgets\nestable;

use yii\web\AssetBundle;

/**
 * Class NestableAsset
 * @package voskobovich\nestedsets\widgets
 */
class NestableAsset extends AssetBundle
{
    /**
     * @var string
     */
    public $sourcePath = '@vendor/voskobovich/yii2-nested-sets-editor/src/widgets/nestable/assets';

    /**
     * @var array
     */
    public $css = [
        'jquery.nestable.css'
    ];

    /**
     * @var array
     */
    public $js = [
        'jquery.nestable.js'
    ];

    /**
     * @var array
     */
    public $depends = [
        'yii\web\YiiAsset',
    ];
}