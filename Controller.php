<?php

namespace dee\rest;

use Yii;
use yii\filters\auth\CompositeAuth;
use yii\filters\ContentNegotiator;
use yii\filters\RateLimiter;
use yii\web\Response;
use yii\db\ActiveRecordInterface;
use yii\web\NotFoundHttpException;

/**
 * Controller
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class Controller extends \yii\web\Controller
{
    /**
     * @var string class name of the model which will be handled by this action.
     * The model class must implement [[ActiveRecordInterface]].
     * This property must be set.
     */
    public $modelClass;

    /**
     *
     * @var array
     */
    public $extraPatterns = [];

    /**
     * @var string|array the configuration for creating the serializer that formats the response data.
     */
    public $serializer = 'yii\rest\Serializer';

    /**
     * @inheritdoc
     */
    public $enableCsrfValidation = false;

    /**
     * @var string
     */
    public $restId = 'index';

    /**
     * @inheritdoc
     */
    public function actions()
    {
        return[
            $this->restId => [
                'class' => __NAMESPACE__ . '\ResourceAction',
                'extraPatterns' => $this->extraPatterns,
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::className(),
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
                'only' => [$this->restId],
            ],
            'authenticator' => [
                'class' => CompositeAuth::className(),
                'authMethods' => $this->authMethods(),
                'only' => [$this->restId],
            ],
            'rateLimiter' => [
                'class' => RateLimiter::className(),
                'only' => [$this->restId],
            ],
        ];
    }

    public function authMethods()
    {
        return[];
    }

    /**
     * @inheritdoc
     */
    public function afterAction($action, $result)
    {
        $result = parent::afterAction($action, $result);
        return $this->serializeData($result);
    }

    /**
     * Serializes the specified data.
     * The default implementation will create a serializer based on the configuration given by [[serializer]].
     * It then uses the serializer to serialize the given data.
     * @param mixed $data the data to be serialized
     * @return mixed the serialized data.
     */
    protected function serializeData($data)
    {
        if (is_array($this->serializer) && !isset($this->serializer['class'])) {
            $this->serializer['class'] = 'yii\rest\Serializer';
        }
        return Yii::createObject($this->serializer)->serialize($data);
    }

    /**
     * Finds the model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Purchase the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        /* @var $modelClass ActiveRecordInterface */
        $modelClass = $this->modelClass;
        $keys = $modelClass::primaryKey();
        if (count($keys) > 1) {
            $values = explode(',', $id);
            if (count($keys) === count($values)) {
                $model = $modelClass::findOne(array_combine($keys, $values));
            }
        } elseif ($id !== null) {
            $model = $modelClass::findOne($id);
        }

        if (isset($model)) {
            return $model;
        } else {
            throw new NotFoundHttpException("Object not found: $id");
        }
    }

    /**
     * Patch resource
     * @param ActiveRecordInterface $model
     * @param array $patch
     */
    protected function doPatch($model, $patch)
    {
        $op = isset($patch['op']) ? $patch['op'] : 'replace';
        switch ($op) {
            case 'remove':
                $model->{$patch['path']} = null;
                break;
            case 'move':
                $model->{$patch['path']} = $model->{$patch['from']};
                $model->{$patch['from']} = null;
                break;
            case 'copy':
                $model->{$patch['path']} = $model->{$patch['from']};
                break;
            default:
                $model->{$patch['path']} = $patch['value'];
                break;
        }
    }
}