<?php

namespace sadi01\moresettings\validators;

use Yii;
use sadi01\moresettings\assetBundles\ValidationAsset;
use yii\validators\Validator;
use yii\helpers\Html;
use yii\helpers\Json;
use yii\web\JsExpression;

/**
 * NationalCodeValidator validates that the attribute value is a national code.
 *
 * @author SADi <sadshafiei.01@gmail.com>
 */

class NationalCodeValidator extends Validator
{
    protected $oneNumberRepeatedPattern = '/^([^\D1])\1{9}$/';
    protected $tenDigitsNumberPattern = '/^[0-9]{10}$/';

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
        $sum = 0;
        $strCode = $model->$attribute;

        $valid = !preg_match($this->oneNumberRepeatedPattern, $strCode)
            && preg_match($this->tenDigitsNumberPattern, $strCode)
            && $strCode != '0123456789';
        if ($valid) {
            $controlNumber = substr($strCode, 9, 1);
            for ($i = 0; $i < 9; $i++)
                $sum += substr($strCode, $i, 1) * (10 - $i);
            $rem = $sum % 11;
            $valid = (($rem < 2 && $controlNumber == $rem) || ($rem >= 2 && $controlNumber == (11 - $rem)));
        }

        return $valid ? null :  $this->addError($model, $attribute, $this->message);
    }

    public function validateValue($value)
    {
        $sum = 0;
        $strCode = $value;

        $valid = !preg_match($this->oneNumberRepeatedPattern, $strCode)
            && preg_match($this->tenDigitsNumberPattern, $strCode)
            && $strCode != '0123456789';
        if ($valid) {
            $controlNumber = substr($strCode, 9, 1);
            for ($i = 0; $i < 9; $i++)
                $sum += substr($strCode, $i, 1) * (10 - $i);
            $rem = $sum % 11;
            $valid = (($rem < 2 && $controlNumber == $rem) || ($rem >= 2 && $controlNumber == (11 - $rem)));
        }

        return $valid ? null :  [$this->message, []];
    }

    /**
     * @inheritdoc
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.nationalcode(value, messages, ' . Json::htmlEncode($options) . ');';
    }

    /**
     * @inheritdoc
     */
    public function getClientOptions($model, $attribute)
    {
        $oneNumberRepeatedPattern = Html::escapeJsRegularExpression($this->oneNumberRepeatedPattern);
        $tenDigitsNumberPattern = Html::escapeJsRegularExpression($this->tenDigitsNumberPattern);

        $options = [
            'oneNumberRepeatedPattern' => new JsExpression($oneNumberRepeatedPattern),
            'tenDigitsNumberPattern' => new JsExpression($tenDigitsNumberPattern),
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
