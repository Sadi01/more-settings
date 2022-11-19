<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $modelsOption [] sadi01\moresettings\models\SettingOption */
/* @var $form ActiveForm */
?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_inner',
    'widgetBody' => '.container-options',
    'widgetItem' => '.field-option',
    'min' => 0,
    'insertButton' => '.add-option',
    'deleteButton' => '.remove-option',
    'model' => $modelsOption[0],
    'formId' => 'dynamic-settings',
    'formFields' => [
        'option_key',
        'name',
        'description',
        'order_id'
    ],
]); ?>
<div class="card material-card mb-3 options">
    <div class="card-header">
        <span class="pull-left">
            <?= Yii::t('more-settings', 'Options') ?>
            <i class="fa fa-check-square fa-lg"></i>
        </span>
        <button type="button" class="pull-right add-option btn btn-success btn-xs">
            <i class="fas fa-plus"></i> <?= Yii::t('more-settings', 'Add Option') ?>
        </button>
        <div class="clearfix"></div>
    </div>

    <div class="card-body container-options">
        <?php foreach ($modelsOption as $indexOption => $modelOption): ?>
            <div class="field-option card material-card border-warning mb-3">
                <div class="card-header">
                        <span class="pull-left sortable-handle-option text-center vcenter" style="cursor: move;">
                            <i class="fas fa-arrows-alt fa-lg"></i>
                        </span>
                    <div class="pull-right">
                        <button type="button" class="remove-option btn btn-danger btn-xs">
                            <i class="fas fa-minus"></i>
                        </button>
                        <span class="panel-title-field-option">
                                <?= Yii::t('more-settings', 'Option: {0}', [0 => ($indexOption + 1)]) ?>
                            </span>
                    </div>
                    <div class="clearfix"></div>
                </div>
                <div class="card-body">
                    <?php
                    // necessary for update action.
                    if (!$modelOption->isNewRecord) {
                        echo Html::activeHiddenInput($modelOption, "[{$indexOption}]id");
                    }
                    ?>
                    <div class="row">
                        <div class="col-sm-4">
                            <?= $form->field($modelOption, "[{$indexOption}]option_key")->label(false)->textInput([
                                'maxlength' => true,
                                'placeHolder' => Yii::t('more-settings', "Input option's key")
                            ]) ?>
                        </div>
                        <div class="col-sm-4">
                            <?= $form->field($modelOption, "[{$indexOption}]name")->label(false)->textInput([
                                'maxlength' => true,
                                'placeHolder' => Yii::t('more-settings', "Input option's name")
                            ]) ?>
                        </div>
                        <div class="col-sm-4">
                            <?= $form->field($modelOption, "[{$indexOption}]description")->label(false)->textInput([
                                'maxlength' => true,
                                'placeHolder' => Yii::t('more-settings', "Any description")
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php DynamicFormWidget::end(); ?>

<?php
$optionText = Yii::t('more-settings', 'Option:');
$js = <<< JS

$(".container-options").sortable({
    items: ".field-option",
    cursor: "move",
    opacity: 0.6,
    axis: "y",
    handle: ".sortable-handle-option",
    update: function(ev){
        jQuery(".dynamicform_inner").yiiDynamicForm("updateContainer");
        jQuery(".dynamicform_inner").trigger('afterDelete');
    }
}).disableSelection();

jQuery(".dynamicform_inner").on("afterInsert", function(e, item) {
    jQuery(item).closest('.options').find('.panel-title-field-option').each(function(index) {
        jQuery(this).html("{$optionText} " + (index + 1))
    });
});

jQuery(".dynamicform_inner").on("afterDelete", function(e) {
    jQuery(".dynamicform_inner .options").each(function() {
        jQuery(this).find('.panel-title-field-option').each(function(index) {
            jQuery(this).html("{$optionText} " + (index + 1))
        });
    });
});
JS;

$this->registerJs($js, yii\web\View::POS_READY);

?>
