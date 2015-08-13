<?php

namespace dee\rest;

use Yii;
use yii\db\ActiveRecord;
use dee\base\GlobalTriggerTrait;
use yii\data\ActiveDataProvider;
use yii\web\NotFoundHttpException;

/**
 * Description of AdvanceController
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class AdvanceController extends Controller
{

    use GlobalTriggerTrait,
        TransactionTrait;

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

        $this->fire('query', [$dataProvider]);
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
        $this->fire('view', [$model]);
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
        $this->fire('viewDetail', [$model, $field]);
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
        $this->beginTransaction();
        try {
            $this->fire('beforeCreate', [$model]);
            $model->load(Yii::$app->request->post(), '');
            $this->fire('create', [$model]);
            if ($model->save()) {
                $this->fire('created', [$model]);
                $this->commit();
                $model->refresh();
            } else {
                $this->fire('rollbackCreate', [$model]);
                $this->rollBack();
            }
        } catch (\Exception $e) {
            $this->fire('errorCreate', [$model, $e]);
            $this->rollBack();
            throw $e;
        }

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

        $this->beginTransaction();
        try {
            $this->fire('beforeUpdate', [$model]);
            $model->load(Yii::$app->request->post(), '');
            $this->fire('update', [$model]);
            if ($model->save()) {
                $this->fire('updated', [$model]);
                $this->commit();
                $model->refresh();
            } else {
                $this->fire('rollbackUpdate', [$model]);
                $this->rollBack();
            }
        } catch (\Exception $e) {
            $this->fire('errorUpdate', [$model, $e]);
            $this->rollBack();
            throw $e;
        }

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

        $this->beginTransaction();
        try {
            $this->fire('beforePatch', [$model]);
            $patchs = Yii::$app->request->post();
            foreach ($patchs as $patch) {
                $this->doPatch($model, $patch);
            }
            $dirty = $model->getDirtyAttributes();
            $olds = $model->getOldAttributes();

            $this->fire('patch', [$model, $dirty, $olds]);
            if ($model->save()) {
                $this->fire('patched', [$model, $dirty, $olds]);
                $this->commit();
                $model->refresh();
            } else {
                $this->fire('rollbackPatch', [$model]);
                $this->rollBack();
            }
        } catch (\Exception $e) {
            $this->fire('errorPatch', [$model, $e]);
            $this->rollBack();
            throw $e;
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
        $this->beginTransaction();
        try {
            $this->fire('beforeDelete', [$model]);
            if ($model->delete()) {
                $this->fire('deleted', [$model]);
                $this->commit();
            } else {
                $this->fire('rollbackDelete', [$model]);
                $this->rollBack();
                return false;
            }
        } catch (\Exception $e) {
            $this->fire('errorDelete', [$model, $e]);
            $this->rollBack();
            throw $e;
        }

        return true;
    }
}