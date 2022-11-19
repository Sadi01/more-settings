<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model sadi01\moresettings\models\SettingCat */

$this->title = Yii::t('more-settings', 'Update Setting Cat: {name}', [
        'name' => $model->title,
    ]);
$this->params['breadcrumbs'][] = ['label' => Yii::t('more-settings', 'Setting Cats'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $model->title;
$this->params['breadcrumbs'][] = Yii::t('more-settings', 'Update');
?>
<div class="card text-left">
    <div class="card-body">
        <h4 class="card-title"><?= Html::encode($this->title) ?></h4>
        <?= $this->render('_form', [
            'model' => $model,
        ]) ?>
    </div>
</div>
