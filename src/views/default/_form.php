<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\helpers\ArrayHelper;
use sadi01\moresettings\models\Settings;
use sadi01\moresettings\models\SettingCat;
use sadi01\moresettings\SettingsAsset;
use yii\helpers\Url;
use yii\bootstrap4\Modal;
use yii\widgets\Pjax;
use sadi01\moresettings\helpers\CustomHtmlHelper;
use yii\web\View;
use sadi01\moresettings\models\Settings;
use sadi01\moresettings\models\SettingOption;

/* @var $this View */
/* @var $model Settings */
/* @var $modelsOption SettingOption[] */
/* @var $form ActiveForm */
?>

    <div class="setting-form">
        <?php $form = ActiveForm::begin([
            'id' => 'dynamic-settings',
            'options' => ['data' => ['action' => 'save-setting']]
        ]); ?>

        <div class="row">
            <div class="col-sm-3">
                <?= $form->field($model, 'cat_id', [
                        'template' => "{label}\n
                                        <div class='input-group mb-3'>
                                            {input}
                                            <div class='input-group-append'>
                                            <span data-toggle='modal' data-target='#add-category-modal' data-action='add-category'
                                            data-url=\"" . Url::to(['/moresettings/setting-cat/create-ajax']) . "\" >
                                                <button class='btn btn-success' type='button' data-toggle='tooltip' 
                                                title=\"" . Yii::t('more-settings', 'Create new category!') . "\" >
                                                    <i class='fas fa-plus'></i>
                                                </button>

                                            </div>
                                            {hint}\n{error}
                                        </div>",
                    ]
                )->dropDownList(
                        ArrayHelper::map(SettingCat::find()->all(), 'id', 'title'),
                        [
                                'prompt' => Yii::t('more-settings', 'Choose setting category ...')
                        ]
                ) ?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($model, "type")
                    ->dropDownList(Settings::itemAlias('Types'), [
                        'class' => 'form-control select-type',
                    ])
                ?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($model, "direction")
                    ->dropDownList(Settings::itemAlias('Directions'), [
                        'class' => 'form-control select-type',
                    ])
                ?>
            </div>
            <div class="col-sm-3">
                <?= $form->field($model, 'name')->textInput(['class' => 'form-control dir-ltr', 'maxlength' => true]) ?>
            </div>
        </div>

        <div class="row">
            <div class="col-sm-4">
                <?= $form->field($model, "label")
                    ->textInput([
                        'maxlength' => true,
                        'placeHolder' => Yii::t('more-settings', 'Label')
                    ])
                ?>
            </div>
            <div class="col-sm-4">
                <?= $form->field($model, 'description')->textInput(['maxlength' => true]) ?>
            </div>
            <div class="col-sm-4">
                <?= $form->field($model, "helper_text")
                    ->textInput([
                        'maxlength' => true,
                        'placeHolder' => Yii::t('more-settings', 'Hlper text')
                    ])
                ?>
            </div>
        </div><!-- end:row -->

        <?= $form->field($model, "default_value")
            ->textInput([
                'maxlength' => true,
                'placeHolder' => Yii::t('more-settings', 'Default value')
            ])
        ?>
        <?= $form->field($model, "place_holder")
            ->textInput([
                'maxlength' => true,
                'placeHolder' => Yii::t('more-settings', 'Place holder')
            ])
        ?>
        <div class="row">
            <div class="col-sm-6">
                <?= $form->field($model, "max_length")
                    ->textInput([
                        'maxlength' => true,
                        'placeHolder' => Yii::t('more-settings', 'Maximum length')
                    ])
                ?>
            </div>
            <div class="col-sm-6">
                <?= $form->field($model, "min_length")
                    ->textInput([
                        'maxlength' => true,
                        'placeHolder' => Yii::t('more-settings', 'Minimum length')
                    ])
                ?>
            </div>
        </div><!-- end:row -->
        <div class="row">
            <div class="col-sm-6">
                <?= $form->field($model, "max")
                    ->textInput([
                        'maxlength' => true,
                        'placeHolder' => Yii::t('more-settings', 'Maximum')
                    ])
                ?>
            </div>
            <div class="col-sm-6">
                <?= $form->field($model, "min")
                    ->textInput([
                        'maxlength' => true,
                        'placeHolder' => Yii::t('more-settings', 'Minimum')
                    ])
                ?>
            </div>
        </div><!-- end:row -->
        <div class="row">
            <div class="col-sm-6">
                <?= $form->field($model, "max_size")
                    ->textInput([
                        'maxlength' => true,
                        'placeHolder' => Yii::t('more-settings', 'Maximum file size(MB)')
                    ])
                ?>
            </div>
        </div><!-- end:row -->
        <?= $form->field($model, "calendar_type")
            ->dropDownList(Settings::itemAlias('CalendarTypes'), [
                'placeHolder' => Yii::t('more-settings', 'Calendar type')
            ])
        ?>
        <?= $form->field($model, "number_type")
            ->dropDownList(Settings::itemAlias('NumberTypes'), [
                'placeHolder' => Yii::t('more-settings', 'Number type')
            ])
        ?>

        <div class="row">
            <div class="col-sm-6">
                <?= $form->field($model, "required", [
                    'labelOptions' => ['class' => 'custom-control-label']
                ])->checkbox([
                        'template' => "<div class='custom-control custom-checkbox'>{input}\n{label}\n{hint}\n{error}</div>",
                        'class' => 'custom-control-input'
                    ]
                );
                ?>
            </div>
            <div class="col-sm-6">
                <?= $form->field($model, "apply_separator", [
                    'labelOptions' => ['class' => 'custom-control-label']
                ])->checkbox([
                        'template' => "<div class='custom-control custom-checkbox'>{input}\n{label}\n{hint}\n{error}</div>",
                        'class' => 'custom-control-input'
                    ]
                );
                ?>
            </div>
        </div><!-- end:row -->

        <div class="row">
            <div class="col-sm-4">
                <?= $form->field($model, 'custom_validation_rule')->dropDownList(
                    Settings::itemAlias('CustomValidationRules'),
                    [
                        'prompt' => Yii::t('more-settings', 'Choose custom validation rule ...')
                    ]
                ) ?>
            </div>
            <div class="col-sm-4 d-flex flex-column justify-content-center">
                <?= $form->field($model, "skip_custom_validation_on_empty")->checkbox()?>
            </div>
            <div class="col-sm-4">
                <?= $form->field($model, 'custom_data_source')->dropDownList(
                    Settings::itemAlias('CustomDataSource') ?: [],
                    [
                        'prompt' => Yii::t('more-settings', 'Choose custom data source...')
                    ]
                ) ?>
            </div>
        </div><!-- end:row -->
        <?= $this->render('_form-options', [
            'form' => $form,
            'modelsOption' => $modelsOption,
        ]) ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('more-settings',$model->isNewRecord ? 'Create' : 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

<?php ActiveForm::end(); ?>

<?php
Pjax::begin();?>

<?php
Pjax::end();
?>

<?php
$loadingTag = CustomHtmlHelper::loadingTag();
$loadingText = Yii::t('more-settings/common', 'Loading ...');

Modal::begin([
    'id' => 'add-category-modal',
    'size' => Modal::SIZE_EXTRA_LARGE,
    'bodyOptions' => [
        'id' => 'create-form-container',
        'class' => 'bg-light',
    ]
]);

echo $loadingTag;

Modal::end();

$js = <<< JS

jQuery('#dynamic-settings .select-type').on('change', function(event){
    resetFields(this); // lib.js
});
   
 resetFields($('#dynamic-settings .select-type')); // lib.js

        var modalAddCategory = $('#add-category-modal');
        modalAddCategory.on('shown.bs.modal', function (e) {
            var button = $(e.relatedTarget)
        
            var pjaxSettings = {
                timeout: 20000,
                scrollTo: false,
                push: false,
                skipOuterContainers: true,
                url: button.data('url'),
                container: '#create-form-container'
            };
                        
            $('#create-form-container').off('pjax:error');
            $('#create-form-container').on('pjax:error', function (event, xhr, textStatus, error, options) {
                swal({
                    title: xhr.responseText,
                    type: "error",
                    confirmButtonText: '<i class="fa fa-thumbs-up font-22"></i>',
                });
                return false;
            });            
            $('#create-form-container').off('pjax:complete');
            $('#create-form-container').on('pjax:complete', function (event, xhr, textStatus, options) {
              modalAddCategory.off('submit', 'form');
              modalAddCategory.on('submit', 'form', function(e) {
                var submitBtn = $(this).find(':submit');
                var submitBtnOldHtml = submitBtn.html();
                submitBtn.attr('disabled', true).text('$loadingText');
                $.post($(this).attr('action'), $(this).serialize(), function (data) {
                    if (data.status === 'success') {
                        var newOption = new Option(data.category.title, data.category.id, true, true);
                        $(newOption).html(data.category.title);
                        $("#settings-cat_id").append(newOption).trigger('change');
                        modalAddCategory.modal('hide');
                    }
                    // errors handling
                    else {
                        $('#setting-cat-form').yiiActiveForm('updateMessages', data, true);
                    }
                }).fail(function (xhr, status, error) {
                    submitBtn.attr('disabled', false).text(submitBtnOldHtml);
                    swal({
                        title: xhr.responseText,
                        type: "error",
                        confirmButtonText: '<i class="fa fa-thumbs-up font-22"></i>',
                    });
                }).then(function (result) {
                    submitBtn.attr('disabled', false).text(submitBtnOldHtml);
                });
                return false;
              })
            });
            $.pjax(pjaxSettings);
        });

        modalAddCategory.on('hidden.bs.modal', function (e) {
          $(this).find('.modal-body').html('$loadingTag');
        });

JS;

$this->registerJs($js, yii\web\View::POS_READY);

SettingsAsset::register($this)
?>