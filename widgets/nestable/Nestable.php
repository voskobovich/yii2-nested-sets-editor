<?php

namespace voskobovich\nestedsets\widgets\nestable;

use voskobovich\nestedsets\behaviors\NestedSetsBehavior;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\bootstrap\ButtonGroup;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\helpers\Url;

/**
 * Class Nestable
 * @package voskobovich\nestedsets\widgets
 */
class Nestable extends Widget
{
    /**
     * Идентификатор виджета
     * @var string
     */
    public $id;

    /**
     * Модель данных
     * @var array
     */
    public $modelClass;

    /**
     * Behavior key in list all behaviors on model
     * @var string
     */
    public $behaviorName;

    /**
     * @var array.
     */
    public $pluginOptions = [];

    /**
     * Url to MoveNodeAction
     * @var string
     */
    public $moveUrl;

    /**
     * Url to CreateNodeAction
     * @var string
     */
    public $createUrl;

    /**
     * Url to UpdateNodeAction
     * @var string
     */
    public $updateUrl;

    /**
     * Url to DeleteNodeAction
     * @var string
     */
    public $deleteUrl;

    /**
     * Структура меню в php array формате
     * @var array
     */
    private $_items = [];

    /**
     * Инициализация плагина
     */
    public function init()
    {
        parent::init();

        if (empty($this->id)) {
            $this->id = $this->getId();
        }

        if ($this->modelClass == null) {
            throw new InvalidConfigException('Param "modelClass" must be contain model name');
        }

        if (null == $this->behaviorName) {
            throw new InvalidConfigException("No 'behaviorName' supplied on action initialization.");
        }

        /** @var ActiveRecord $model */
        $model = new $this->modelClass;
        /** @var NestedSetsBehavior $behavior */
        $behavior = $model->getBehavior($this->behaviorName);

        $items = $model::find()
            ->orderBy([$behavior->leftAttribute => SORT_ASC])
            ->asArray()
            ->all();
        $this->_items = $this->prepareItems($items);
    }

    /**
     * @param $items
     * @return array
     */
    private function prepareItems($items)
    {
        $stack = [];
        $arraySet = [];

        foreach ($items as $intKey => $arrValues) {
            $stackSize = count($stack);
            while ($stackSize > 0 && $stack[$stackSize - 1]['rgt'] < $arrValues['lft']) {
                array_pop($stack);
                $stackSize--;
            }

            $link =& $arraySet;
            for ($i = 0; $i < $stackSize; $i++) {
                $link =& $link[$stack[$i]['index']]['children']; //navigate to the proper children array
            }
            $tmp = array_push($link, [
                'id' => $arrValues['id'],
                'name' => $arrValues['name'],
                'children' => []
            ]);
            array_push($stack, [
                'index' => $tmp - 1,
                'rgt' => $arrValues['rgt']
            ]);
        }

        return $arraySet;
    }

    /**
     * Работаем!
     */
    public function run()
    {
        $this->registerAssets();

        $this->actionButtons();
        $this->renderMenu();
        $this->actionButtons();
    }

    /**
     * Register Asset manager
     */
    private function registerAssets()
    {
        NestableAsset::register($this->getView());

        $view = $this->getView();

        $pluginOptions = ArrayHelper::merge($this->pluginOptions, $this->getDefaultPluginOptions());
        $pluginOptions = Json::encode($pluginOptions);
        $view->registerJs("$('#{$this->id}').nestable({$pluginOptions});");

        $view->registerJs("
			$('.{$this->id}-nestable-menu').on('click', function(e) {
				var target = $(e.target), action = target.data('action');

				switch (action) {
					case 'expand-all': $('#{$this->id}').nestable('expandAll');
						break;
					case 'collapse-all': $('#{$this->id}').nestable('collapseAll');
						break;
					case 'create-item': $('#{$this->id}').nestable('createItem');
				}

				return false;
			});
		");
    }

    /**
     * Generate default plugin options
     * @return array
     */
    private function getDefaultPluginOptions()
    {
        $options = [
            'moveUrl' => $this->moveUrl,
            'createUrl' => $this->createUrl,
            'updateUrl' => $this->updateUrl,
            'deleteUrl' => $this->deleteUrl,
        ];

        $controller = Yii::$app->controller;
        if ($controller) {
            if (!$options['moveUrl']) {
                $options['moveUrl'] = Url::to(["{$controller->id}/moveNode"]);
            }
            if (!$options['createUrl']) {
                $options['createUrl'] = Url::to(["{$controller->id}/createNode"]);
            }
            if (!$options['updateUrl']) {
                $options['updateUrl'] = Url::to(["{$controller->id}/updateNode"]);
            }
            if (!$options['deleteUrl']) {
                $options['deleteUrl'] = Url::to(["{$controller->id}/deleteNode"]);
            }
        }

        return $options;
    }

    /**
     * Кнопки действий над виджетом
     */
    public function actionButtons()
    {
        echo Html::beginTag('div', ['class' => "{$this->id}-nestable-menu"]);

        echo ButtonGroup::widget([
            'buttons' => [
                ['label' => 'Добавить пункт', 'options' => ['data-action' => 'create-item', 'class' => 'btn btn-success']],
                ['label' => 'Закрыть все', 'options' => ['data-action' => 'collapse-all', 'class' => 'btn btn-default']],
                ['label' => 'Открыть все', 'options' => ['data-action' => 'expand-all', 'class' => 'btn btn-default']],
            ]
        ]);

        echo Html::endTag('div');
    }

    /**
     * Вывод меню
     */
    private function renderMenu()
    {
        echo Html::beginTag('div', ['class' => 'dd-nestable', 'id' => $this->id]);

        $emptyItem = [
            ['id' => 0, 'name' => 'Новая ссылка']
        ];

        $menu = (count($this->_items) > 0) ? $this->_items : $emptyItem;

        $this->printLevel($menu);

        echo Html::endTag('div');
    }

    /**
     * Распечатка одного уровня
     * @param $level
     */
    private function printLevel($level)
    {
        echo Html::beginTag('ol', ['class' => 'dd-list']);

        foreach ($level as $item) {
            $this->printItem($item);
        }

        echo Html::endTag('ol');
    }

    /**
     * Распечатка одного пункта
     * @param $item
     */
    private function printItem($item)
    {
        $htmlOptions = ['class' => 'dd-item'];
        $htmlOptions['data-id'] = !empty($item['id']) ? $item['id'] : '';
        $htmlOptions['data-name'] = !empty($item['name']) ? $item['name'] : '';

        echo Html::beginTag('li', $htmlOptions);
        echo Html::beginTag('div', ['class' => 'dd-handle']);
        echo Html::endTag('div');
        echo Html::beginTag('div', ['class' => 'dd-content']);
        echo $item['name'];
        echo Html::endTag('div');

        if (isset($item['children']) && count($item['children'])) {
            $this->printLevel($item['children']);
        }

        echo Html::endTag('li');
    }
}