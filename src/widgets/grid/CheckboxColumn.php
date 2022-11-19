<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace common\widgets\grid;

use Closure;
use yii\helpers\Html;
use yii\helpers\Json;

class CheckboxColumn extends \yii\grid\CheckboxColumn
{
    /**
     * Renders the header cell content.
     * The default implementation simply renders [[header]].
     * This method may be overridden to customize the rendering of the header cell.
     * @return string the rendering result
     */
    protected function renderHeaderCellContent()
    {
        if ($this->header !== null || !$this->multiple) {
            return parent::renderHeaderCellContent();
        }

        return Html::tag('div', Html::checkbox($this->getHeaderCheckBoxName(), false, ['class' => 'select-on-check-all custom-control-input', 'id' => "{$this->grid->id}-checkbox-select-all"]) . Html::label('', "{$this->grid->id}-checkbox-select-all", ['class' => 'custom-control-label']), ['class' => 'custom-control custom-checkbox']);
    }

    /**
     * {@inheritdoc}
     */
    protected function renderDataCellContent($model, $key, $index)
    {
        if ($this->content !== null) {
            return parent::renderDataCellContent($model, $key, $index);
        }

        if ($this->checkboxOptions instanceof Closure) {
            $options = call_user_func($this->checkboxOptions, $model, $key, $index, $this);
        } else {
            $options = $this->checkboxOptions;
        }

        if (!isset($options['value'])) {
            $options['value'] = is_array($key) ? Json::encode($key) : $key;
            $options['id'] = 'checkbox-' . (is_array($key) ? Json::encode($key) : $key);
        }

        if ($this->cssClass !== null) {
            Html::addCssClass($options, $this->cssClass);
        }

        return Html::tag('div', Html::checkbox($this->name, !empty($options['checked']), $options) . Html::label('', 'checkbox-' . $key, ['class' => 'custom-control-label']), ['class' => 'custom-control custom-checkbox']);
    }
}
