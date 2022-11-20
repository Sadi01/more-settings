<?php
namespace sadi01\moresettings;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Class SettingsAsset
 *
 * @package sadi01\moresettings
 */
class SettingsAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@vendor/sadi01/yii2-more-settings/src/assets';
    /**
     * @inheritdoc
     */
    public $js = [
        'js/lib.js'
    ];
    /**
     * @inheritdoc
     */
    public $css = [

    ];
    /**
     * @inheritdoc
     */
    public $depends = [
        'yii\jui\JuiAsset'
    ];
}