<?php

namespace sadi01\moresettings\traits;

use yii\base\Model;
use yii\web\Response;
use yii\widgets\ActiveForm;

trait AjaxValidationTrait
{
    /**
     * Performs ajax validation.
     *
     * @param Model $model
     *
     * @throws \yii\base\ExitException
     */
    protected function performAjaxValidation(Model $model, $attributes = null)
    {
        if (\Yii::$app->request->isAjax && $model->load(\Yii::$app->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            \Yii::$app->response->data = ActiveForm::validate($model, $attributes);
            \Yii::$app->response->send();
            \Yii::$app->end();
        }
    }

    /**
     * Performs ajax multiple validation.
     *
     * @param Model[] $models
     *
     * @throws \yii\base\ExitException
     */
    protected function performAjaxMultipleValidation($models, $attributes = null)
    {
        if (\Yii::$app->request->isAjax && Model::loadMultiple($models, \Yii::$app->request->post())) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            \Yii::$app->response->data = ActiveForm::validateMultiple($models, $attributes);
            \Yii::$app->response->send();
            \Yii::$app->end();
        }
    }

    /**
     * Performs ajax batch validation.
     *
     * @param array $models
     *
     * @throws \yii\base\ExitException
     */
    protected function performAjaxBatchValidation($models)
    {
        if (\Yii::$app->request->isAjax) {
            \Yii::$app->response->format = Response::FORMAT_JSON;
            $errors = [];

            foreach ($models as $index => $model) {
                if (is_array($model)) {
                    $modelErrors = ActiveForm::validateMultiple($model);
                    $errors = array_merge($errors, $modelErrors);
                } elseif($model instanceof Model) {
                    $modelErrors = ActiveForm::validate($model);
                    $errors = array_merge($errors, $modelErrors);
                }
            }

            \Yii::$app->response->data = $errors;
            \Yii::$app->response->send();
            \Yii::$app->end();
        }
    }
}