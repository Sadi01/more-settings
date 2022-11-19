<?php

namespace common\assetBundles;

use yii\web\AssetBundle;


class RunwidgetValidationAsset extends AssetBundle
{
    public $sourcePath = '@common/assets';
    public $js = [
        'js/runwidget.validation.js',
    ];
    public $depends = [
        'yii\web\YiiAsset',
    ];
}
