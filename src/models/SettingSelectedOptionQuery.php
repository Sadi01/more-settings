<?php

namespace sadi01\moresettings\models;

/**
 * This is the ActiveQuery class for [[SettingSelectedOption]].
 *
 * @see SettingSelectedOption
 */
class SettingSelectedOptionQuery extends \yii\db\ActiveQuery
{
    /*public function active()
    {
        return $this->andWhere('[[status]]=1');
    }*/

    /**
     * @inheritdoc
     * @return SettingSelectedOption[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return SettingSelectedOption|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
