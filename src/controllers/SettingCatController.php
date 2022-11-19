<?php

namespace sadi01\moresettings\controllers;

use sadi01\moresettings\traits\AjaxValidationTrait;
use Yii;
use sadi01\moresettings\models\SettingCat;
use sadi01\moresettings\models\SettingCatSearch;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;

/**
 * SettingCatController implements the CRUD actions for SettingCat model.
 */
class SettingCatController extends Controller
{
    use AjaxValidationTrait;
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                    'delete-selected' => ['POST'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['superAdmin'],
                        'actions' => ['index', 'create', 'update', 'delete', 'create-ajax', 'delete-selected']
                    ]
                ]
            ]
        ];
    }

    /**
     * Lists all SettingCat models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SettingCatSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new SettingCat model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $model = new SettingCat();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Updates an existing SettingCat model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index']);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * Deletes an existing SettingCat model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        if ($this->findModel($id)->softDelete() === false)
            $this->flash('warning', Yii::t('more-settings', 'This Setting Category is in use!'));

        return $this->redirect(['index']);
    }

    public function actionDeleteSelected($selectedIds)
    {
        foreach (explode(',', $selectedIds) as $selectedId) {
            $model = $this->findModel($selectedId);
            if ($model->softDelete() === false)
                $this->flash('warning', Yii::t('more-settings', 'This Setting Category is in use!'));
        }
        return $this->redirect(['index']);
    }

    /**
     * Creates a new Category model.
     * @return mixed
     */
    public function actionCreateAjax()
    {
        $model = new SettingCat();

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->response->format = Response::FORMAT_JSON;
            return [
                'status' => 'success',
                'category' => ['id' => $model->id, 'title' => $model->title]
            ];
        }

        $this->performAjaxValidation($model);

        return $this->renderAjax('create', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the SettingCat model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return SettingCat the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = SettingCat::find()->byId($id)->notDeleted()->one()) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }
}