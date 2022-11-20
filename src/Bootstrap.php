<?php

namespace sadi01\moresettings;

use WebApplication;
use yii\base\Application;
use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;

class Bootstrap implements BootstrapInterface
{
    /**
     * Bootstrap method to be called during application bootstrap stage.
     * @param Application $app the application currently running
     */
    public function bootstrap($app)
    {
        if (!isset($app->get('i18n')->translations['more-settings*'])) {
            $app->get('i18n')->translations['more-settings*'] = [
                'class' => PhpMessageSource::className(),
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => 'en-US',
                'fileMap' => [
                    'more-settings' => 'main.php',
                    'common' => 'common.php',
                ],
            ];
        }
    }
}
