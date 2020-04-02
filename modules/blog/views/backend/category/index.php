<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\SerialColumn;
use yii\grid\ActionColumn;
use modules\blog\models\Category;
use modules\blog\Module;

/* @var $this yii\web\View */
/* @var $searchModel modules\blog\models\search\CategorySearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

echo $this->render('_base', ['link' => false]);
?>
<div class="blog-backend-category-index">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Html::encode(Module::t('module', 'Categories')) ?></h3>
            <div class="box-tools pull-right"></div>
        </div>
        <div class="box-body">
            <div class="pull-left"></div>
            <div class="pull-right">
                <p>
                    <?= Html::a('<span class="fa fa-plus"></span> ', ['create'], [
                        'class' => 'btn btn-block btn-success',
                        'title' => Module::t('module', 'Create'),
                        'data' => [
                            'toggle' => 'tooltip',
                            'placement' => 'left',
                            'pjax' => 0,
                        ],
                    ]) ?>
                </p>
            </div>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'columns' => [
                    ['class' => SerialColumn::class],

                    //'id',
                    'tree',
                    'lft',
                    'rgt',
                    'depth',
                    //'title',
                    [
                        'attribute' => 'title',
                        'value' => static function (Category $model) {
                            return str_repeat('-', $model->depth) . ' ' . $model->title;
                        }
                    ],
                    //'slug',
                    //'description:ntext',
                    'created_at:datetime',
                    //'updated_at:datetime',
                    [
                        'attribute' => 'status',
                        'format' => 'raw',
                        'value' => static function (Category $model) {
                            return $model->getStatusLabelName();
                        }
                    ],
                    'position',
                    [
                        'class' => ActionColumn::class,
                        'contentOptions' => [
                            'class' => 'action-column',
                            'style' => 'width: 90px'
                        ],
                        'template' => '{view} {move} {update} {delete}',
                        'buttons' => [
                            'move' => static function ($url) {
                                return Html::a('<span class="glyphicon glyphicon-random"></span>', $url, [
                                    'title' => Module::t('module', 'Move'),
                                    'data' => [
                                        //'toggle' => 'tooltip',
                                        'pjax' => 0,
                                    ]
                                ]);
                            },
                        ]
                    ],
                ],
            ]) ?>
        </div>
        <div class="box-footer"></div>
    </div>
</div>
