<?php

namespace common\behaviors;

use Yii;
use yii\base\Behavior;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;


class DateBehavior extends Behavior
{
    const CALENDAR_TYPE_JALALI = 1;
    const CALENDAR_TYPE_GREGORIAN = 2;

    /**
     * @var array|\Closure the attributes that will receive timestamp value
     * The signature of the anonymous function should be as follows,
     *
     * ```php
     * function($model) {
     *     // compute dateAttributes
     *     return $dateAttributes;
     * }
     * ```
     */
    public $dateAttributes = [];

    /**
     * @var array the scenarios in which the behavior will be triggered
     */
    public $scenarios = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (empty($this->dateAttributes)) {
            throw new InvalidConfigException('The "attributes" property must be set.');
        }
    }

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
        ];
    }

    /**
     * This method is called at the beginning of inserting or updating a record.
     */
    public function beforeSave()
    {
        /** @var BaseActiveRecord $model */
        $model = $this->owner;

        if ($this->dateAttributes instanceof \Closure) {
            $this->dateAttributes = call_user_func($this->dateAttributes, $model);
        }

        if (in_array($model->scenario, $this->scenarios) && !empty($this->dateAttributes)) {
            foreach ($this->dateAttributes as $attribute) {
                if ($attribute['name'] && $model->{$attribute['name']}) {
                    if ($attribute['calendarType'] === self::CALENDAR_TYPE_JALALI) {
                        $date = date_parse_from_format("Y/m/d H:i:s", $model->{$attribute['name']});
                        $model->{$attribute['name']} = Yii::$app->Pdate->jmktime($date['hour'] ? $date['hour'] : 00, $date['minute'] ? $date['minute'] : 00, $date['second'] ? $date['second'] : 00, $date['month'], $date['day'], $date['year']);
                    } elseif ($attribute['calendarType'] === self::CALENDAR_TYPE_GREGORIAN) {
                        $model->{$attribute['name']} = strtotime($model->{$attribute['name']});
                    }
                }
            }
        }
    }
}
