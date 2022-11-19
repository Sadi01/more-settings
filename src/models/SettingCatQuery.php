<?php

namespace sadi01\moresettings\models;

use yii2tech\ar\softdelete\SoftDeleteQueryBehavior;

/**
 * This is the ActiveQuery class for [[SettingCat]].
 *
 * @see SettingCat
 */
class SettingCatQuery extends \yii\db\ActiveQuery
{
    public function byId($id)
    {
        return $this->andWhere(SettingCat::tableName() . '.id=:id', [':id' => $id]);
    }

    /**
     * @inheritdoc
     * @return SettingCat[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @inheritdoc
     * @return SettingCat|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }

    /**
     * @inheritdoc
     * @return SettingCat|array|null
     */
    public function withTitleAndModelClass($title, $modelClass)
    {
        return $this->andWhere([
            'and',
            'title=:title',
            'model_class=:modelClass',
        ], [
            ':title' => $title,
            ':modelClass' => $modelClass
        ]);
    }

    /**
     * @inheritdoc
     * @return SettingCat|array|null
     */
    public function withTitle($title)
    {
        return $this->andWhere([
            'and',
            'title=:title',
        ], [
            ':title' => $title,
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
