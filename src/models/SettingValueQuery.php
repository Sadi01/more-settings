<?php

namespace sadi01\moresettings\models;

/**
 * This is the ActiveQuery class for [[SettingValue]].
 *
 * @see SettingValue
 */
class SettingValueQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return SettingValue[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return SettingValue|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @inheritdoc
     * @return SettingValue|array|null
     */
    public function withSettingIdAndModelId($settingId, $modelId)
    {
        return $this->andWhere([
            'and',
            'setting_id=:settingId',
            'model_id=:modelId',
        ], [
            ':settingId' => $settingId,
            ':modelId' => $modelId
        ]);
    }

    /**
     * @inheritdoc
     * @return SettingValue|array|null
     */
    public function withSettingIdAndModelIdAndSourceModelId($settingId, $modelId, $sourceModelId)
    {
        return $this->andWhere([
            'and',
            'setting_id=:settingId',
            'model_id=:modelId',
            'source_model_id=:sourceModelId',
        ], [
            ':settingId' => $settingId,
            ':modelId' => $modelId,
            ':sourceModelId' => $sourceModelId
        ]);
    }

    /**
     * @inheritdoc
     * @return SettingValue|array|null
     */
    public function withSettingIdAndModelIdIsNull($settingId)
    {
        return $this->andWhere([
            'and',
            'setting_id=:settingId',
            ['model_id' => NULL],
        ], [
            ':settingId' => $settingId,
        ]);
    }
}
