<?php

use yii\helpers\Html;
use modules\comment\Module;

/* @var $this yii\web\View */
/* @var $model modules\comment\models\Comment */

$this->title = Module::t('module', 'Comments');
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->getComment(), 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Module::t('module', 'Update');
?>
<div class="comment-backend-default-update">
    <div class="box">
        <div class="box-header with-border">
            <h3 class="box-title"><?= Html::encode($model->getComment()) ?></h3>
            <div class="box-tools pull-right"></div>
        </div>
        <div class="box-body">
            <div class="pull-left"></div>
            <div class="pull-right"></div>
            <?= $this->render('form/_update', [
                'model' => $model,
            ]) ?>
        </div>
        <div class="box-footer">
            <?= Html::submitButton('<span class="glyphicon glyphicon-floppy-disk"></span> ' . Module::t('module', 'Save'), [
                'class' => 'btn btn-success',
                'form' => 'form-update'
            ]) ?>
        </div>
    </div>
</div>
