<?php
namespace sadi01\moresettings;

use sadi01\dateRangePicker\RangePickerAsset;
use yii\web\AssetBundle;
use yii\web\View;

/**
 * Class RenderSettingsFormAsset
 *
 * @package sadi01\moresettings
 */
class RenderSettingsFormAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@vendor/sadi01/yii2-more-settings/assets';
    /**
     * @inheritdoc
     */
    public $js = [
        'js/rendersettingsformbuilder.js',
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
        '\sadi01\dateRangePicker\RangePickerAsset',
        '\kartik\file\FileInputAsset',
        //'\kartik\file\FileInputThemeAsset',
        //'\kartik\file\DomPurifyAsset',
    ];
}