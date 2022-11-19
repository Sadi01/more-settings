<?php

namespace sadi01\moresettings\models;

/**
 * This is the ActiveQuery class for [[SettingOption]].
 *
 * @see SettingOption
 */
class SettingOptionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return SettingOption[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return SettingOption|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
