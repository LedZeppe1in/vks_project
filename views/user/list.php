<?php

use app\models\User;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel app\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Пользователи';
?>

<div class="user-list">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('<span class="glyphicon glyphicon-plus"></span> Создать', ['create'],
            ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            'id',
            'username',
            [
                'attribute'=>'role',
                'value' => function($data) {
                    return ($data->role !== null) ? $data->getRoleName(true) : null;
                },
                'format' => 'raw',
                'filter' => User::getRoles(false)
            ],
            [
                'attribute'=>'status',
                'value' => function($data) {
                    return ($data->status !== null) ? $data->getStatusName(true) : null;
                },
                'format' => 'raw',
                'filter' => User::getStatuses(false)
            ],
            [
                'attribute'=>'full_name',
                'value' => function($data) {
                    return ($data->full_name != '') ? $data->full_name : null;
                },
            ],
            [
                'attribute'=>'email',
                'value' => function($data) {
                    return ($data->email != '') ? $data->email : null;
                },
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>