<?php

namespace dee\rest;

use Yii;
use yii\db\ActiveRecord;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * Description of BasicController
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class BasicController extends Controller
{

    /**
     * @inheritdoc
     */
    protected function verbs()
    {
        return [
            'index' => ['GET', 'HEAD'],
            'view' => ['GET', 'HEAD'],
            'create' => ['POST'],
            'update' => ['PUT'],
            'patch' => ['PATCH'],
            'delete' => ['DELETE'],
        ];
    }

    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex()
    {
        if ($this->modelSearchClass !== null) {
            /* @var $modelClass ActiveRecord */
            $modelSearch = new $this->modelSearchClass;
            $dataProvider = $modelSearch->search(Yii::$app->getRequest()->getQueryParams());
        } else {
            /* @var $modelClass ActiveRecord */
            $modelClass = $this->modelClass;
            $query = $modelClass::find();
            $dataProvider = new ActiveDataProvider([
                'query' => $query,
            ]);
        }

        return $dataProvider;
    }

    /**
     * Displays a single model.
     * @param integer $id
     * @return mixed
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        return $model;
    }

    /**
     * Displays a single filed of model.
     * @param integer $id
     * @return mixed
     */
    protected function viewDetail($id, $field)
    {
        $model = $this->findModel($id);
        $definition = array_merge($model->fields(), $model->extraFields());
        if (isset($definition[$field])) {
            return is_string($definition[$field]) ? $model->{$definition[$field]} : call_user_func($definition[$field], $model, $field);
        } elseif (in_array($field, $definition)) {
            return $model->$field;
        }
        throw new NotFoundHttpException("Object not found: $id/$field");
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        /* @var $model ActiveRecord */
        $model = $this->createModel();
        $model->load(Yii::$app->request->post(), '');
        $model->save();

        return $model;
    }

    /**
     * Updates an existing model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->load(Yii::$app->request->post(), '');
        $model->save();

        return $model;
    }

    /**
     * Updates an existing model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionPatch($id)
    {
        $model = $this->findModel($id);

        $patchs = Yii::$app->request->post();
        foreach ($patchs as $patch) {
            $this->doPatch($model, $patch);
        }
        $model->save();

        return $model;
    }

    /**
     * Deletes an existing model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);

        return $model->delete();
    }
}
