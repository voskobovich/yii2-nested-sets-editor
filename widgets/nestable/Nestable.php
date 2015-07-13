<?php

namespace voskobovich\nestedsets\widgets\nestable;

use yii\base\Widget;
use yii\helpers\Html;
use yii\helpers\Json;

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
    public $id = NULL;

    /**
     * Модель данных
     * @var array
     */
    public $model = NULL;

    /**
     * Имя атрибута в котором хранятся данные
     * @var string
     */
    public $attribute = 'data';

    /**
     * @var array native Chosen plugin options.
     */
    public $options = [];

    /**
     * Структура меню в php array формате
     * @var array
     */
    private $_data = [];

    /**
     * Инициализация плагина
     */
    public function init()
    {
        parent::init();

        if (empty($this->id)) {
            $this->id = $this->getId();
        }

        if (!empty($this->model) && !empty($this->model->{$this->attribute})) {
            $this->_data = Json::decode($this->model->{$this->attribute});
        }
    }

    /**
     * Работаем!
     */
    public function run()
    {
        NestableAsset::register($this->getView());

        $view = $this->getView();

        $options = empty($this->options) ? '' : Json::encode($this->options);
        $view->registerJs("$('#{$this->id}').nestable({$options});");

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

        $this->actionButtons();
        $this->renderMenu();
        $this->actionButtons();
    }

    /**
     * Кнопки действий над виджетом
     */
    public function actionButtons()
    {
        echo Html::beginTag('div', ['class' => "{$this->id}-nestable-menu"]);

//        echo ButtonGroup::widget([
//            'buttons' => [
//                ['label' => 'Добавить пункт', 'options'=>['data-action'=>'create-item', 'class'=>'btn btn-default']],
//                ['label' => 'Закрыть все', 'options'=>['data-action'=>'collapse-all', 'class'=>'btn btn-default']],
//                ['label' => 'Открыть все', 'options'=>['data-action'=>'expand-all', 'class'=>'btn btn-default']],
//            ]
//        ]);

        echo Html::endTag('div');
    }

    /**
     * Вывод меню
     */
    private function renderMenu()
    {
        echo Html::beginTag('div', ['class' => 'dd-nestable', 'id' => $this->id]);

        $emptyItem = [
            ['id' => 0, 'name' => 'Новая ссылка', 'url' => '', 'bizrule' => '']
        ];

        $menu = (count($this->_data) > 0) ? $this->_data : $emptyItem;

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

        foreach ($level as $item)
            $this->printItem($item);

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
        $htmlOptions['data-url'] = !empty($item['url']) ? $item['url'] : '';
        $htmlOptions['data-bizrule'] = !empty($item['bizrule']) ? $item['bizrule'] : '';

        echo Html::beginTag('li', $htmlOptions);
        echo Html::beginTag('div', ['class' => 'dd-handle']);
        echo Html::endTag('div');
        echo Html::beginTag('div', ['class' => 'dd-content']);
        echo $item['name'];
        echo Html::endTag('div');

        if (isset($item['children']) && count($item['children']))
            $this->printLevel($item['children']);

        echo Html::endTag('li');
    }
}