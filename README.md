# Nested Sets Editor for Yii2
Editor nested set using jquery.nestable plugin.

Реализует полный набор CRUD операций для узлов дерева.

Installation
-------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist voskobovich/yii2-nested-sets-editor "*"
```

or add

```
"voskobovich/yii2-nested-sets-editor": "*"
```

to the require section of your `composer.json` file.


Внимание!
-----
В расширении наследуется и расширяется behavior [Nested Sets Behavior for Yii 2](https://github.com/creocoder/yii2-nested-sets).  
Всю информацию по настройке воедения можно взять на [странице](https://github.com/creocoder/yii2-nested-sets) поведения.

Но для работы виджета нужно использовать реализацию поведения из этого пакета!


Usage
-----
1. Подключите behavior из этого пакета к своей модели и сконфигурируйте как сказано в [документации](https://github.com/creocoder/yii2-nested-sets).  
```
/**
 * @return array
 */
public function behaviors()
{
    return [
        'nestedSetsBehavior' => 'voskobovich\nestedsets\behaviors\NestedSetsBehavior',
    ];
}
```

2. Подключите в контроллер дополнительные actions  
```
/**
 * @return array
 */
public function actions()
{
    return [
        'moveNode' => [
            'class' => 'voskobovich\nestedsets\actions\MoveNodeAction',
            'modelClass' => 'models\ModelName',
        ],
        'deleteNode' => [
            'class' => 'voskobovich\nestedsets\actions\DeleteNodeAction',
            'modelClass' => 'models\ModelName',
        ],
        'updateNode' => [
            'class' => 'voskobovich\nestedsets\actions\UpdateNodeAction',
            'modelClass' => 'models\ModelName',
        ],
        'createNode' => [
            'class' => 'voskobovich\nestedsets\actions\CreateNodeAction',
            'modelClass' => 'models\ModelName',
        ],
    ];
}
```

3. Выведите виджет в удобном месте
```
<?= \voskobovich\nestedsets\widgets\nestable\Nestable::widget([
    'modelClass' => '\backend\models\Activity',
]) ?>
```