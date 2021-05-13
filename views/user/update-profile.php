<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\User */

$this->title = 'Обновить профиль';
?>

<div class="update-profile">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form_update_profile', [
        'model' => $model,
    ]) ?>

</div>