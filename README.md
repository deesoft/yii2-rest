yii2-rest
============

Yii2 rest extension

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require deesoft/yii2-rest "~1.0"
```

or add

```
"deesoft/yii2-rest": "~1.0"
```

to the require section of your `composer.json` file.

Usage
-----

In controller
```php

public function actions()
{
    return [
        'index' => [
            'class' => 'dee\rest\RestAction',
        ],
    ];
}
```
To use url rule. Set in config
```php
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                [
                    'class' => 'dee\rest\UrlRule',
                    'actions' => [
                        'post' => 'post/index',
                        
                    ],
                ],
            ],
        ],
```
