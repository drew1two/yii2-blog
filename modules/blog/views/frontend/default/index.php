<?php

use yii\web\View;
use yii\widgets\ListView;
use modules\blog\models\Post;
use modules\blog\widgets\menu\CategoryMenu;
use modules\blog\widgets\tag\TagCloud;
use modules\comment\widgets\other\LastComment;
use modules\blog\widgets\other\LastPost;
use modules\blog\Module;
use modules\blog\assets\BlogAsset;
use modules\search\widgets\SearchSidebar;

/** @var $this View */
/** @var $dataProvider Post */

BlogAsset::register($this);

$this->title = Module::t('module', 'Blog');
if ($tag = Yii::$app->request->get('tag')) {
    $this->title = Module::t('module', 'Entries tagged &laquo;{:tag}&raquo;', [':tag' => $tag]);
    $this->params['breadcrumbs'][] = ['label' => Module::t('module', 'Blog'), 'url' => ['index']];
}
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="blog-frontend-default-index">
    <div class="row">
        <div class="col-md-3">
            <?= SearchSidebar::widget(['status' => true]) ?>
            <?= CategoryMenu::widget(['status' => true]) ?>
            <noindex>
                <?= LastPost::widget(['status' => true, 'limit' => 5]) ?>
            </noindex>
            <noindex>
                <?= TagCloud::widget(['status' => true, 'limit' => 50]) ?>
            </noindex>
            <noindex>
                <?= LastComment::widget(['status' => true, 'limit' => 5]) ?>
            </noindex>
        </div>
        <div class="col-md-9">
            <?= ListView::widget([
                'dataProvider' => $dataProvider,
                'layout' => "{items}\n{pager}",
                'itemView' => '_list'
            ]) ?>
        </div>
    </div>

</div>
