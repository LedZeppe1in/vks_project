<?php

/* @var $this yii\web\View */
/* @var $model app\models\User */
/* @var $form yii\widgets\ActiveForm */

use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>

<div class="change-password-form">

    <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'password')->textInput(['maxlength' => true]) ?>

        <div class="form-group">
            <?= Html::submitButton('<span class="glyphicon glyphicon-ok"></span> Поменять',
                ['class' => 'btn btn-success']) ?>
        </div>

    <?php ActiveForm::end(); ?>

</div>