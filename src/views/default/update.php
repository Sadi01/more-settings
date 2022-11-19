<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model sadi01\moresettings\models\Settings */
/* @var $modelsOption [] sadi01\moresettings\models\SettingOption */

$this->title = Yii::t('more-settings', 'Update Setting: {name}', [
    'name' => $model->name,
]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('more-settings', 'Settings'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('more-settings', 'Update');
?>
<div class="card text-left">
    <div class="card-body">
        <h4 class="card-title"><?= Html::encode($this->title) ?></h4>

    <?= $this->render('_form', [
        'model' => $model,
        'modelsOption' => $modelsOption,
    ]) ?>
    </div>
</div>
