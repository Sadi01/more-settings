<?php

namespace sadi01\moresettings\traits;

use sadi01\moresettings\SettingsModule;

/**
 * Trait ModuleTrait
 *
 * @property-read SettingsModule $module
 */
trait ModuleTrait
{
    /**
     * @return SettingsModule
     */
    public function getModule()
    {
        return \Yii::$app->getModule('moresettings');
    }
}