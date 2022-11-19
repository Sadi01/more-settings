<?php

use yii\helpers\Html;
use sadi01\moresettings\widgets\grid\GridView;
use yii\widgets\Pjax;
use sadi01\moresettings\models\Settings;

/* @var $this yii\web\View */
/* @var $searchModel sadi01\moresettings\models\SettingsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('more-settings', 'Settings');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="card text-left">
    <div class="card-body">
        <h4 class="card-title"><?= Html::encode($this->title) ?></h4>
        <?php Pjax::begin(); ?>
        <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'toolbar' => [
                'content' =>
                    Html::a('<i class="far fa-ballot"></i>', ['/moresettings/setting-cat'], [
                        'title' => Yii::t('more-settings', 'Setting categories'),
                        'data' => [
                            'toggle' => 'tooltip',
                            'pjax' => '0',
                        ],
                        'class' => 'btn btn-info btn-outline mb-2'
                    ]),
            ],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'name',
                [
                    'attribute' => 'type',
                    'value' => function ($model) {
                        return Settings::itemAlias('Types', $model->type);
                    }
                ],
                [
                    'attribute' => 'cat.title',
                    'label' => Yii::t('more-settings', 'Category')
                ],
                'label',
                [
                    'attribute' => 'status',
                    'value' => function ($model) {
                        return Settings::itemAlias('Status', $model->status);
                    }
                ],
                'description',
                'created_at:datetime',

                [
                    'class' => 'common\widgets\grid\ActionColumn',
                    'template' => "{update} {delete}"
                ],
            ],
        ]); ?>
        <?php Pjax::end(); ?>
    </div>
</div>