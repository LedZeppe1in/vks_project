<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = 'Профиль';
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="profile">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<span class="glyphicon glyphicon-pencil"></span> Обновить учетные данные',
            ['update-profile', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
        <?= Html::a('<span class="glyphicon glyphicon-repeat"></span> Поменять пароль',
            ['change-password', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= DetailView::widget([
        'model' => $model,
        'attributes' => [
            'username',
            [
                'attribute'=>'full_name',
                'value' => $model->full_name != '' ? $model->full_name : null,
            ],
            [
                'attribute'=>'email',
                'value' => $model->email != '' ? $model->email : null,
            ],
            [
                'attribute'=>'role',
                'value' => $model->getRoleName(true),
                'format' => 'raw'
            ],
            [
                'attribute'=>'status',
                'value' => $model->getStatusName(true),
                'format' => 'raw'
            ],
            [
                'attribute' => 'created_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
            [
                'attribute' => 'updated_at',
                'format' => ['date', 'dd.MM.Y HH:mm:ss']
            ],
        ],
    ]) ?>

</div>