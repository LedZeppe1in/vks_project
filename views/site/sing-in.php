<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap\ActiveForm */
/* @var $model app\models\LoginForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;

$this->title = 'Вход';
?>

<div class="sing-in">
    <h1><?= Html::encode($this->title) ?></h1>

    <p>Пожалуйста, заполните следующие поля для входа:</p>

    <div class="row">
        <div class="col-lg-5">

            <?php $form = ActiveForm::begin(['id' => 'sign-in-form']); ?>

                <?= $form->field($model, 'username') ?>

                <?= $form->field($model, 'password')->passwordInput() ?>

                <?= $form->field($model, 'rememberMe')->checkbox() ?>

                <div class="form-group">
                    <?= Html::submitButton('<span class="glyphicon glyphicon-log-in"></span> Войти',
                        ['class' => 'btn btn-primary', 'name' => 'sign-in-button']) ?>
                </div>

                <div class="col-lg-offset-1 col-lg-11" style="color:#999;margin:1em 0">
                    <?= Html::a('Политика конфиденциальности', ['/site/privacy-policy']) ?>
                </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>