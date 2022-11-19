<?php

namespace sadi01\moresettings\models;

use Yii;
use sadi01\moresettings\models\SettingCatQuery;
use yii2tech\ar\softdelete\SoftDeleteBehavior;

/**
 * This is the model class for table "{{%setting_cat}}".
 *
 * @property integer $id
 * @property string $title
 * @property string $description
 * @property string $model_class
 * @property string $source_model_class
 * @property boolean is_public
 * @property boolean $is_deleted
 * @property integer $deleted_at
 *
 * @property Setting[] $settings
 */
class SettingCat extends \yii\db\ActiveRecord
{
    const BOOLEAN_NO = 0;
    const BOOLEAN_YES = 1;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%setting_cat}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['model_class'], 'required', 'when' => function ($model) {
                return !$model->is_public;
            }, 'whenClient' => "function (attribute, value) {
                    return $('#settingcat-is_public').prop('checked') == false;
                }"
            ],
            [['title'], 'string', 'max' => 100],
            [['description'], 'string', 'max' => 150],
            [['model_class', 'source_model_class'], 'string', 'max' => 255],
            ['is_public', 'boolean'],
            [['model_class', 'source_model_class'], function ($attribute, $params, $validator) {
                if (!class_exists($this->$attribute)) {
                    $this->addError($attribute, \Yii::t('rbac', $this->getAttributeLabel($attribute) . ' "{0}" does not exist', $this->$attribute));
                }
            }],
            [['title'], 'unique', 'targetAttribute' => ['model_class', 'title', 'is_deleted', 'deleted_at'], 'filter' => function ($query) {
                /** @var ActiveQuery $query */
                return $query->where([
                    'and',
                    [SettingCat::tableName() . '.model_class' => $this->model_class],
                    [SettingCat::tableName() . '.title' => $this->title],
                    [SettingCat::tableName() . '.is_deleted' => 0],
                    [SettingCat::tableName() . '.deleted_at' => null],
                ]);
            }, 'message' => Yii::t('more-settings', 'This Title has already been taken.')],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => Yii::t('more-settings', 'ID'),
            'title' => Yii::t('more-settings', 'Title'),
            'description' => Yii::t('more-settings', 'Description'),
            'model_class' => Yii::t('more-settings', 'Model Class'),
            'source_model_class' => Yii::t('more-settings', 'Source Model Class'),
            'is_public' => Yii::t('more-settings', 'Public Category'),
            'is_deleted' => Yii::t('more-settings', 'Is Deleted'),
            'deleted_at' => Yii::t('more-settings', 'Deleted At'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSettings()
    {
        return $this->hasMany(Settings::class, ['cat_id' => 'id'])
            ->inverseOf('cat');
    }

    /**
     * @inheritdoc
     * @return SettingCatQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new SettingCatQuery(get_called_class());
    }

    public static function itemAlias($type, $code = NULL)
    {
        $list_data = [];
        if ($type == 'List') {
            $list_data = ArrayHelper::map(self::find()->all(), 'id', 'name');
        }

        $_items = [
            'YesOrNo' => [
                self::BOOLEAN_NO => Yii::t('more-settings', 'No'),
                self::BOOLEAN_YES => Yii::t('more-settings', 'Yes'),
            ],
        ];

        if (isset($code))
            return isset($_items[$type][$code]) ? $_items[$type][$code] : false;
        else
            return isset($_items[$type]) ? $_items[$type] : false;
    }

    public function behaviors()
    {
        return [
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