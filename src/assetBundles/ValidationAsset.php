<?php

namespace sadi01\moresettings\assetBundles;

use yii\web\AssetBundle;

class ValidationAsset extends AssetBundle
{
    public $sourcePath = __DIR__ . '/../assets';

    public $js = [
        'js/validation.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}
