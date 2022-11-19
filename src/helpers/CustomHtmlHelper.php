<?php

namespace sadi01\moresettings\helpers;

use Yii;
use yii\helpers\Html;

class CustomHtmlHelper
{
    /**
     * Return loading tag
     * @param string $loadingText [optional]
     * @return string
     */
    public static function loadingTag($loadingText = NULL)
    {
        $loadingText = $loadingText ?: Yii::t('common', 'Loading ...');

        $loadingTag = Html::tag('div',
            Html::tag('div',
                Html::tag('span', $loadingText, ['class' => 'sr-only']),
                ['class' => 'spinner-grow text-success p-4', 'role' => 'status']),
            ['class' => 'd-flex justify-content-center']);

        return $loadingTag;
    }
}