<?php

namespace dee\rest;

/**
 * Description of Bootstrap
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Bootstrap
{

    public static function apply()
    {
        \Yii::$classMap['yii\base\ArrayableTrait'] = __DIR__ . '/ArrayableTrait.php';
        \Yii::$classMap['yii\helpers\ArrayHelper'] = __DIR__ . '/ArrayHelper.php';
    }
}