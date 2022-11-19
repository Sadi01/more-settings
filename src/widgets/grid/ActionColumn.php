<?php

namespace common\widgets\grid;

use Yii;
use yii\helpers\Html;
use yii\grid\ActionColumn as YiiActionColumn;

/**
 * ActionColumn is a column for the [[GridView]] widget that displays buttons for viewing and manipulating the items.
 *
 */
class ActionColumn extends YiiActionColumn
{
    /**
     * Initializes the default button rendering callbacks.
     */
    protected function initDefaultButtons()
    {
        $this->initDefaultButton('view', 'eye');
        $this->initDefaultButton('update', 'edit');
        $this->initDefaultButton('delete', 'trash-alt',
            $this->grid->enablePjaxDelete ? [] : [
                'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                'data-method' => 'post'
            ]);
    }

    /**
     * Initializes the default button rendering callback for single button.
     * @param string $name Button name as it's written in template
     * @param string $iconName The part of Bootstrap glyphicon class that makes it unique
     * @param array $additionalOptions Array of additional options
     * @since 2.0.11
     */
    protected function initDefaultButton($name, $iconName, $additionalOptions = [])
    {
        if (!isset($this->buttons[$name]) && strpos($this->template, '{' . $name . '}') !== false) {
            $this->buttons[$name] = function ($url, $model, $key) use ($name, $iconName, $additionalOptions) {
                switch ($name) {
                    case 'view':
                        $title = Yii::t('yii', 'View');
                        $className = 'info';
                        break;
                    case 'update':
                        $title = Yii::t('yii', 'Update');
                        $className = 'success';
                        break;
                    case 'delete':
                        $title = Yii::t('yii', 'Delete');
                        $className = 'danger delete-grid-item';
                        break;
                    default:
                        $title = ucfirst($name);
                }
                $options = array_merge([
                    'title' => $title,
                    'aria-label' => $title,
                    'data-pjax' => '0',
                    'data-toggle' => 'tooltip',
                    'data-placement' => 'top',
                    'data-container' => 'body',
                    'class' => "btn btn-outline-$className btn-circle btn-sm",
                ], $additionalOptions, $this->buttonOptions);
                $icon = Html::tag('span', '', ['class' => "far fa-$iconName"]);
                return Html::a($icon, $url, $options);
            };
        }
    }
}
