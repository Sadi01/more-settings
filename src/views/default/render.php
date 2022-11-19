<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\helpers\ArrayHelper;
use yii\bootstrap4\ActiveForm;
use sadi01\moresettings\models\Settings;
use sadi01\dateRangePicker\dateRangePicker;
use kartik\file\FileInput;
use yii\web\View;
use yii\web\JsExpression;
use kartik\number\NumberControl;

/* @var $this yii\web\View */
/* @var $model \yii\db\ActiveRecord */
/* @var $settingModel Settings */
/* @var $settingValueModel sadi01\moresettings\models\SettingValue */
/* @var $settingCatModel sadi01\moresettings\models\SettingCat */
/* @var $form ActiveForm */
?>

<?php $form = ActiveForm::begin([
    'action' => Url::to([
        '/moresettings/default/change-value',
        'params' => $encryptedEntity
    ]),
    'options' => [
        'id' => 'formSetting_' . $settingModel->id,
        'data-pjax' => true
    ]
]); ?>

<?php
switch ($settingModel->type) {
    case Settings::TYPE_TEXT_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])->textInput([
            'id' => 'formSetting_' . $settingModel->id . '_textInput',
            'maxLength' => true,
            'class' => 'form-control ' . Settings::itemAlias('DirectionsClass', $settingModel->direction),
            'placeHolder' => $settingModel->place_holder,
            'data' => [
                'required' => $settingModel->required,
                'string' => 1,
            ]
        ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_NUMBER_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])->textInput([
            'id' => 'formSetting_' . $settingModel->id . '_numberInput',
            'maxLength' => true,
            'placeHolder' => $settingModel->place_holder,
            'class' => 'form-control ' . Settings::itemAlias('DirectionsClass', $settingModel->direction),
            'data' => [
                'required' => $settingModel->required,
                'integer' => $settingModel->number_type === Settings::NUMBER_TYPE_INTEGER,
                'double' => $settingModel->number_type === Settings::NUMBER_TYPE_FLOAT,
            ]
        ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_PRICE_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])->widget(NumberControl::class, [
            'hashVarLoadPosition' => View::POS_READY, //cause of rendering the widget via `renderAjax`
            'maskedInputOptions' => Yii::$app->params['maskedInputOptions'],
            'options' => [
                'id' => 'formSetting_' . $settingModel->id . '_priceInput',
                'placeHolder' => $settingModel->place_holder,
                'class' => 'form-control ' . Settings::itemAlias('DirectionsClass', $settingModel->direction),
                'data' => [
                    'required' => $settingModel->required,
                    'integer' => $settingModel->number_type === Settings::NUMBER_TYPE_INTEGER,
                    'double' => $settingModel->number_type === Settings::NUMBER_TYPE_FLOAT,
                ]
            ]
        ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_CHECKBOX_INPUT:
        echo $form->field($settingValueModel, "value", [
            'labelOptions' => [
                'class' => 'custom-control-label'
            ],
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])->checkbox([
                'id' => 'formSetting_' . $settingModel->id . '_checkBoxInput',
                'template' => "<div class='custom-control custom-checkbox'>{input}\n{label}\n{hint}\n{error}</div>",
                'class' => 'custom-control-input',
                'data' => [
                    'required' => $settingModel->required,
                    'boolean' => 1,
                ]
            ]
        )->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_CHECKBOX_GROUP_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])
            ->checkboxList(ArrayHelper::map($settingModel->settingOptions, 'option_key', 'name'), [
                'id' => 'formSetting_' . $settingModel->id . '_checkBoxGroupInput',
                'item' => function ($index, $label, $name, $checked, $value) {
                    $checked = $checked ? 'checked' : '';
                    $return = '<div class="custom-control custom-checkbox custom-control-inline">';
                    $return .= '<input type="checkbox" id="i' . $index . '-' . $name . '" name="' . $name . '" value="' . $value . '" class="custom-control-input" ' . $checked . '>';
                    $return .= '<label class="custom-control-label" for="i' . $index . '-' . $name . '">' . $label . '</label>';
                    $return .= '</div>';

                    return $return;
                },
                'data' => [
                    'required' => $settingModel->required,
                    'range' => 1,
                ]
            ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_RADIO_GROUP_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])
            ->radioList(ArrayHelper::map($settingModel->settingOptions, 'option_key', 'name'), [
                'id' => 'formSetting_' . $settingModel->id . '_radioGroupInput',
                'item' => function ($index, $label, $name, $checked, $value) {
                    $checked = $checked ? 'checked' : '';
                    $return = '<div class="custom-control custom-radio custom-control-inline">';
                    $return .= '<input type="radio" id="i' . $index . '-' . $name . '" name="' . $name . '" value="' . $value . '" class="custom-control-input"' . $checked . '>';
                    $return .= '<label class="custom-control-label" for="i' . $index . '-' . $name . '">' . $label . '</label>';
                    $return .= '</div>';

                    return $return;
                },
                'data' => [
                    'required' => $settingModel->required,
                    'just-range' => 1,
                ]
            ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_DROP_DOWN_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])
            ->dropDownList(ArrayHelper::map($settingModel->settingOptions, 'option_key', 'name'), [
                'id' => 'formSetting_' . $settingModel->id . '_dropDownInput',
                'prompt' => Yii::t('more-settings', 'Choose an option ...'),
                'data' => [
                    'required' => $settingModel->required,
                    'just-range' => 1,
                ]
            ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_TEXT_AREA_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])->textarea([
            'id' => 'formSetting_' . $settingModel->id . '_textAreaInput',
            'placeHolder' => $settingModel->place_holder,
            'class' => 'form-control ' . Settings::itemAlias('DirectionsClass', $settingModel->direction),
            'data' => [
                'required' => $settingModel->required,
                'string' => 1,
            ]
        ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_MOBILE_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])->textInput([
            'id' => 'formSetting_' . $settingModel->id . '_mobileInput',
            'maxLength' => true,
            'placeHolder' => $settingModel->place_holder,
            'class' => 'form-control ' . Settings::itemAlias('DirectionsClass', $settingModel->direction),
            'data' => [
                'required' => $settingModel->required,
                'mobile' => 1,
            ]
        ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_PHONE_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])->textInput([
            'id' => 'formSetting_' . $settingModel->id . '_phoneInput',
            'maxLength' => true,
            'placeHolder' => $settingModel->place_holder,
            'class' => 'form-control ' . Settings::itemAlias('DirectionsClass', $settingModel->direction),
            'data' => [
                'required' => $settingModel->required,
                'phone' => 1,
            ]
        ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_NATIONAL_CODE_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])->textInput([
            'id' => 'formSetting_' . $settingModel->id . '_nationalCodeInput',
            'maxLength' => true,
            'placeHolder' => $settingModel->place_holder,
            'class' => 'form-control ' . Settings::itemAlias('DirectionsClass', $settingModel->direction),
            'data' => [
                'required' => $settingModel->required,
                'nationalCode' => 1,
            ]
        ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_PLATE_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])->textInput([
            'id' => 'formSetting_' . $settingModel->id . '_plateInput',
            'maxLength' => true,
            'placeHolder' => $settingModel->place_holder,
            'class' => 'form-control ' . Settings::itemAlias('DirectionsClass', $settingModel->direction),
            'data' => [
                'required' => $settingModel->required,
                'plate' => 1,
                'string' => 1,
            ]
        ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_EMAIL_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])->textInput([
            'id' => 'formSetting_' . $settingModel->id . '_emailInput',
            'maxLength' => true,
            'placeHolder' => $settingModel->place_holder,
            'class' => 'form-control ' . Settings::itemAlias('DirectionsClass', $settingModel->direction),
            'data' => [
                'required' => $settingModel->required,
                'email' => 1,
            ]
        ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_URL_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])->textInput([
            'id' => 'formSetting_' . $settingModel->id . '_urlInput',
            'maxLength' => true,
            'placeHolder' => $settingModel->place_holder,
            'class' => 'form-control ' . Settings::itemAlias('DirectionsClass', $settingModel->direction),
            'data' => [
                'required' => $settingModel->required,
                'url' => 1,
            ]
        ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_DATE_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])->widget(dateRangePicker::class, [
            'options' => [
                'locale' => [
                    'format' => $settingModel->calendar_type === Settings::CALENDAR_TYPE_JALALI ? 'jYYYY/jMM/jDD' : 'YYYY/MM/DD',
                ],
                'drops' => 'up',
                'opens' => 'right',
                'jalaali' => $settingModel->calendar_type === Settings::CALENDAR_TYPE_JALALI ? true : false,
                'showDropdowns' => true,
                'language' => $settingModel->calendar_type === Settings::CALENDAR_TYPE_JALALI ? 'fa' : 'en',
                'singleDatePicker' => true,
                'useTimestamp' => true,
            ],
            'htmlOptions' => [
                'id' => 'formSetting_' . $settingModel->id . '_dateInput',
                'class' => 'form-control',
                'placeHolder' => $settingModel->place_holder,
                'data' => [
                    'required' => $settingModel->required,
                    'jdate' => $settingModel->calendar_type === Settings::CALENDAR_TYPE_JALALI ? 1 : 0,
                    'date' => $settingModel->calendar_type === Settings::CALENDAR_TYPE_GREGORIAN ? 1 : 0,
                ]
            ]
        ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_IMAGE_UPLOAD_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])->widget(FileInput::class, [
            'options' => [
                'accept' => ['image/png', 'image/jpeg'],
                'id' => 'formSetting_' . $settingModel->id . '_imageInput',
            ],
            'hashVarLoadPosition' => View::POS_READY, //cause of rendering the widget via `renderAjax`
            'pluginOptions' => [
                'showCaption' => false,
                'showRemove' => false,
                'showUpload' => false,
                'showCancel' => false,
                'theme' => 'explorer-fas',
                'browseClass' => 'btn btn-primary btn-sm btn-preview',
                'browseIcon' => '<i class="fas fa-camera"></i> ',
                'browseLabel' => Yii::t('more-settings', 'Choose a picture ...'),
                'previewFileType' => 'image',
                'initialPreviewAsData' => true,
                'initialPreview' => !$settingValueModel->isNewRecord ? ($settingValueModel->getUploadUrl("value") ?: false) : false,
                'initialPreviewFileType' => 'image',
            ],
            'data' => [
                'required' => $settingValueModel->isNewRecord ? $settingModel->required : ''
            ]
        ])->hint($settingModel->helper_text ? ' ' : '');
        break;
    case Settings::TYPE_CUSTOM_DATA_SOURCE_INPUT:
        echo $form->field($settingValueModel, "value", [
            'template' => "{label}{hint}\n{input}\n{error}",
            'hintOptions' => [
                'tag' => 'a',
                'class' => 'fas fa-question-circle fa-md ml-1',
                'title' => Yii::t('more-settings', 'Get help!'),
                'tabindex' => 1,
                'data' => [
                    'toggle' => 'popover',
                    'container' => 'body',
                    'placement' => 'top',
                    'trigger' => 'focus',
                    'content' => $settingModel->helper_text,
                ]
            ]
        ])
            ->dropDownList(Settings::itemAlias($settingModel->custom_data_source), [
                'id' => 'formSetting_' . $settingModel->id . '_CustomDataSourceDropDownInput',
                'prompt' => Yii::t('more-settings', 'Choose an option ...'),
                'data' => [
                    'required' => $settingModel->required,
                    'just-custom-range' => 1,
                ]
            ])->hint($settingModel->helper_text ? ' ' : '');
        break;
}

?>
    <div class="form-group">
        <?= Html::submitButton(Yii::t('common', 'Save'), ['class' => 'btn btn-success']) ?>
    </div>

<?php ActiveForm::end(); ?>

<?php
$js = <<<JS
$("#formSetting_{$settingModel->id}").on('beforeSubmit', function(e) {
  $(this).find(':submit').html('<i class="fas fa-spinner fa-pulse"></i>').attr('disabled', true)
})
JS;

$this->registerJs($js, View::POS_READY);
?>