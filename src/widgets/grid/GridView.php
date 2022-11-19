<?php

namespace sadi01\moresettings\widgets\grid;

use kartik\grid\GridView as KartikGridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;
use Yii;
use yii\web\View;

/**
 * The GridView widget is used to display data in a grid.
 *
 */
class GridView extends KartikGridView
{
    /**
     * @var array the HTML attributes for the grid table element.
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $tableOptions = ['class' => 'table table-striped table-bordered no-wrap'];
    /**
     * @var array the HTML attributes for the container tag of the grid view.
     * The "tag" element specifies the tag name of the container element and defaults to "div".
     * @see \yii\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public $options = ['class' => 'grid-view'];

    public $layout = "{toolbar}\n{summary}\n<div class='table-responsive mb-2'>{items}</div>{pager}";

    public $pager = [
        'options' => ['class' => 'pagination'],
        'prevPageLabel' => "<",
        'nextPageLabel' => ">",
        'firstPageLabel' => "<<",
        'lastPageLabel' => ">>",
        'linkContainerOptions' => ['class' => 'page-item'],
        'linkOptions' => ['class' => 'page-link'],
        'disabledListItemSubTagOptions' => ['class' => 'page-link'],
        'maxButtonCount' => 5,
    ];

    public $responsive = false;

    public $responsiveWrap = false;

    public $resizableColumns = false;

    public $toolbar = [];

    public $showCustomToolbar = true;

    public $mergeCustomToolbar = false;

    public $enablePjaxDelete = false;

    public $customToolbar = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        $view = $this->getView();

        if (empty($this->customToolbar)) {
            $this->customToolbar = [
                [
                    'content' =>
                        Html::a('<i class="far fa-plus"></i>', ['create'], [
                            'title' => Yii::t('common', 'Add'),
                            'data' => [
                                'toggle' => 'tooltip',
                                'pjax' => '0',
                            ],
                            'class' => 'btn btn-success btn-outline mb-2'
                        ]) . ' ' .
                        Html::a('<i class="far fa-trash-alt"></i>', ['delete-selected'], [
                            'class' => 'btn btn-danger ml-1 mb-2 grid-delete-selected-btn',
                            'title' => Yii::t('common', 'Remove selected row(s).'),
                            'data' => $this->enablePjaxDelete ?
                                [
                                    'toggle' => 'tooltip',
                                    'pjax' => '0',
                                ] : [
                                    'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    'method' => 'post',
                                    'toggle' => 'tooltip',
                                    'pjax' => '0',
                                ]
                        ]) . ' ',
                ]
            ];
        } elseif ($this->mergeCustomToolbar) {
            $this->customToolbar = ArrayHelper::merge($this->customToolbar, [
                [
                    'content' =>
                        Html::a('<i class="far fa-trash-alt"></i>', ['delete-selected'], [
                            'class' => 'btn btn-danger ml-1 mb-2 grid-delete-selected-btn',
                            'title' => Yii::t('common', 'Remove selected row(s).'),
                            'data' => $this->enablePjaxDelete ?
                                [
                                    'toggle' => 'tooltip',
                                    'pjax' => '0',
                                ] : [
                                    'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    'method' => 'post',
                                    'toggle' => 'tooltip',
                                    'pjax' => '0',
                                ]
                        ]) . ' ',
                ]
            ]);
        }

        $this->toolbar = $this->showCustomToolbar ? ArrayHelper::merge($this->customToolbar, $this->toolbar) : $this->toolbar;

        $this->columns = $this->showCustomToolbar ?
            ArrayHelper::merge(
                [
                    [
                        'class' => 'common\widgets\grid\CheckboxColumn',
                        'checkboxOptions' => [
                            'class' => 'custom-control-input'
                        ]
                    ]
                ], $this->columns
            ) : $this->columns;


        Parent::init();

        if ($this->showCustomToolbar && $this->enablePjaxDelete) {
            $confirmTitle = Yii::t('common', 'Are you sure?');
            $confirmText = Yii::t('common', "You won't be able to revert this!");
            $confirmBtnText = Yii::t('common', "Yes, delete selected items!");
            $cancelBtnText = Yii::t('common', "Cancel");
            $deletedTitle = Yii::t('common', "Items have been deleted!");
            $errorOnDeleteTitle = Yii::t('common', "Error on delete!");
            $deletedText = Yii::t('common', "Selected items have been deleted.");

            $view->registerJs("           
            handleDeleteSelected = function(e){
                e.preventDefault();
                
                selectedIds = jQuery('#" . $this->options['id'] . "').yiiGridView('getSelectedRows');
                if(selectedIds.length > 0){
                    href = jQuery(this).attr('href');
                    if(href.indexOf('?') != -1){
                    //console.log(href.param())
                        href = href.substr(0, href.indexOf('?'));
                    }
                    var pjaxContainer = jQuery('#" . $this->options['id'] . "').closest('[data-pjax-container]');
                    jQuery(this).attr('href', href + '?selectedIds=' + selectedIds)
                    
                }else{
                     swal({
                           title: '" . Yii::t('common', 'Select one or more row!') . "',
                           type: 'info',
                           confirmButtonText: '" . Html::tag('i', null, ['class' => 'fas fa-thumbs-up font-22']) . "',
                          });
                    return false;
                }                
                  swal({
                  title: '$confirmTitle',
                  text: '$confirmText',
                  type: 'warning',
                  showCancelButton: true,
                  confirmButtonColor: '#3085d6',
                  cancelButtonColor: '#d33',
                  cancelButtonText: '$cancelBtnText',
                  confirmButtonText: '$confirmBtnText'
                }).then((result) => {
                  if (result.value) {
                    $.post($(this).attr('href'), {}, function (data) {
                        if (data.status !== false) {
                            
                        }
                        // errors handling
                        else {
                
                        }
                    }).fail(function (xhr, status, error) {
                        swal({
                            title: xhr.responseText,
                            type: 'error',
                            confirmButtonText: '" . Html::tag('i', null, ['class' => 'fas fa-thumbs-up font-22']) . "',
                        });
                    }).then(function (result) {
                        if(result.status === true){
                            swal({
                              title: '$deletedTitle',
                              text: '$deletedText',
                              type: 'success',
                              confirmButtonText: '" . Html::tag('i', null, ['class' => 'fas fa-thumbs-up font-22']) . "',
                            });
                            $.pjax.reload('#'+$(pjaxContainer).attr('id'), [])
                        }
                        else{
                            swal({
                              title: '$errorOnDeleteTitle',
                              text: result.message,
                              type: 'error',
                              confirmButtonText: '" . Html::tag('i', null, ['class' => 'fas fa-thumbs-up font-22']) . "',
                            });                      
                        }
                    });
                  }
                });
            }
        
            jQuery('.grid-delete-selected-btn').on('click', handleDeleteSelected);
            
        $(document).on('pjax:end', function() {
            jQuery('.grid-delete-selected-btn').on('click', handleDeleteSelected);
        });
        ", View::POS_READY);
        }

        if ($this->showCustomToolbar && !$this->enablePjaxDelete) {
            $view->registerJs("           
            handleDeleteSelected = function(e){
                e.preventDefault();
                
                selectedIds = jQuery('#" . $this->options['id'] . "').yiiGridView('getSelectedRows');
                if(selectedIds.length > 0){
                    href = jQuery(this).attr('href');
                    if(href.indexOf('?') != -1){
                    //console.log(href.param())
                        href = href.substr(0, href.indexOf('?'));
                    }
                    var pjaxContainer = jQuery('#" . $this->options['id'] . "').closest('[data-pjax-container]');
                    jQuery(this).attr('href', href + '?selectedIds=' + selectedIds)
                    
                }else{
                     swal({
                           title: '" . Yii::t('common', 'Select one or more row!') . "',
                           type: 'info',
                           confirmButtonText: '" . Html::tag('i', null, ['class' => 'fas fa-thumbs-up font-22']) . "',
                          });
                    return false;
                }
            }
        
            jQuery('.grid-delete-selected-btn').on('click', handleDeleteSelected);
            
        $(document).on('pjax:end', function() {
            jQuery('.grid-delete-selected-btn').on('click', handleDeleteSelected);
        });
        ", View::POS_READY);
        }

        if ($this->enablePjaxDelete) {

            $confirmTitle = Yii::t('common', 'Are you sure?');
            $confirmText = Yii::t('common', "You won't be able to revert this!");
            $confirmBtnText = Yii::t('common', "Yes, delete it!");
            $cancelBtnText = Yii::t('common', "Cancel");
            $deletedTitle = Yii::t('common', "Deleted!");
            $errorOnDeleteTitle = Yii::t('common', "Error on delete!");
            $deletedText = Yii::t('common', "Item has been deleted.");

            $view->registerJs("           
            handleDeleteGridItem = function(e) {
            e.preventDefault();
              var pjaxContainer = jQuery('#" . $this->options['id'] . "').closest('[data-pjax-container]');            
              swal({
              title: '$confirmTitle',
              text: '$confirmText',
              type: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#d33',
              cancelButtonText: '$cancelBtnText',
              confirmButtonText: '$confirmBtnText'
            }).then((result) => {
              if (result.value) {
                $.post($(this).attr('href'), {}, function (data) {
                    if (data.status !== false) {
                        
                    }
                    // errors handling
                    else {
                        return false;
                    }
                }).fail(function (xhr, status, error) {
                    swal({
                        title: xhr.responseText,
                        type: 'error',
                        confirmButtonText: '" . Html::tag('i', null, ['class' => 'fas fa-thumbs-up font-22']) . "',
                    });
                }).then(function (result) {
                    if(result.status === true){
                        swal({
                          title: '$deletedTitle',
                          text: '$deletedText',
                          type: 'success',
                          confirmButtonText: '" . Html::tag('i', null, ['class' => 'fas fa-thumbs-up font-22']) . "',
                        });
                        $.pjax.reload('#'+$(pjaxContainer).attr('id'), [])
                    }
                    else{
                        swal({
                          title: '$errorOnDeleteTitle',
                          text: result.message,
                          type: 'error',
                          confirmButtonText: '" . Html::tag('i', null, ['class' => 'fas fa-thumbs-up font-22']) . "',
                        });                      
                    }
                });
              }
            });
            };
        
            jQuery('.delete-grid-item').on('click', handleDeleteGridItem);
            
        $(document).on('pjax:end', function() {
            jQuery('.delete-grid-item').on('click', handleDeleteGridItem);
        });
        ", View::POS_READY);
        }
    }
}