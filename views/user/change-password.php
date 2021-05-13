<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = 'Поменять пароль';
?>

<div class="change-password">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form_change_password', [
        'model' => $model,
    ]) ?>

</div>