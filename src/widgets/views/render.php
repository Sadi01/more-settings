<?php

use yii\widgets\Pjax;
use sadi01\moresettings\models\Settings;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model \yii\db\ActiveRecord */
/* @var $settingModel Settings */
/* @var $settingValueModel sadi01\moresettings\models\SettingValue */
/* @var $settingCatModel sadi01\moresettings\models\SettingCat */
/* @var $form ActiveForm */
/* @var $viewFile string */
?>
<div id="<?= $renderFormWrapperId ?>" class="card material-card text-left">

    <div class="card-body">
        <?php
        Pjax::begin([
            'id' => $pjaxContainerId,
            'enablePushState' => false,
            'timeout' => 20000,
        ]);
        ?>

        <?php
        echo $this->render($viewFile, [
            'options' => $options,
            //'model' => $this->model,
            //'modelClass' => $this->modelClass,
            'settingModel' => $settingModel,
            'settingValueModel' => $settingValueModel,
            //'settingCatModel' => $settingCatModel,
            //'boxTitleText' => $boxTitleText,
            'encryptedEntity' => $encryptedEntity
        ]);
        ?>

        <?php Pjax::end(); ?>

    </div>

</div>

<?php
$js = <<<JS
            $('#change-form-container').off('pjax:error');
            $('#change-form-container').off('pjax:complete');
JS;

$this->registerJs($js, View::POS_READY);
?>