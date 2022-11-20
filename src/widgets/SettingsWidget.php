<?php

namespace sadi01\moresettings\widgets;

use sadi01\moresettings\helpers\CustomHtmlHelper;
use sadi01\moresettings\models\SettingCat;
use sadi01\moresettings\models\Settings;
use sadi01\moresettings\models\SettingValue;
use sadi01\moresettings\RenderSettingsFormAsset;
use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\helpers\Html;
use yii\i18n\PhpMessageSource;
use yii\web\NotFoundHttpException;

/**
 * @name        Yii2 Settings Widget
 * @author        SADi <sadshafiei.01@gmail.com>
 * @version    1.0
 * @license        MIT License
 */
class SettingsWidget extends Widget
{

    public $options = [];

    public $loadingText;

    public $loadingTag;

    public $boxTitleText;

    /**
     * $model
     * @var ActiveRecord
     */
    public $model;

    /**
     * $sourceModel
     * @var ActiveRecord
     */
    public $sourceModel;
    /**
     * @var string
     */
    public $settingName;
    /**
     * @var string
     */
    public $categoryName;
    /**
     * @var string
     */
    public $pjaxContainerId;
    /**
     * @var string
     */
    protected $renderFormWrapperId;
    /**
     * @var int primary key value of the widget model
     */
    protected $entityId;
    /**
     * @var string hash(crc32) from class name of the widget model
     */
    protected $entity;
    /**
     * @var string encrypted entity
     */
    protected $encryptedEntity;
    /**
     * @var string entity id attribute
     */
    public $entityIdAttribute = 'id';
    /**
     * @var string source entity id attribute
     */
    public $sourceEntityIdAttribute = 'id';
    /**
     * @var string custome view
     */
    public $viewFile = '../../views/default/render';
    /**
     * @var string model class
     */
    protected $modelClass;
    /**
     * @var string source model class
     */
    protected $sourceModelClass;
    /**
     * @var SettingCat Setting category model
     */
    protected $settingCatModel;
    /**
     * @var Settings Setting model
     */
    protected $settingModel;
    /**
     * @var SettingValue Setting value model
     */
    protected $settingValueModel;


    /** @inheritdoc */
    public function init()
    {
        parent::init();

        if (!$this->settingName) {
            throw new InvalidConfigException(Yii::t('more-settings', "The 'settingName' property must be set."));
        }

        if (!$this->categoryName) {
            throw new InvalidConfigException(Yii::t('more-settings', "The 'categoryName' property must be set."));
        }

        $this->settingCatModel = SettingCat::find()->withTitle($this->categoryName)->one();

        if (!$this->settingCatModel instanceof SettingCat) {
            throw new NotFoundHttpException(Yii::t('more-settings/common', 'The requested setting category does not exist.'));
        }

        if (!$this->settingCatModel->is_public && !$this->model instanceof ActiveRecord) {
            throw new InvalidConfigException(Yii::t('more-settings', "The 'model' property must be set and must extend from '{0}'.", [ActiveRecord::class]));
        }

        $this->settingModel = Settings::find()->withNameAndcatId($this->settingName, $this->settingCatModel->id)->one();

        if (!$this->settingModel instanceof Settings) {
            throw new NotFoundHttpException(Yii::t('more-settings/common', 'The requested setting does not exist.'));
        }

        if (!$this->settingCatModel->is_public) {
            $this->modelClass = get_class($this->model);
            $this->sourceModelClass = $this->sourceModel instanceof ActiveRecord ? get_class($this->sourceModel) : null;

            if (!$this->model->hasAttribute($this->entityIdAttribute)) {
                throw new InvalidConfigException(Yii::t('more-settings', "The '{0}' has not property '{1}'.", [$this->modelClass, $this->entityIdAttribute]));
            }

            if ($this->sourceModel && !$this->sourceModel->hasAttribute($this->sourceEntityIdAttribute)) {
                throw new InvalidConfigException(Yii::t('more-settings', "The '{0}' has not property '{1}'.", [$this->sourceModelClass, $this->sourceEntityIdAttribute]));
            }

            $this->settingValueModel = $this->sourceModel ?
                SettingValue::find()->withSettingIdAndModelIdAndSourceModelId($this->settingModel->id, $this->model->{$this->entityIdAttribute}, $this->sourceModel->{$this->sourceEntityIdAttribute})->one() :
                SettingValue::find()->withSettingIdAndModelId($this->settingModel->id, $this->model->{$this->entityIdAttribute})->one();

            if (!$this->settingValueModel instanceof SettingValue) {
                $this->settingValueModel = new SettingValue([
                    'setting_id' => $this->settingModel->id,
                    'model_id' => $this->model->{$this->entityIdAttribute},
                    'source_model_id' => $this->sourceModel ? $this->sourceModel->{$this->sourceEntityIdAttribute} : null
                ]);
            } else if ($this->settingValueModel->setting->type === Settings::TYPE_CHECKBOX_GROUP_INPUT) {
                $this->settingValueModel->value = ArrayHelper::map($this->settingValueModel->options, 'option_key', 'option_key');
            }
            $this->entity = hash('crc32', $this->modelClass);
            $this->entityId = $this->model->{$this->entityIdAttribute};
        } else {
            $this->settingValueModel = SettingValue::find()->withSettingIdAndModelIdIsNull($this->settingModel->id)->one();

            if (!$this->settingValueModel instanceof SettingValue) {
                $this->settingValueModel = new SettingValue([
                    'setting_id' => $this->settingModel->id,
                ]);
            } else if ($this->settingValueModel->setting->type === Settings::TYPE_CHECKBOX_GROUP_INPUT) {
                $this->settingValueModel->value = ArrayHelper::map($this->settingValueModel->options, 'option_key', 'option_key');
            }
            $this->entity = hash('crc32', $this->settingName);
            $this->entityId = $this->settingModel->id;
        }

        $this->encryptedEntity = $this->getEncryptedData([
            'setting_id' => $this->settingValueModel->setting_id,
            'model_id' => $this->settingValueModel->model_id,
            'source_model_id' => $this->settingValueModel->source_model_id,
            'is_public' => $this->settingCatModel->is_public,
            'viewFile' => $this->viewFile,
            'options' => $this->options
        ]);

        $this->renderFormWrapperId = 'render-settings-form' . $this->entity . $this->entityId . $this->getId();

        if (empty($this->pjaxContainerId)) {
            $this->pjaxContainerId = 'render-settings-form-pjax-container-' . $this->entity . $this->getId();
        }

        if (empty($this->loadingText)) {
            $this->loadingText = Yii::t('more-settings/common', 'Loading ...');
        }

        if (empty($this->loadingTag)) {
            $this->loadingTag = CustomHtmlHelper::loadingTag();
        }

        if (empty($this->boxTitleText)) {
            $this->boxTitleText = Yii::t('more-settings', 'Settings');
        }

        $this->registerTranslations();
        $this->registerAssets();
    }

    /** @inheritdoc */
    public function run()
    {
        return $this->render('render',
            [
                'options' => $this->options,
                //'model' => $this->model,
                //'modelClass' => $this->modelClass,
                'settingModel' => $this->settingModel,
                'settingValueModel' => $this->settingValueModel,
                //'settingCatModel' => $this->settingCatModel,
                //'boxTitleText' => $this->boxTitleText,
                'renderFormWrapperId' => $this->renderFormWrapperId,
                'pjaxContainerId' => $this->pjaxContainerId,
                'encryptedEntity' => $this->encryptedEntity,
                'viewFile' => $this->viewFile
            ]);
    }

    /**
     * Register assets.
     */
    protected function registerAssets()
    {
        $view = $this->getView();
        //RenderSettingsFormAsset::register($view);
        //$view->registerJs("jQuery('#{$this->renderFormWrapperId}').renderSettingsForm({$this->getClientOptions()});");
    }

    /**
     * Get client options
     * @return string
     */
    protected function getClientOptions()
    {
        $this->options['pjaxContainerId'] = '#' . $this->pjaxContainerId;
        $this->options['isNewRecord'] = $this->model->isNewRecord;
        $this->options['renderFormUrl'] = Url::to([
            '/moresettings/default/render',
            'params' => $this->getEncryptedData([
                'setting_id' => $this->settingValueModel->setting_id,
                'model_id' => $this->settingValueModel->model_id,
                'source_model_id' => $this->settingValueModel->source_model_id
            ])
        ]);
        $this->options['encryptedEntity'] = $this->encryptedEntity;
        $this->options['loadingText'] = $this->loadingText;
        $this->options['loadingTag'] = $this->loadingTag;
        return Json::encode($this->options);
    }

    protected function registerTranslations()
    {
        $i18n = Yii::$app->i18n;
        if (!isset($i18n->translations['more-settings*'])) {
            $i18n->translations['more-settings*'] = [
                'class' => PhpMessageSource::class,
                'basePath' => dirname(__DIR__) . '/messages',
                'sourceLanguage' => 'en-US',
                'fileMap' => [
                    'more-settings' => 'main.php',
                ],
            ];
        }
    }

    /**
     * Get encrypted data
     * @param $data array data to encrypt
     * @return string
     */
    protected function getEncryptedData($data)
    {
        return utf8_encode(Yii::$app->getSecurity()->encryptByKey(Json::encode($data),
            Yii::$app->getModule('moresettings')->id));
    }

}