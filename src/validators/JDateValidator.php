<?php

namespace common\validators;

use Yii;
use common\assetBundles\RunwidgetValidationAsset;
use yii\validators\Validator;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * JDateValidator validates that the attribute value is a Jalali Date.
 *
 * @author RunWidget(SADi) <sadshafiei.01@gmail.com>
 */

class JDateValidator extends Validator
{
    protected $datePattern = '#^([0-9]?[0-9]?[0-9]{2}[- /.](0?[1-9]|1[012])[- /.](0?[1-9]|[12][0-9]|3[01]))*$#';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} is invalid.');
        }
    }

    public function validateAttribute($model, $attribute)
    {
        $jDate = $model->$attribute;

        if(($valid = preg_match($this->datePattern, $jDate)))
        {
            $dateParsed = date_parse_from_format("Y/m/d", $jDate);
            $valid = Yii::$app->Pdate->jcheckdate($dateParsed['month'], $dateParsed['day'], $dateParsed['year']);
        }

        return $valid ? null :  $this->addError($model, $attribute, $this->message);
    }

    public function validateValue($value)
    {
        $jDate = $value;

        if(($valid = preg_match($this->datePattern, $jDate)))
        {
            $dateParsed = date_parse_from_format("Y/m/d", $jDate);
            $valid = Yii::$app->pDate->jcheckdate($dateParsed['month'], $dateParsed['day'], $dateParsed['year']);
        }

        return $valid ? null :  [$this->message, []];
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        RunwidgetValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.runwidgetvalidation.jalalidate(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * @inheritdoc
     */
    public function getClientOptions($model, $attribute)
    {
        $datePattern = Html::escapeJsRegularExpression($this->datePattern);

        $options = [
            'datePattern' => new JsExpression($datePattern),
            'message' => $this->formatMessage($this->message, [
                'attribute' => $model->getAttributeLabel($attribute),
            ]),
        ];
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
