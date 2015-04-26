<?php

namespace dee\rest;

use Yii;
use yii\db\ActiveRecord;
use yii\base\InvalidConfigException;
use yii\web\NotFoundHttpException;

/**
 * Controller
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Controller extends \yii\rest\Controller
{
    /**
     * @var string the model class name. This property must be set.
     */
    public $modelClass;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->modelClass === null) {
            throw new InvalidConfigException('The "modelClass" property must be set.');
        }
    }

    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['POST', 'PUT'],
            'patch' => ['PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer|array $id
     * @return Purchase the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;
        $keys = $modelClass::primaryKey();
        if (count($keys) > 1) {
            if (is_array($id)) {
                $model = $modelClass::findOne($id);
            } else {
                $values = explode(',', $id);
                if (count($keys) === count($values)) {
                    $model = $modelClass::findOne(array_combine($keys, $values));
                }
            }
        } elseif ($id !== null) {
            $model = $modelClass::findOne($id);
        }

        if (isset($model)) {
            return $model;
        } else {
            throw new NotFoundHttpException("Object not found: ".  json_encode($id));
        }
    }

    /**
     * @return ActiveRecord
     */
    protected function createModel()
    {
        /* @var $modelClass ActiveRecord */
        $modelClass = $this->modelClass;
        return new $modelClass();
    }

    /**
     * Patch resource
     * @param ActiveRecord $model
     * @param array $patch
     */
    protected function doPatch($model, $patch)
    {
        $op = isset($patch['op']) ? $patch['op'] : 'replace';
        switch ($op) {
            case 'remove':
                $model->{$patch['field']} = null;
                break;
            case 'move':
                $model->{$patch['field']} = $model->{$patch['from']};
                $model->{$patch['from']} = null;
                break;
            case 'copy':
                $model->{$patch['field']} = $model->{$patch['from']};
                break;
            default:
                $model->{$patch['field']} = $patch['value'];
                break;
        }
    }
}