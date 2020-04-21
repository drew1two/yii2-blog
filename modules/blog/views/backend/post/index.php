<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use modules\comment\models\Comment;
use modules\blog\grid\GridView;
use modules\blog\grid\DataDetailColumn;
use yii\grid\SerialColumn;
use yii\grid\ActionColumn;
use yii\widgets\LinkPager;
use modules\blog\assets\BlogAsset;
use modules\blog\models\Post;
use modules\blog\Module;
use modules\comment\Module as CommentModule;

/* @var $this yii\web\View */
/* @var $searchModel modules\blog\models\search\PostSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $comment Comment */

echo $this->render('_base', ['link' => false]);

BlogAsset::register($this);


$style = '
tr.detail:hover,
tr.detail:focus {
    background-color: inherit !important;
}
.comment-list .item {
    padding: 5px;
    margin-bottom: 5px;
    border-radius: 5px 5px 0 0;
}
.comment-list .item .item-comment {
    border: 1px solid #c5c5c5;
    padding: 5px;
    margin: 5px 0;
    border-radius: 5px 0 5px 0;
}
.comment-list .item-avatar img {
    width: 50px;
    margin: 0 5px 5px 0;
}
.comment-list .item-blank {
    padding: 10px;
    color: #c5c5c5;
}
';
$this->registerCss($style);

$script = "
let loc = window.location.hash.replace('#',''),
    tr;
    
if (loc !== '') {    
    tr = $('#' + loc).parent().parent().parent('tr');    
    tr.show();
}

$('#post-table .row-detail').on('click', function(){
    let key = $(this).parent('tr').data('key')
        detail = $('.detail')
        targetDetail = $('#detail-' + key);
    
    if(targetDetail.is(':visible')) {
        targetDetail.hide();
    } else {
        detail.hide(); 
        targetDetail.show();
    }
});

$('.btn-reply').on('click', function(e){       
    e.preventDefault();
    
    let target = $(this),
        id = target.data('id'),
        entityId = target.data('entityid'),
        form = $('#reply-form'),
        replyContainer = '#form-container-' + id;
        
        console.log(entityId);
        form.appendTo(replyContainer);
        form.show();
        $('#comment-entity_id').val(entityId);
        $('#comment-parentid').val(id);
});
";
$this->registerJs($script);
?>
<div class="blog-backend-post-index">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Html::encode(Module::t('module', 'Posts')) ?></h3>
            <div class="box-tools pull-right"></div>
        </div>
        <div class="box-body">
            <div class="pull-left">
                <?= common\widgets\PageSize::widget([
                    'label' => '',
                    'defaultPageSize' => Post::getDefaultPageSize(),
                    'sizes' => Post::getSizes(),
                    'options' => [
                        'class' => 'form-control'
                    ]
                ]) ?>
            </div>
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
                'id' => 'post-table',
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'filterSelector' => 'select[name="per-page"]',
                'layout' => '{items}',
                'tableOptions' => [
                    'class' => 'table table-bordered table-hover',
                ],
                'detailRowOptions' => [
                    'style' => 'display: none;',
                ],
                'columns' => [
                    [
                        'class' => SerialColumn::class,
                        'contentOptions' => [
                            'class' => 'data-column',
                            'style' => 'width: 50px'
                        ]
                    ],
                    [
                        'class' => DataDetailColumn::class,
                        'attribute' => 'title',
                        'format' => 'raw',
                        'value' => static function (Post $model) {
                            $count = $model->getCommentsWaitCount();
                            $comments = '';
                            if ($count > 0) {
                                $comments = Html::tag('span', $count, [
                                    'class' => 'pull-right label label-warning',
                                    'title' => CommentModule::t('module', 'Comments awaiting moderation')
                                ]);
                            }
                            return $model->title . $comments;
                        },
                        'contentOptions' => [
                            'class' => 'row-detail',
                            'style' => 'cursor:pointer;'
                        ],
                        'detail' => function (Post $model) {
                            return $this->render('comments/index', [
                                'model' => $model
                            ]);
                        },
                    ],
                    'slug',
                    [
                        'class' => DataDetailColumn::class,
                        'attribute' => 'tagNames',
                        'value' => static function (Post $model) {
                            return $model->getStringTagsToPost(true, false, '-');
                        },
                    ],
                    [
                        'attribute' => 'authorName',
                        'value' => static function (Post $model) {
                            return $model->getAuthorName();
                        }
                    ],
                    [
                        'attribute' => 'created_at',
                        'filter' => kartik\date\DatePicker::widget([
                            'model' => $searchModel,
                            'attribute' => 'date_from',
                            'attribute2' => 'date_to',
                            'type' => kartik\date\DatePicker::TYPE_RANGE,
                            'separator' => '-',
                            'pluginOptions' => [
                                'todayHighlight' => true,
                                'weekStart' => 1,
                                'autoclose' => true,
                                'format' => 'yyyy-mm-dd'
                            ],
                        ]),
                        'format' => ['date', 'YYYY-MM-dd HH:mm:ss'],
                        'contentOptions' => [
                            'class' => 'data-column',
                            'style' => 'width: 250px'
                        ]
                    ],
                    [
                        'attribute' => 'status',
                        'filter' => Html::activeDropDownList($searchModel, 'status', $searchModel->statusesArray, [
                            'class' => 'form-control',
                            'prompt' => Module::t('module', '- all -'),
                            'data' => [
                                'pjax' => true
                            ]
                        ]),
                        'format' => 'raw',
                        'value' => static function (Post $model) {
                            $title = $model->isPublish ? Module::t('module', 'Click to change status to draft') : Module::t('module', 'Click to change status to publish');
                            return Html::a($model->getStatusLabelName(), ['change-status', 'id' => $model->id], ['title' => $title]);
                        },
                        'contentOptions' => [
                            'class' => 'data-column',
                            'style' => 'width: 140px'
                        ],
                    ],
                    [
                        'attribute' => 'category_id',
                        'filter' => Html::activeDropDownList($searchModel, 'category_id', Post::getCategoriesTree(), [
                            'class' => 'form-control',
                            'prompt' => Module::t('module', '- all -'),
                            'data' => [
                                'pjax' => true
                            ]
                        ]),
                        'format' => 'raw',
                        'value' => static function (Post $model) {
                            return $model->getCategoryTitlePath();
                        }
                    ],
                    [
                        'attribute' => 'sort',
                        'contentOptions' => [
                            'class' => 'data-column',
                            'style' => 'width: 80px'
                        ]
                    ],
                    [
                        'class' => ActionColumn::class,
                        'contentOptions' => [
                            'class' => 'action-column',
                            'style' => 'width: 90px'
                        ],
                    ]
                ]
            ]) ?>
        </div>
        <div class="box-footer">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'registerLinkTags' => true,
                'options' => [
                    'class' => 'pagination pagination-sm no-margin pull-right',
                ]
            ]) ?>
        </div>
    </div>
</div>

<div id="form-container" style="display: none;">
    <?php $form = ActiveForm::begin([
        'id' => 'reply-form',
        'enableClientValidation' => true,
        'action' => Url::to(['/comment/default/create'])
    ]); ?>

    <?= $form->field($comment, 'author')->textInput([
        'class' => 'form-control',
        'placeholder' => true
    ]) ?>

    <?= $form->field($comment, 'email')->textInput([
        'class' => 'form-control',
        'placeholder' => true
    ])->hint(CommentModule::t('module', 'No one will see')) ?>

    <?= $form->field($comment, 'comment')->textarea([
        'rows' => 6,
        'class' => 'form-control',
        'placeholder' => true
    ]) ?>

    <?= $form->field($comment, 'entity')->hiddenInput()->label(false) ?>
    <?= $form->field($comment, 'entity_id')->hiddenInput()->label(false) ?>
    <?= $form->field($comment, 'rootId')->hiddenInput()->label(false) ?>
    <?= $form->field($comment, 'parentId')->hiddenInput()->label(false) ?>

    <div class="form-group">
        <?= Html::submitButton('<span class="glyphicon glyphicon-send"></span> ' . CommentModule::t('module', 'Submit comment'), [
            'class' => 'btn btn-primary',
            'name' => 'comment-button',
            'value' => $comment->scenario
        ]) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
