<?php

namespace sadi01\moresettings\models;

use yii2tech\ar\softdelete\SoftDeleteQueryBehavior;

/**
 * This is the ActiveQuery class for [[Settings]].
 *
 * @see Settings
 */
class SettingsQuery extends \yii\db\ActiveQuery
{
    public function byId($id)
    {
        return $this->andWhere(Settings::tableName() . '.id=:id', [':id' => $id]);
    }

    /**
     * @inheritdoc
     * @return Settings[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return Settings|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    public function active()
    {
        return $this->andWhere(['<>', Settings::tableName() . '.status', Settings::STATUS_DELETED]);
    }

    /**
     * @inheritdoc
     * @return Settings|array|null
     */
    public function withNameAndcatId($name, $catId)
    {
        return $this->andWhere([
            'and',
            'name=:name',
            'cat_id=:catId',
        ], [
            ':name' => $name,
            ':catId' => $catId
        ]);
    }

    public function behaviors()
    {
        return [
            'softDelete' => [
                'class' => SoftDeleteQueryBehavior::class,
                'deletedCondition' => [
                    'is_deleted' => true,
                ],
                'notDeletedCondition' => [
                    'is_deleted' => false,
                ],
            ],
        ];
    }
}