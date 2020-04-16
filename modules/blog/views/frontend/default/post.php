<?php

use yii\web\View;
use yii\helpers\Html;
use modules\blog\models\Post;
use modules\blog\widgets\menu\CategoryMenu;
use modules\blog\widgets\tag\TagCloud;
use modules\comment\widgets\CommentList;
use modules\comment\widgets\CommentForm;
use modules\blog\Module;

/** @var $this View */
/** @var $model Post */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Module::t('module', 'Blog'), 'url' => ['index']];
if (($category = $model->postCategory) && $category !== null) {
    $this->params['breadcrumbs'] = $category->getBreadcrumbs($this->params['breadcrumbs'], true);
}
$this->params['breadcrumbs'][] = $model->title;
?>
<div class="blog-frontend-default-post">
    <div class="row">
        <div class="col-md-3">
            <?= CategoryMenu::widget() ?>
            <noindex>
                <?= TagCloud::widget(['limit' => 50]) ?>
            </noindex>
        </div>
        <div class="col-md-9">

            <div class="content-container">
                <div class="header">
                    <h2><?= Html::encode($model->title) ?></h2>
                    <div class="info">
                        <span class="glyphicon glyphicon-calendar"></span> <?= Yii::$app->formatter->asDatetime($model->created_at) ?>
                        <span class="glyphicon glyphicon-user"></span> <?= $model->getAuthorName() ?>
                    </div>
                </div>
                <div class="body">
                    <div class="content">
                        <?= $model->anons ?>
                        <?= $model->content ?>
                    </div>
                </div>
                <div class="footer">
                    <div class="info">
                        <?php if ($category !== null) { ?>
                            <noindex>
                                <span class="glyphicon glyphicon-folder-open"></span> <?= Html::a($category->title, [$category->url], ['rel' => 'nofollow']) ?>
                            </noindex>
                        <?php } ?>
                        <?php if ($tags = $model->getStringTagsToPost(true, true)) { ?>
                            <noindex>
                                <span class="glyphicon glyphicon-tags"></span> <?= Module::t('module', 'Tags') ?>
                                : <?= $tags ?>
                            </noindex>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="comment-container">
                <?= CommentList::widget([
                    'status' => true,
                    'model' => $model
                ]) ?>
                <?= CommentForm::widget([
                    'status' => true,
                    'model' => $model
                ]) ?>
            </div>

        </div>
    </div>
</div>