<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = 'Поменять пароль';
$this->params['breadcrumbs'][] = ['label' => 'Профиль', 'url' => ['profile', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Поменять пароль для: ' . $model->username;
?>

<div class="change-password">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form_change_password', [
        'model' => $model,
    ]) ?>

</div>