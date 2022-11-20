<?php

namespace sadi01\moresettings\controllers;

use sadi01\moresettings\models\SettingOption;
use sadi01\moresettings\models\SettingSelectedOption;
use sadi01\moresettings\models\SettingValue;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use sadi01\moresettings\models\Settings;
use sadi01\moresettings\models\SettingsSearch;
use sadi01\moresettings\models\Model;
use yii\web\Response;

class DefaultController extends Controller
{
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
                        'roles' => ['@'],
                        'actions' => ['create', 'update', 'delete', 'delete-selected']
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'actions' => ['index']
                    ],
                    [
                        'allow' => true,
                        'roles' => ['@'],
                        'actions' => ['change-value']
                    ],
                ]
            ]
        ];
    }

    /**
     * Lists all Settings models.
     * @return mixed
     */
    public function actionIndex()
    {
        $searchModel = new SettingsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Creates a new Settings model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        $modelSetting = new Settings();
        $modelsOption = [new SettingOption()];
        $modelSetting->loadDefaultValues();

        if ($modelSetting->load(Yii::$app->request->post())) {

            $modelsOption = Model::createMultiple(SettingOption::class);
            Model::loadMultiple($modelsOption, Yii::$app->request->post());

            foreach ($modelsOption as $indexField => $modelOption) {
                $modelOption->order_id = $indexField;
            }
            // validate setting and setting options models
            $valid = $modelSetting->validate();
            $valid = Model::validateMultiple($modelsOption) && $valid;

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($flag = $modelSetting->save(false)) {
                        foreach ($modelsOption as $indexField => $modelOption) {

                            if ($flag === false) {
                                break;
                            }

                            $modelOption->setting_id = $modelSetting->id;

                            if (!($flag = $modelOption->save(false))) {
                                break;
                            }
                        }
                    }

                    if ($flag) {
                        $transaction->commit();
                        return $this->redirect(['index']);
                    } else {
                        $transaction->rollBack();
                    }
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }

        return $this->render('create', [
            'model' => $modelSetting,
            'modelsOption' => (empty($modelsOption)) ? [new SettingOption()] : $modelsOption,
        ]);
    }

    /**
     * Updates an existing Settings model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $modelSetting = $this->findModel($id);

        if ($modelSetting->getSettingValues()->count() > 0) {
            throw new MethodNotAllowedHttpException(Yii::t('more-settings', 'You cannot change this setting. It\'s in use!'));
        }

        //$modelSetting->scenario = Settings::SCENARIO_UPDATE;
        $modelsOption = $modelSetting->settingOptions;

        if ($modelSetting->load(Yii::$app->request->post())) {

            $oldOptionIDs = ArrayHelper::map($modelsOption, 'id', 'id');
            $modelsOption = Model::createMultiple(SettingOption::class, SettingOption::SCENARIO_DEFAULT, SettingOption::SCENARIO_DEFAULT, $modelsOption);
            Model::loadMultiple($modelsOption, Yii::$app->request->post());

            foreach ($modelsOption as $indexField => $modelOption) {
                /**@var $modelOption SettingOption */
                $modelOption->order_id = $indexField;
            }

            $deletedOptionsIDs = array_diff($oldOptionIDs, array_filter(ArrayHelper::map($modelsOption, 'id', 'id')));

            // validate setting and setting options models
            $valid = $modelSetting->validate();
            $valid = Model::validateMultiple($modelsOption) && $valid;

            if ($valid) {
                $transaction = Yii::$app->db->beginTransaction();
                try {
                    if ($flag = $modelSetting->save(false)) {

                        if (!empty($deletedOptionsIDs)) {
                            SettingOption::deleteAll(['id' => $deletedOptionsIDs]);
                        }

                        foreach ($modelsOption as $indexField => $modelOption) {

                            if ($flag === false) {
                                break;
                            }

                            $modelOption->setting_id = $modelSetting->id;

                            if (!($flag = $modelOption->save(false))) {
                                break;
                            }
                        }
                    }

                    if ($flag) {
                        $transaction->commit();
                        return $this->redirect(['index']);
                    } else {
                        $transaction->rollBack();
                    }
                } catch (Exception $e) {
                    $transaction->rollBack();
                }
            }
        }

        return $this->render('update', [
            'model' => $modelSetting,
            'modelsOption' => (empty($modelsOption)) ? [new SettingOption()] : $modelsOption,
        ]);
    }


    public function actionChangeValue()
    {
        if (!Yii::$app->request->isAjax) {
            throw new MethodNotAllowedHttpException('Method Not Allowed.');
        }

        $request = Yii::$app->request;
        $encryptedEntity = $request->get('params');
        $params = $this->getDecryptedData($encryptedEntity);
        $oldSelectedOptionIDs = [];
        $deletedSelectedOptionIDs = [];
        $newSelectedOptionIDs = [];

        /** @var SettingValue $settingValueModel */
        if ($params['is_public']) {
            $settingValueModel = SettingValue::find()->withSettingIdAndModelIdIsNull($params['setting_id'])->one();
        } else {
            if ($params['source_model_id']) {
                $settingValueModel = SettingValue::find()->withSettingIdAndModelIdAndSourceModelId($params['setting_id'], $params['model_id'], $params['source_model_id'])->one();
            } else {
                $settingValueModel = SettingValue::find()->withSettingIdAndModelId($params['setting_id'], $params['model_id'])->one();
            }
        }

        if (!$settingValueModel instanceof SettingValue) {
            $settingValueModel = new SettingValue([
                'setting_id' => $params['setting_id'],
                'model_id' => $params['model_id'],
                'source_model_id' => $params['source_model_id'] ?: null
            ]);
        }

        $settingValueModel->scenario = SettingValue::SCENARIO_CHANGE_VALUE;

        if ($settingValueModel->load(Yii::$app->request->post()) && $settingValueModel->validate()) {

            if (!$settingValueModel->isNewRecord) {
                $oldSelectedOptionIDs = ArrayHelper::map($settingValueModel->options, 'option_key', 'option_key');
            }

            if (is_array($settingValueModel->value) || !empty($oldSelectedOptionIDs)) {
                $deletedSelectedOptionIDs = !$settingValueModel->isNewRecord ?
                    array_diff($oldSelectedOptionIDs, is_array($settingValueModel->value) ? $settingValueModel->value : [])
                    :
                    [];
                $newSelectedOptionIDs = $settingValueModel->isNewRecord ?
                    $settingValueModel->value :
                    array_diff(is_array($settingValueModel->value) ? $settingValueModel->value : [], $oldSelectedOptionIDs);
                $settingValueModel->scenario = SettingValue::SCENARIO_CHANGE_VALUE_ARRAY;
            }

            $transaction = Yii::$app->db->beginTransaction();
            try {
                $attributeNames = ['setting_id', 'source_model_id', 'model_id', 'created_at', 'updated_at', 'created_by', 'updated_by'];
                if ($settingValueModel->scenario === SettingValue::SCENARIO_CHANGE_VALUE) {
                    $attributeNames[] = 'value';
                }

                if ($flag = $settingValueModel->save(false, $attributeNames)) {

                    if (!$settingValueModel->isNewRecord) {
                        if (!empty($deletedSelectedOptionIDs)) {
                            SettingSelectedOption::deleteAll([
                                'and',
                                'setting_value_id' => $settingValueModel->id,
                                ['in', 'option_id', $deletedSelectedOptionIDs]
                            ]);
                        }
                    }

                    if (is_array($newSelectedOptionIDs)) {
                        foreach ($newSelectedOptionIDs as $option) {
                            $settingSelectedOption = new SettingSelectedOption();
                            $settingSelectedOption->setting_value_id = $settingValueModel->id;
                            $settingSelectedOption->option_id = $option;
                            if (!($flag = $settingSelectedOption->save())) {
                                break;
                            }
                        }
                    }
                }

                if ($flag) {
                    $transaction->commit();
                } else {
                    $transaction->rollBack();
                    Yii::$app->response->format = Response::FORMAT_JSON;
                    return [
                        'status' => 'error'
                    ];
                }

            } catch (Exception $e) {
                $transaction->rollBack();
            }
        }

        return $this->renderAjax($params['viewFile'], [
            'settingModel' => $settingValueModel->setting,
            'settingValueModel' => $settingValueModel,
            'encryptedEntity' => $encryptedEntity,
            'options' => $params['options']
        ]);
    }

    /**
     * Deletes an existing Settings model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionDelete($id)
    {
        if ($this->findModel($id)->softDelete() === false)
            $this->flash('warning', Yii::t('more-settings', 'This Setting is in use!'));

        return $this->redirect(['index']);
    }

    public function actionDeleteSelected($selectedIds)
    {
        foreach (explode(',', $selectedIds) as $selectedId) {
            $model = $this->findModel($selectedId);
            if ($model->softDelete() === false)
                $this->flash('warning', Yii::t('more-settings', 'This Setting is in use!'));
        }
        return $this->redirect(['index']);
    }

    /**
     * Finds the Settings model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Settings the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Settings::find()->byId($id)->notDeleted()->one()) !== null) {
            return $model;
        }

        throw new NotFoundHttpException(Yii::t('common', 'The requested page does not exist.'));
    }

    public function flash($type, $message)
    {
        Yii::$app->getSession()->setFlash($type == 'error' ? 'danger' : $type, $message);
    }

    /**
     * Get list of attributes from encrypted entity
     *
     * @param $entity string encrypted entity
     *
     * @return array|mixed
     *
     * @throws BadRequestHttpException
     */
    protected function getDecryptedData($entity)
    {
        $decryptEntity = Yii::$app->getSecurity()->decryptByKey(utf8_decode($entity), Yii::$app->getModule('moresettings')->id);
        if (false !== $decryptEntity) {
            return Json::decode($decryptEntity);
        }

        throw new BadRequestHttpException(Yii::t('more-settings', 'Oops, something went wrong. Please try again later.'));
    }
}
