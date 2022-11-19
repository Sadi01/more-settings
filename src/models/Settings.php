<?php

namespace sadi01\moresettings\models;

use common\models\User;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\helpers\ArrayHelper;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "settings".
 *
 * @property int $id
 * @property string $name
 * @property string $type
 * @property string $label
 * @property string $cat_id
 * @property string $place_holder
 * @property string $custom_data_source
 * @property string $custom_validation_rule
 * @property boolean $skip_custom_validation_on_empty
 * @property string $description
 * @property int $status
 * @property string $helper_text
 * @property string $default_value
 * @property string $required
 * @property int $max_length
 * @property int $min_length
 * @property int $max
 * @property int $min
 * @property int $max_size
 * @property boolean $apply_separator
 * @property string $creator_id
 * @property int $calendar_type
 * @property int $number_type
 * @property int $direction
 * @property string $updated_at
 * @property string $created_at
 * @property string $created_by
 * @property string $updated_by
 * @property boolean $is_deleted
 * @property integer $deleted_at
 *
 * @property SettingCat $cat
 * @property SettingOption[] $settingOptions
 * @property SettingValue[] $settingValues
 */
class Settings extends \yii\db\ActiveRecord
{
    const STATUS_ACTIVE = 1;
    const STATUS_DELETED = 2;

    const TYPE_TEXT_INPUT = 1;
    const TYPE_NUMBER_INPUT = 2;
    const TYPE_CHECKBOX_INPUT = 3;
    const TYPE_CHECKBOX_GROUP_INPUT = 4;
    const TYPE_RADIO_GROUP_INPUT = 5;
    const TYPE_TEXT_AREA_INPUT = 6;
    const TYPE_MOBILE_INPUT = 7;
    const TYPE_PHONE_INPUT = 8;
    const TYPE_NATIONAL_CODE_INPUT = 9;
    const TYPE_IMAGE_UPLOAD_INPUT = 10;
    const TYPE_DROP_DOWN_INPUT = 11;
    const TYPE_DATE_INPUT = 12;
    const TYPE_PLATE_INPUT = 13;
    const TYPE_PRICE_INPUT = 14;
    const TYPE_EMAIL_INPUT = 15;
    const TYPE_URL_INPUT = 16;
    const TYPE_CUSTOM_DATA_SOURCE_INPUT = 17;

    const CALENDAR_TYPE_JALALI = 1;
    const CALENDAR_TYPE_GREGORIAN = 2;

    const NUMBER_TYPE_INTEGER = 1;
    const NUMBER_TYPE_FLOAT = 2;

    const BOOLEAN_NO = 0;
    const BOOLEAN_YES = 1;

    const RTL = 1;
    const LTR = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%setting}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['cat_id', 'name', 'type', 'label'], 'required'],
            [['name', 'label', 'description'], 'trim'],
            ['name', 'match', 'pattern' => '/^[a-zA-Z][\w_-]*$/'],
            ['status', 'default', 'value' => self::STATUS_ACTIVE],
            [['status'], 'in', 'range' => array_keys(self::itemAlias('CalendarTypes'))],
            [['cat_id', 'type', 'calendar_type', 'number_type'], 'integer'],
            [['name', 'label', 'place_holder', 'default_value'], 'string', 'max' => 100],
            [['description', 'helper_text'], 'string', 'max' => 200],
            [['custom_data_source', 'custom_validation_rule'], 'string', 'max' => 150],
            [['required', 'apply_separator'], 'string', 'max' => 1],
            [['cat_id'], 'exist', 'skipOnError' => false, 'targetClass' => SettingCat::class, 'targetAttribute' => ['cat_id' => 'id']],
            [['name'], 'unique', 'targetAttribute' => ['cat_id', 'name', 'is_deleted', 'deleted_at'], 'filter' => function ($query) {
                /** @var ActiveQuery $query */
                return $query->where([
                    'and',
                    [Settings::tableName() . '.cat_id' => $this->cat_id],
                    [Settings::tableName() . '.name' => $this->name],
                    [Settings::tableName() . '.is_deleted' => 0],
                    [Settings::tableName() . '.deleted_at' => null],
                ]);
            }, 'message' => Yii::t('common', 'Repetitive Setting Category Name')],
            [['created_by'], 'exist', 'skipOnError' => false, 'targetClass' => User::class, 'targetAttribute' => ['created_by' => 'id']],
            [['updated_by'], 'exist', 'skipOnError' => false, 'targetClass' => User::class, 'targetAttribute' => ['updated_by' => 'id']],

            [['required', 'apply_separator', 'skip_custom_validation_on_empty'], 'boolean'],
            [['max_length', 'min_length', 'max', 'min'], 'integer', 'min' => 1],
            ['max_size', 'integer', 'min' => 1, 'max' => 20],
            ['max', 'compare', 'skipOnError' => false,
                'operator' => '>=', 'compareAttribute' => 'min', 'type' => 'number',
                'enableClientValidation' => false
            ],
            ['max_length', 'compare', 'skipOnError' => false,
                'operator' => '>=', 'compareAttribute' => 'min_length', 'type' => 'number',
                'enableClientValidation' => false
            ],
            [['type'], 'in', 'range' => array_keys(self::itemAlias('Types'))],
            [['calendar_type'], 'in',
                'range' => array_keys(self::itemAlias('CalendarTypes')),
                'when' => function ($model) {
                    return $model->type === self::TYPE_DATE_INPUT;
                },
                'whenClient' => "function (attribute, value) {
				   return $(attribute.input).closest('form').find('.select-type').val() == " . self::TYPE_DATE_INPUT . ";				  
			    }"
            ],
            [['direction'], 'in', 'range' => array_keys(self::itemAlias('Directions'))],
            [['number_type'], 'in',
                'range' => array_keys(self::itemAlias('NumberTypes')),
                'when' => function ($model) {
                    return $model->type === self::TYPE_NUMBER_INPUT;
                },
                'whenClient' => "function (attribute, value) {
				   return $(attribute.input).closest('form').find('.select-type').val() == " . self::TYPE_NUMBER_INPUT . ";				  
			    }"
            ],

            ['number_type', 'required', 'when' => function ($model) {
                return $model->type === self::TYPE_NUMBER_INPUT;
            },
                'whenClient' => "function (attribute, value) {
				   return $(attribute.input).closest('form').find('.select-type').val() == " . self::TYPE_NUMBER_INPUT . ";				  
			    }"
            ],
            ['calendar_type', 'required', 'when' => function ($model) {
                return $model->type === self::TYPE_DATE_INPUT;
            },
                'whenClient' => "function (attribute, value) {
				   return $(attribute.input).closest('form').find('.select-type').val() == " . self::TYPE_DATE_INPUT . ";				  
			    }"
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
            'cat_id' => Yii::t('more-settings', 'Category'),
            'name' => Yii::t('more-settings', 'Name'),
            'description' => Yii::t('more-settings', 'Description'),
            'custom_data_source' => Yii::t('more-settings', 'Custom Data Source'),
            'custom_validation_rule' => Yii::t('more-settings', 'Custom Validation Rule'),
            'skip_custom_validation_on_empty' => Yii::t('more-settings', 'Skip Custom Validation On Empty'),
            'status' => Yii::t('more-settings', 'Status'),
            'type' => Yii::t('more-settings', 'Type'),
            'label' => Yii::t('more-settings', 'Label'),
            'place_holder' => Yii::t('more-settings', 'Place Holder'),
            'helper_text' => Yii::t('more-settings', 'Helper Text'),
            'default_value' => Yii::t('more-settings', 'Default Value'),
            'required' => Yii::t('more-settings', 'Required'),
            'max_length' => Yii::t('more-settings', 'Max Length'),
            'min_length' => Yii::t('more-settings', 'Min Length'),
            'max' => Yii::t('more-settings', 'Max'),
            'min' => Yii::t('more-settings', 'Min'),
            'apply_separator' => Yii::t('more-settings', 'Apply Separator'),
            'max_size' => Yii::t('more-settings', 'Max Size'),
            'calendar_type' => Yii::t('more-settings', 'Calendar Type'),
            'number_type' => Yii::t('more-settings', 'Number Type'),
            'direction' => Yii::t('more-settings', 'Input Direction'),
            'created_by' => Yii::t('more-settings', 'Created By'),
            'updated_by' => Yii::t('more-settings', 'Updated By'),
            'created_at' => Yii::t('more-settings', 'Created At'),
            'updated_at' => Yii::t('more-settings', 'Updated At'),
            'is_deleted' => Yii::t('more-settings', 'Is Deleted'),
            'deleted_at' => Yii::t('more-settings', 'Deleted At'),
        ];
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCat()
    {
        return $this->hasOne(SettingCat::class, ['id' => 'cat_id'])
            ->inverseOf('settings');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingOptions()
    {
        return $this->hasMany(SettingOption::class, ['setting_id' => 'id'])
            ->orderBy('order_id')
            ->inverseOf('setting');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingValues()
    {
        return $this->hasMany(SettingValue::class, ['setting_id' => 'id'])
            ->inverseOf('setting');
    }

    public static function itemAlias($type, $code = NULL)
    {
        $list_data = [];
        if ($type == 'List') {
            $list_data = ArrayHelper::map(self::find()->all(), 'id', 'name');
        }

        $_items = [
            'Status' => [
                self::STATUS_ACTIVE => Yii::t("more-settings", "Active"),
                self::STATUS_DELETED => Yii::t("more-settings", "Deleted"),
            ],
            'Types' => [
                self::TYPE_TEXT_INPUT => Yii::t('more-settings', 'Text Input'),
                self::TYPE_NUMBER_INPUT => Yii::t('more-settings', 'Number Input'),
                self::TYPE_CHECKBOX_INPUT => Yii::t('more-settings', 'Checkbox Input'),
                self::TYPE_CHECKBOX_GROUP_INPUT => Yii::t('more-settings', 'Checkbox Group Input'),
                self::TYPE_RADIO_GROUP_INPUT => Yii::t('more-settings', 'Radio Group Input'),
                self::TYPE_TEXT_AREA_INPUT => Yii::t('more-settings', 'Text Area Input'),
                self::TYPE_MOBILE_INPUT => Yii::t('more-settings', 'Mobile Input'),
                self::TYPE_PHONE_INPUT => Yii::t('more-settings', 'Phone Input'),
                self::TYPE_NATIONAL_CODE_INPUT => Yii::t('more-settings', 'National Code Input'),
                self::TYPE_IMAGE_UPLOAD_INPUT => Yii::t('more-settings', 'Image Upload Input'),
                self::TYPE_DROP_DOWN_INPUT => Yii::t('more-settings', 'DropDown Input'),
                self::TYPE_DATE_INPUT => Yii::t('more-settings', 'Date Input'),
                self::TYPE_PLATE_INPUT => Yii::t('more-settings', 'Plate Input'),
                self::TYPE_PRICE_INPUT => Yii::t('more-settings', 'Price Input'),
                self::TYPE_EMAIL_INPUT => Yii::t('more-settings', 'Email Input'),
                self::TYPE_URL_INPUT => Yii::t('more-settings', 'Url Input'),
                self::TYPE_CUSTOM_DATA_SOURCE_INPUT => Yii::t('more-settings', 'Custom Data Source Input'),
            ],
            'CalendarTypes' => [
                self::CALENDAR_TYPE_JALALI => Yii::t('more-settings', 'Jalali Calendar'),
                self::CALENDAR_TYPE_GREGORIAN => Yii::t('more-settings', 'Gregorian Calendar'),
            ],
            'NumberTypes' => [
                self::NUMBER_TYPE_FLOAT => Yii::t('more-settings', 'Float Number'),
                self::NUMBER_TYPE_INTEGER => Yii::t('more-settings', 'Integer Number'),
            ],
            'YesOrNo' => [
                self::BOOLEAN_NO => Yii::t('more-settings', 'No'),
                self::BOOLEAN_YES => Yii::t('more-settings', 'Yes'),
            ],
            'Directions' => [
                self::RTL => Yii::t('more-settings', 'Right To Left'),
                self::LTR => Yii::t('more-settings', 'Left To Right'),
            ],
            'DirectionsClass' => [
                self::RTL => 'dir-rtl',
                self::LTR => 'dir-ltr',
            ],
            'List' => $list_data,
            'CustomValidationRules' => [
                'required' => Yii::t('more-settings', 'Required'),
                'checkMultiIpAddresses' => Yii::t('more-settings', 'Check Multi Ip Addresses With # As Separator')
            ],
        ];

        if (isset($code))
            return isset($_items[$type][$code]) ? $_items[$type][$code] : false;
        else
            return isset($_items[$type]) ? $_items[$type] : false;
    }


    /**
     * @inheritdoc
     * @return SettingsQuery the active query used by this AR class.
     */
    public static function find()
    {
        $query = new SettingsQuery(get_called_class());
        return $query->active();
    }


    public function beforeSoftDelete()
    {
        try {
            return $this->delete();
        } catch (\Exception $exception) {
            // PHP < 7.0
            return false;
        } catch (\Throwable $exception) {
            // PHP >= 7.0
            return false;
        }
    }

    public function beforeRestore()
    {
        return true;
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
            'softDeleteBehavior' => [
                'class' => SoftDeleteBehavior::class,
                'softDeleteAttributeValues' => [
                    'is_deleted' => true,
                    'deleted_at' => time()
                ],
                'restoreAttributeValues' => [
                    'is_deleted' => false,
                    'deleted_at' => null
                ],
                'replaceRegularDelete' => false, // mutate native `delete()` method
                'allowDeleteCallback' => function ($user) {
                    return false;
                },
                'invokeDeleteEvents' => false
            ],
        ];
    }
}