<?php

namespace sadi01\moresettings\models;

use sadi01\moresettings\behaviors\DateBehavior;
use sadi01\moresettings\behaviors\UploadImageBehavior;
use common\models\User;
use sadi01\moresettings\validators\JDateValidator;
use sadi01\moresettings\validators\NationalCodeValidator;
use sadi01\moresettings\validators\NewImageValidator;
use sadi01\moresettings\validators\NewNumberValidator;
use sadi01\moresettings\validators\NewStringValidator;
use sadi01\moresettings\traits\ModuleTrait;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;
use yii\validators\RegularExpressionValidator;

/**
 * This is the model class for table "{{%setting_value}}".
 *
 * @property integer $id
 * @property integer $setting_id
 * @property integer $model_id
 * @property integer $source_model_id
 * @property string $value
 * @property integer $created_by
 * @property integer $update_by
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Settings $setting
 * @property SettingSelectedOption[] $settingSelectedOptions
 * @property SettingOption[] $options
 */
class SettingValue extends \yii\db\ActiveRecord
{
    use ModuleTrait;

    const SCENARIO_CHANGE_VALUE = "ChangeValue";
    const SCENARIO_CHANGE_VALUE_ARRAY = "ChangeValueArray";

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%setting_value}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['setting_id', 'model_id'], 'required'],
            [['setting_id', 'model_id', 'source_model_id'], 'integer'],
            [['setting_id', 'model_id'], 'unique', 'targetAttribute' => ['setting_id', 'model_id'], 'message' => 'The combination of Setting and Model has already been taken.'],
            [['setting_id'], 'exist', 'skipOnError' => false, 'targetClass' => Settings::class, 'targetAttribute' => ['setting_id' => 'id']],
            [['created_by'], 'exist', 'skipOnError' => false, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => false, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],

            [['value'], 'validateValue', 'skipOnError' => false, 'skipOnEmpty' => false, 'on' => [self::SCENARIO_CHANGE_VALUE], 'params' => ['test' => 'hi']],

            // Range Rules (Allowed Array)
            ['value', 'in', 'skipOnError' => false,
                'allowArray' => true,
                'range' => function ($model, $attribute) {
                    return ArrayHelper::map($this->setting->settingOptions, 'option_key', 'option_key');
                },
                'when' => function ($model, $attribute) {
                    return $this->setting->type === Settings::TYPE_CHECKBOX_GROUP_INPUT;
                },
                'whenClient' => "function (attribute, value) {
                            return $(attribute.input).data('range') === 1;
			        }"
            ],
            // Range Rules (Not Allowed Array)
            ['value', 'in', 'skipOnError' => false,
                'range' => function ($model, $attribute) {
                    return ArrayHelper::map($this->setting->settingOptions, 'option_key', 'option_key');
                },
                'when' => function ($model, $attribute) {
                    return in_array($this->setting->type, [
                        Settings::TYPE_DROP_DOWN_INPUT, Settings::TYPE_RADIO_GROUP_INPUT
                    ]);
                },
                'whenClient' => "function (attribute, value) {
                            return $(attribute.input).data('just-range') === 1;
			        }"
            ],
            // Range Rules (Not Allowed Array)
            ['value', 'in', 'skipOnError' => false,
                'range' => $this->setting->custom_data_source ? array_keys(Settings::itemAlias($this->setting->custom_data_source)) : [],
                'when' => function ($model, $attribute) {
                    return $this->setting->type === Settings::TYPE_CUSTOM_DATA_SOURCE_INPUT;
                },
                'whenClient' => "function (attribute, value) {
                            return $(attribute.input).data('just-custom-range') === 1;
			        }"
            ],
            // Required Rules
            ['value', 'required',
                'when' => function ($model, $attribute) {
                    if ($this->setting->type === Settings::TYPE_IMAGE_UPLOAD_INPUT) {
                        return $model->isNewRecord ? $this->setting->required : false;
                    } else {
                        return $this->setting->required;
                    }
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('required') === 1;
			        }"
            ],
            //Integer Rules
            ['value', NewNumberValidator::class, 'integerOnly' => true,
                'min' => function ($model, $attribute) {
                    return $this->setting->min;
                },
                'max' => function ($model, $attribute) {
                    return $this->setting->max;
                },
                'when' => function ($model, $attribute) {
                    return $this->setting->number_type === Settings::NUMBER_TYPE_INTEGER &&
                        in_array($this->setting->type, [
                            Settings::TYPE_NUMBER_INPUT, Settings::TYPE_PRICE_INPUT
                        ]);
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('integer') === 1;
			        }"
            ],
            //Double Rules
            ['value', NewNumberValidator::class, 'integerOnly' => false,
                'min' => function ($model, $attribute) {
                    return $this->setting->min;
                },
                'max' => function ($model, $attribute) {
                    return $this->setting->max;
                },
                'when' => function ($model, $attribute) {
                    return $this->setting->number_type === Settings::NUMBER_TYPE_FLOAT &&
                        in_array($this->setting->type, [
                            Settings::TYPE_NUMBER_INPUT, Settings::TYPE_PRICE_INPUT
                        ]);
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('double') === 1;
			        }"
            ],
            //Plate Rules
            ['value', 'match',
                'pattern' => '/^[0-9]{2}[آابپتثجچحخدذرزژسشصضطظعغفقکگلمنوهی][0-9]{3}\-[0-9]{2}$/',
                'when' => function ($model, $attribute) {
                    return $this->setting->type === Settings::TYPE_PLATE_INPUT;
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('plate') === 1;
			        }"
            ],
            //String Rules
            ['value', NewStringValidator::class,
                'min' => function ($model, $attribute) {
                    return $this->setting->min_length;
                },
                'max' => function ($model, $attribute) {
                    return $this->setting->max_length;
                },
                'when' => function ($model, $attribute) {
                    return in_array($this->setting->type, [
                        Settings::TYPE_TEXT_INPUT, Settings::TYPE_TEXT_AREA_INPUT, Settings::TYPE_PLATE_INPUT
                    ]);
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('string') === 1;
			        }"
            ],
            //Image Rules
            ['value', NewImageValidator::class, 'skipOnError' => false,
                'maxSize' => function ($model, $attribute) {
                    return $this->setting->max_size * 1024 * 1024;
                },
                'when' => function ($model, $attribute) {
                    return in_array($this->setting->type, [
                        Settings::TYPE_IMAGE_UPLOAD_INPUT
                    ]);
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('image') === 1;
			        }"
            ],
            //Boolean Rules
            ['value', 'boolean',
                'when' => function ($model, $attribute) {
                    return $this->setting->type === Settings::TYPE_CHECKBOX_INPUT;
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('boolean') === 1;
			        }"
            ],
            //Mobile Rules
            ['value', 'match',
                'pattern' => '/^([0]{1}[9]{1}[0-9]{9})$/',
                'when' => function ($model, $attribute) {
                    return $this->setting->type === Settings::TYPE_MOBILE_INPUT;
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('mobile') === 1;
			        }"
            ],
            //Phone Rules
            ['value', 'match',
                'pattern' => '/^([0]{1}[0-9]{10})$/',
                'when' => function ($model, $attribute) {
                    return $this->setting->type === Settings::TYPE_PHONE_INPUT;
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('phone') === 1;
			        }"
            ],
            //National Code Rules
            ['value', NationalCodeValidator::class,
                'skipOnError' => false,
                'when' => function ($model, $attribute) {
                    return $this->setting->type === Settings::TYPE_NATIONAL_CODE_INPUT;
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('nationalCode') === 1;
			        }"
            ],
            //Email Rules
            ['value', 'email',
                'when' => function ($model, $attribute) {
                    return $this->setting->type === Settings::TYPE_EMAIL_INPUT;
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('email') === 1;
			        }"
            ],
            //Url Rules
            ['value', 'url',
                'when' => function ($model, $attribute) {
                    return $this->setting->type === Settings::TYPE_URL_INPUT;
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('url') === 1;
			        }"
            ],
            //Date Rules
            ['value', 'date', 'format' => 'yyyy/MM/dd', 'min' => '1000/01/01',
                'when' => function ($model, $attribute) {
                    return ($this->setting->type === Settings::TYPE_DATE_INPUT && $this->setting->calendar_type === Settings::CALENDAR_TYPE_GREGORIAN);
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('date') === 1;
			        }"
            ],
            ['value', JDateValidator::class,
                'when' => function ($model, $attribute) {
                    return ($this->setting->type === Settings::TYPE_DATE_INPUT && $this->setting->calendar_type === Settings::CALENDAR_TYPE_JALALI);
                },
                'whenClient' => "function (attribute, value) {
                           return $(attribute.input).data('jdate') === 1;
			        }"
            ],
            //Default Rules, ensure empty values are stored as NULL in the database
            ['value', 'default',
                'value' => null,
                'when' => function ($model, $attribute) {
                    return $this->setting->type === Settings::TYPE_DATE_INPUT;
                }
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('more-settings', 'ID'),
            'setting_id' => Yii::t('more-settings', 'Setting ID'),
            'model_id' => Yii::t('more-settings', 'Model ID'),
            'source_model_id' => Yii::t('more-settings', 'Source Model ID'),
            'value' => $this->setting->label,
            'created_by' => Yii::t('more-settings', 'Created By'),
            'update_by' => Yii::t('more-settings', 'Update By'),
            'created_at' => Yii::t('more-settings', 'Created At'),
            'updated_at' => Yii::t('more-settings', 'Updated At'),
        ];
    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CHANGE_VALUE] = ['value'];
        $scenarios[self::SCENARIO_CHANGE_VALUE_ARRAY] = ['!value'];
        return $scenarios;
    }

    public function validateValue($attribute, $params, $validator)
    {
        $validateFunction = $this->setting->custom_validation_rule;
        if (($this->$attribute || !$this->setting->skip_custom_validation_on_empty) && $validateFunction) {
            $this->$validateFunction($attribute, $params, $validator);
        }
    }

    public function checkMultiIpAddresses($attribute, $params, $validator)
    {
        $validator = new RegularExpressionValidator([
            'pattern' => "/^(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9]))(#(?:(?:2(?:[0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])\.){3}(?:(?:2([0-4][0-9]|5[0-5])|[0-1]?[0-9]?[0-9])))*$/"
        ]);

        if (!$validator->validate($this->$attribute, $error)) {
            $this->addError($attribute, $error);
        }
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingSelectedOptions()
    {
        return $this->hasMany(SettingSelectedOption::class, ['setting_value_id' => 'id'])
            ->inverseOf('settingValue');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOptions()
    {
        return $this->hasMany(SettingOption::class, ['option_key' => 'option_id'])
            ->via('settingSelectedOptions');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSetting()
    {
        return $this->hasOne(Settings::class, ['id' => 'setting_id'])
            ->inverseOf('settingValues');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }

    public static function setValue($categoryName, $settingName, ActiveRecord $model = null, $newValue, $entityIdAttribute = 'id', ActiveRecord $sourceModel = null, $sourceEntityIdAttribute = 'id')
    {
        $settingCatModel = SettingCat::find()->withTitle($categoryName)->one();

        if (!$settingCatModel instanceof SettingCat) {
            return false;
        }

        $settingModel = Settings::find()->withNameAndcatId($settingName, $settingCatModel->id)->one();

        if (!$settingModel instanceof Settings) {
            return false;
        }

        if (!$settingCatModel->is_public) {
            if (!$model instanceof ActiveRecord || !$model->hasAttribute($entityIdAttribute)) {
                return false;
            }

            if ($settingCatModel->source_model_class && (!$sourceModel instanceof ActiveRecord || !$sourceModel->hasAttribute($sourceEntityIdAttribute))) {
                return false;
            }

            if ($settingCatModel->source_model_class) {
                $settingValueModel = SettingValue::find()->withSettingIdAndModelIdAndSourceModelId($settingModel->id, $model->{$entityIdAttribute}, $sourceModel->{$sourceEntityIdAttribute})->one();
            } else {
                $settingValueModel = SettingValue::find()->withSettingIdAndModelId($settingModel->id, $model->{$entityIdAttribute})->one();
            }

            $settingValueModel = SettingValue::find()->withSettingIdAndModelId($settingModel->id, $model->{$entityIdAttribute})->one();

            if (!$settingValueModel instanceof SettingValue) {
                $settingValueModel = new SettingValue([
                    'setting_id' => $settingModel->id,
                    'model_id' => $model->{$entityIdAttribute}
                ]);
            }
        } else {
            $settingValueModel = SettingValue::find()->withSettingIdAndModelIdIsNull($settingModel->id)->one();

            if (!$settingValueModel instanceof SettingValue) {
                $settingValueModel = new SettingValue([
                    'setting_id' => $settingModel->id,
                ]);
            }
        }

        $settingValueModel->value = $newValue;
        $settingValueModel->save();
    }

    public static function getValue($categoryName, $settingName, ActiveRecord $model = null, ActiveRecord $sourceModel = null, $return = 'key', $entityIdAttribute = 'id', $sourceEntityIdAttribute = 'id')
    {
        $settingCatModel = SettingCat::find()->withTitle($categoryName)->one();

        if (!$settingCatModel instanceof SettingCat) {
            return false;
        }

        $settingModel = Settings::find()->withNameAndcatId($settingName, $settingCatModel->id)->one();

        if (!$settingModel instanceof Settings) {
            return false;
        }

        if (!$settingCatModel->is_public) {
            if (!$model instanceof ActiveRecord || !$model->hasAttribute($entityIdAttribute)) {
                return false;
            }

            if ($settingCatModel->source_model_class && (!$sourceModel instanceof ActiveRecord || !$sourceModel->hasAttribute($sourceEntityIdAttribute))) {
                return false;
            }

            if ($settingCatModel->source_model_class) {
                $settingValueModel = SettingValue::find()->withSettingIdAndModelIdAndSourceModelId($settingModel->id, $model->{$entityIdAttribute}, $sourceModel->{$sourceEntityIdAttribute})->one();
            } else {
                $settingValueModel = SettingValue::find()->withSettingIdAndModelId($settingModel->id, $model->{$entityIdAttribute})->one();
            }
        } else {
            $settingValueModel = SettingValue::find()->withSettingIdAndModelIdIsNull($settingModel->id)->one();
        }

        if ($settingValueModel instanceof SettingValue) {
            switch ($settingValueModel->setting->type) {
                case Settings::TYPE_TEXT_INPUT:
                case Settings::TYPE_PLATE_INPUT:
                case Settings::TYPE_URL_INPUT:
                case Settings::TYPE_NATIONAL_CODE_INPUT:
                case Settings::TYPE_EMAIL_INPUT:
                case Settings::TYPE_TEXT_AREA_INPUT:
                case Settings::TYPE_PHONE_INPUT:
                case Settings::TYPE_MOBILE_INPUT:
                case Settings::TYPE_PRICE_INPUT:
                case Settings::TYPE_NUMBER_INPUT:
                    return $settingValueModel->value;
                    break;
                case Settings::TYPE_CHECKBOX_GROUP_INPUT:
                    $options = ArrayHelper::map($settingValueModel->options, 'option_key', 'name');
                    return ($return === 'key') ? array_keys($options) : $options;
                    break;
                case Settings::TYPE_RADIO_GROUP_INPUT:
                case Settings::TYPE_DROP_DOWN_INPUT:
                    $options = ArrayHelper::map($settingValueModel->setting->getSettingOptions()
                        ->andWhere(['and', SettingOption::tableName() . '.option_key=:value'], [':value' => $settingValueModel->value])->all(),
                        'option_key', 'name');

                    switch ($return) {
                        case 'key':
                            return $options ? $settingValueModel->value : false;
                            break;
                        case 'value':
                            return $options ? $options[$settingValueModel->value] : false;
                            break;
                        case 'options':
                            return $options;
                            break;
                    }
                    break;
                case Settings::TYPE_IMAGE_UPLOAD_INPUT:
                    return $settingValueModel->getUploadUrl('value');
                    break;
                case Settings::TYPE_CHECKBOX_INPUT:
                    return (boolean)$settingValueModel->value;
                    break;
                case Settings::TYPE_DATE_INPUT:
                    return $settingValueModel->value;
                    break;
                case Settings::TYPE_CUSTOM_DATA_SOURCE_INPUT:
                    return ($return === 'key') ? $settingValueModel->value : Settings::itemAlias($settingValueModel->setting->custom_data_source, $settingValueModel->value);
                    break;
            }
        } else {

            return $settingModel->default_value;
        }

        return false;
    }

    /**
     * @inheritdoc
     * @return SettingValueQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SettingValueQuery(get_called_class());
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                //'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class,
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
            ],
            [
                'class' => DateBehavior::class,
                'dateAttributes' => function ($model) {
                    $dateAttributes = [];
                    if ($this->setting->type === Settings::TYPE_DATE_INPUT) {
                        $dateAttributes[] = [
                            'name' => 'value',
                            'calendarType' => $this->setting->calendar_type
                        ];
                    }
                    return $dateAttributes;
                },
                'scenarios' => [
                    self::SCENARIO_CHANGE_VALUE,
                ],
            ],

            //use UploadImageBehavior to manage image upload
            [
                'class' => UploadImageBehavior::class,
                'attribute' => function ($model) {
                    if ($this->setting->type === Settings::TYPE_IMAGE_UPLOAD_INPUT) {
                        return "value";
                    }
                    return null;
                },
                //'instanceByName' => true,
                'scenarios' => [self::SCENARIO_CHANGE_VALUE],
                //'placeholder' => "{$this->module->rootAlias}/assets/images/default.jpg",
                'basePath' => "{$this->module->rootAlias}/upload/{$this->formName()}/{primaryKey}",
                'path' => "{$this->module->rootAlias}/upload/{$this->formName()}/{primaryKey}/setting-{setting_id}/images",
                'url' => "{$this->module->webAlias}/upload/{$this->formName()}/{primaryKey}/setting-{setting_id}/images",
                'thumbPath' => "{$this->module->rootAlias}/upload/{$this->formName()}/{primaryKey}/setting-{setting_id}/images/thumb",
                'thumbUrl' => "{$this->module->webAlias}/upload/{$this->formName()}/{primaryKey}/setting-{setting_id}/images/thumb",
                'thumbs' => [
                    'thumb' => ['width' => 400, 'quality' => 90],
                    'preview' => ['width' => 200, 'height' => 200],
                ],
            ]
        ];
    }
}