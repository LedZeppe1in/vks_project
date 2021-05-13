<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = 'Пользователь: ' . $model->username;
$this->params['breadcrumbs'][] = ['label' => 'Пользователи', 'url' => ['list']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="user-view">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<span class="glyphicon glyphicon-pencil"></span> Обновить', ['update', 'id' => $model->id],
            ['class' => 'btn btn-primary']) ?>
        <?= Html::a('<span class="glyphicon glyphicon-trash"></span> Удалить', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Вы уверены, что хотите удалить этого пользователя?',
                'method' => 'post',
            ],
        ]) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            'username',
            [
                'attribute' => 'auth_key',
                'value' => ($model->auth_key != '') ? $model->auth_key : null
            ],
            [
                'attribute' => 'role',
                'value' => ($model->role !== null) ? $model->getRoleName(true) : null,
                'format' => 'raw'
            ],
            [
                'attribute' => 'status',
                'value' => ($model->status !== null) ? $model->getStatusName(true) : null,
                'format' => 'raw'
            ],
            [
                'attribute' => 'full_name',
                'value' => ($model->full_name != '') ? $model->full_name : null
            ],
            [
                'attribute' => 'email',
                'value' => ($model->email != '') ? $model->email : null
            ],
        ],
    ]) ?>

</div>