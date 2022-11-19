<?php

namespace sadi01\moresettings\models;

use Yii;

/**
 * This is the model class for table "{{%setting_option}}".
 *
 * @property integer $id
 * @property integer $setting_id
 * @property string $option_key
 * @property string $name
 * @property string $description
 * @property integer $order_id
 *
 * @property Settings $setting
 * @property SettingSelectedOption[] $settingSelectedOptions
 * @property SettingValue[] $settingValues
 */
class SettingOption extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%setting_option}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['option_key', 'name', 'order_id'], 'required'],
            [['setting_id', 'order_id'], 'integer'],
            [['name'], 'string', 'max' => 150],
            [['option_key'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 200],
            [['setting_id', 'option_key'], 'unique', 'targetAttribute' => ['setting_id', 'option_key'], 'message' => 'The combination of Setting and Key has already been taken.'],
            [['setting_id', 'name'], 'unique', 'targetAttribute' => ['setting_id', 'name'], 'message' => 'The combination of Setting and Name has already been taken.'],
            [['setting_id'], 'exist', 'skipOnError' => false, 'targetClass' => Settings::class, 'targetAttribute' => ['setting_id' => 'id']],
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
            'option_key' => Yii::t('more-settings', 'Option Key'),
            'name' => Yii::t('more-settings', 'Name'),
            'description' => Yii::t('more-settings', 'Description'),
            'order_id' => Yii::t('more-settings', 'Order ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSetting()
    {
        return $this->hasOne(Settings::class, ['id' => 'setting_id'])
            ->inverseOf('settingOptions');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingSelectedOptions()
    {
        return $this->hasMany(SettingSelectedOption::class, ['option_id' => 'option_key'])
            ->inverseOf('option');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingValues()
    {
        return $this->hasMany(SettingValue::class, ['id' => 'setting_value_id'])
            ->via('settingSelectedOptions');
    }

    /**
     * @inheritdoc
     * @return SettingOptionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SettingOptionQuery(get_called_class());
    }
}