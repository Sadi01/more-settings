<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model sadi01\moresettings\models\SettingCat */
/* @var $form ActiveForm */
?>

<div class="setting-cat-form">

    <?php $form = ActiveForm::begin([
        'id' => 'setting-cat-form'
    ]); ?>

    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'model_class')->textInput(['maxlength' => true, 'class' => 'form-control dir-ltr']) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-6">
            <?= $form->field($model, 'source_model_class')->textInput(['maxlength' => true, 'class' => 'form-control dir-ltr']) ?>
        </div>
        <div class="col-sm-6">
            <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <?= $form->field($model, "is_public", [
                'labelOptions' => ['class' => 'custom-control-label']
            ])->checkbox([
                    'template' => "<div class='custom-control custom-checkbox'>{input}\n{label}\n{hint}\n{error}</div>",
                    'class' => 'custom-control-input'
                ]
            );
            ?>
        </div>
    </div>
</div>

<div class="form-group">
    <?= Html::submitButton($model->isNewRecord ? Yii::t('more-settings', 'Create') : Yii::t('more-settings', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
</div>

<?php ActiveForm::end(); ?>
