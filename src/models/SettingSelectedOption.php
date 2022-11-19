<?php

namespace sadi01\moresettings\models;

use Yii;

/**
 * This is the model class for table "{{%setting_selected_option}}".
 *
 * @property integer $id
 * @property integer $setting_value_id
 * @property string $option_id
 *
 * @property SettingOption $option
 * @property SettingValue $settingValue
 */
class SettingSelectedOption extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%setting_selected_option}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['setting_value_id', 'option_id'], 'required'],
            [['setting_value_id'], 'integer'],
            [['option_id'], 'string', 'max' => 50],
            [['setting_value_id', 'option_id'], 'unique', 'targetAttribute' => ['setting_value_id', 'option_id'], 'message' => 'The combination of Setting Value and Option has already been taken.'],
            [['option_id'], 'exist', 'skipOnError' => false, 'targetClass' => SettingOption::class, 'targetAttribute' => ['option_id' => 'option_key']],
            [['setting_value_id'], 'exist', 'skipOnError' => false, 'targetClass' => SettingValue::class, 'targetAttribute' => ['setting_value_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('more-settings', 'ID'),
            'setting_value_id' => Yii::t('more-settings', 'Setting Value ID'),
            'option_id' => Yii::t('more-settings', 'Option ID'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getOption()
    {
        return $this->hasOne(SettingOption::class, ['id' => 'option_id'])
            ->inverseOf('settingSelectedOptions');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettingValue()
    {
        return $this->hasOne(SettingValue::class, ['id' => 'setting_value_id'])
            ->inverseOf('settingSelectedOptions');
    }

    /**
     * @inheritdoc
     * @return SettingSelectedOptionQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SettingSelectedOptionQuery(get_called_class());
    }

}
