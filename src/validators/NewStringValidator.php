<?php
namespace common\validators;

use Yii;
use yii\validators\ValidationAsset;
use yii\validators\Validator;

/**
 * NewStringValidator validates that the attribute value is of certain length.
 *
 * Note, this validator should only be used with string-typed attributes.
 *
 * $length, $min, $max can be closure too in this version.
 *
 * @author RunWidget(SADi) <sadshafiei.01@gmail.com>
 */
class NewStringValidator extends Validator
{
    /**
     * @var int|array|\Closure specifies the length limit of the value to be validated.
     * This can be specified in one of the following forms:
     * - an integer: the exact length that the value should be of;
     * - an array of one element: the minimum length that the value should be of. For example, `[8]`.
     *   This will overwrite [[min]].
     * - an array of two elements: the minimum and maximum lengths that the value should be of.
     *   For example, `[8, 128]`. This will overwrite both [[min]] and [[max]].
     * - a closure: The signature of the anonymous function should be as follows,
     *
     * ```php
     * function($model, $attribute) {
     *     // compute length
     *     return $length;
     * }
     * ```
     * @see tooShort for the customized message for a too short string.
     * @see tooLong for the customized message for a too long string.
     * @see notEqual for the customized message for a string that does not match desired length.
     */
    public $length;
    /**
     * @var int|\Closure maximum length. If not set, it means no maximum length limit.
     * @see tooLong for the customized message for a too long string.
     * The signature of the anonymous function should be as follows,
     *
     * ```php
     * function($model, $attribute) {
     *     // compute max
     *     return $max;
     * }
     * ```
     */
    public $max;
    /**
     * @var int|\Closure minimum length. If not set, it means no minimum length limit.
     * @see tooShort for the customized message for a too short string.
     * The signature of the anonymous function should be as follows,
     *
     * ```php
     * function($model, $attribute) {
     *     // compute min
     *     return $min;
     * }
     * ```
     */
    public $min;
    /**
     * @var string user-defined error message used when the value is not a string.
     */
    public $message;
    /**
     * @var string user-defined error message used when the length of the value is smaller than [[min]].
     */
    public $tooShort;
    /**
     * @var string user-defined error message used when the length of the value is greater than [[max]].
     */
    public $tooLong;
    /**
     * @var string user-defined error message used when the length of the value is not equal to [[length]].
     */
    public $notEqual;
    /**
     * @var string the encoding of the string value to be validated (e.g. 'UTF-8').
     * If this property is not set, [[\yii\base\Application::charset]] will be used.
     */
    public $encoding;


    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        if (is_array($this->length)) {
            if (isset($this->length[0])) {
                $this->min = $this->length[0];
            }
            if (isset($this->length[1])) {
                $this->max = $this->length[1];
            }
            $this->length = null;
        }
        if ($this->encoding === null) {
            $this->encoding = Yii::$app ? Yii::$app->charset : 'UTF-8';
        }
        if ($this->message === null) {
            $this->message = Yii::t('yii', '{attribute} must be a string.');
        }
        if ($this->min !== null && $this->tooShort === null) {
            $this->tooShort = Yii::t('yii', '{attribute} should contain at least {min, number} {min, plural, one{character} other{characters}}.');
        }
        if ($this->max !== null && $this->tooLong === null) {
            $this->tooLong = Yii::t('yii', '{attribute} should contain at most {max, number} {max, plural, one{character} other{characters}}.');
        }
        if ($this->length !== null && $this->notEqual === null) {
            $this->notEqual = Yii::t('yii', '{attribute} should contain {length, number} {length, plural, one{character} other{characters}}.');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function validateAttribute($model, $attribute)
    {
        if ($this->min instanceof \Closure) {
            $this->min = call_user_func($this->min, $model, $attribute);

        }
        if ($this->max instanceof \Closure) {
            $this->max = call_user_func($this->max, $model, $attribute);
        }
        if ($this->length instanceof \Closure) {
            $this->length = call_user_func($this->length, $model, $attribute);
        }

        $value = $model->$attribute;

        if (!is_string($value)) {
            $this->addError($model, $attribute, $this->message);

            return;
        }

        $length = mb_strlen($value, $this->encoding);

        if ($this->min !== null && $length < $this->min) {
            $this->addError($model, $attribute, $this->tooShort, ['min' => $this->min]);
        }
        if ($this->max !== null && $length > $this->max) {
            $this->addError($model, $attribute, $this->tooLong, ['max' => $this->max]);
        }
        if ($this->length !== null && $length !== $this->length) {
            $this->addError($model, $attribute, $this->notEqual, ['length' => $this->length]);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function validateValue($value)
    {
        if (!is_string($value)) {
            return [$this->message, []];
        }

        $length = mb_strlen($value, $this->encoding);

        if ($this->min !== null && $length < $this->min) {
            return [$this->tooShort, ['min' => $this->min]];
        }
        if ($this->max !== null && $length > $this->max) {
            return [$this->tooLong, ['max' => $this->max]];
        }
        if ($this->length !== null && $length !== $this->length) {
            return [$this->notEqual, ['length' => $this->length]];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function clientValidateAttribute($model, $attribute, $view)
    {
        if ($this->min instanceof \Closure) {
            $this->min = call_user_func($this->min, $model, $attribute);

        }
        if ($this->max instanceof \Closure) {
            $this->max = call_user_func($this->max, $model, $attribute);
        }
        if ($this->length instanceof \Closure) {
            $this->length = call_user_func($this->length, $model, $attribute);
        }

        ValidationAsset::register($view);
        $options = $this->getClientOptions($model, $attribute);

        return 'yii.validation.string(value, messages, ' . json_encode($options, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . ');';
    }

    /**
     * {@inheritdoc}
     */
    public function getClientOptions($model, $attribute)
    {
        $label = $model->getAttributeLabel($attribute);

        $options = [
            'message' => $this->formatMessage($this->message, [
                'attribute' => $label,
            ]),
        ];

        if ($this->min !== null) {
            $options['min'] = $this->min;
            $options['tooShort'] = $this->formatMessage($this->tooShort, [
                'attribute' => $label,
                'min' => $this->min,
            ]);
        }
        if ($this->max !== null) {
            $options['max'] = $this->max;
            $options['tooLong'] = $this->formatMessage($this->tooLong, [
                'attribute' => $label,
                'max' => $this->max,
            ]);
        }
        if ($this->length !== null) {
            $options['is'] = $this->length;
            $options['notEqual'] = $this->formatMessage($this->notEqual, [
                'attribute' => $label,
                'length' => $this->length,
            ]);
        }
        if ($this->skipOnEmpty) {
            $options['skipOnEmpty'] = 1;
        }

        return $options;
    }
}
