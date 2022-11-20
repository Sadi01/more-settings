<?php

namespace sadi01\moresettings;

use Yii;
use yii\base\BootstrapInterface;
use yii\base\Module;
use yii\i18n\PhpMessageSource;
use yii\base\InvalidArgumentException;

/**
 * Settings module definition class
 */
class SettingsModule extends Module implements BootstrapInterface
{
    public $controllerNamespace = 'sadi01\moresettings\controllers';
    //public $defaultRoute = 'default/index';
    public $settings = [

    ];

    public $rootAlias = '@webroot';
    public $webAlias = '@web';

    /**
     * @inheritdoc
     */

    public function init()
    {
        parent::init();

        //$this->defaultRoute = '';

        if (empty($this->rootAlias) || Yii::getAlias($this->rootAlias, false) === false) {
            throw new InvalidArgumentException("Invalid path alias: $this->rootAlias, Enter a valid rootAlias in config file");
        }

        if (empty($this->webAlias) || Yii::getAlias($this->webAlias, false) === false) {
            throw new InvalidArgumentException("Invalid path alias: $this->webAlias, Enter a valid webAlias in config file");
        }

    }

    public function bootstrap($app)
    {
        if (!isset($app->get('i18n')->translations['more-settings*'])) {
            $app->get('i18n')->translations['more-settings*'] = [
                'class' => PhpMessageSource::class,
                'basePath' => __DIR__ . '/messages',
                'sourceLanguage' => 'en-US',
                'fileMap' => [
                    'more-settings' => 'main.php',
                ],
            ];
        }
    }
}