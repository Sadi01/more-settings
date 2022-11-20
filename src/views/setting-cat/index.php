<?php

use yii\helpers\Html;
use sadi01\moresettings\widgets\grid\GridView;
use yii\widgets\Pjax;
use sadi01\moresettings\models\SettingCat;

/* @var $this yii\web\View */
/* @var $searchModel sadi01\moresettings\models\SettingCatSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('more-settings', 'Setting Cats');
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
                    Html::a('<i class="far fa-cogs"></i>', ['/moresettings/default/index'], [
                        'title' => Yii::t('more-settings', 'Settings'),
                        'data' => [
                            'toggle' => 'tooltip',
                            'pjax' => '0',
                        ],
                        'class' => 'btn btn-info btn-outline mb-2'
                    ]),
            ],
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'title',
                'description',
/*                [
                    'attribute' => 'is_public',
                    'value' => function ($model) {
                        return $model->is_public ?
                            Html::tag('i', '', ['class' => 'fas fa-check-circle text-success']) :
                            Html::tag('i', '', ['class' => 'fas fa-times-circle text-danger']);
                    },
                    'format' => 'html'
                ],*/
                [
                    'attribute' => 'is_public',
                    'value' => function ($model) {
                        return $model->is_public ?
                            Html::tag('i', '', ['class' => 'fas fa-check-circle text-success']) :
                            Html::tag('i', '', ['class' => 'fas fa-times-circle text-danger']);
                    },
                    'vAlign' => 'middle',
                    'width' => '150px',
                    'filterType' => GridView::FILTER_SELECT2,
                    'filter' => SettingCat::itemAlias('YesOrNo'),
                    'filterWidgetOptions' => [
                        'pluginOptions' => ['allowClear' => true],
                    ],

                    'filterInputOptions' => ['placeholder' => Yii::t('more-settings', 'All')],
                    'format' => 'raw'
                ],
                'model_class',
                [
                    'class' => 'sadi01\moresettings\widgets\grid\ActionColumn',
                    'template' => "{update} {delete}"
                ],
            ],
        ]); ?>
        <?php Pjax::end(); ?>
    </div>
</div>
