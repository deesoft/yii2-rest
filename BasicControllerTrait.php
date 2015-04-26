<?php

namespace dee\rest;

use yii\data\ActiveDataProvider;

/**
 * Description of BasicResourceTrait
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
trait BasicControllerTrait
{
    /**
     * @var string the model class name. This property must be set.
     */
    public $searchModelClass;

    /**
     * Lists all models.
     * @return mixed
     */
    public function actionIndex()
    {
        /* @var $modelClass ActiveRecord */
        if ($this->searchModelClass !== null) {
            $modelClass = $this->searchModelClass;
            $model = new $modelClass;
            $dataProvider = $model->search(Yii::$app->request->getQueryParams());
        } else {
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