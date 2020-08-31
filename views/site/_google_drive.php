<?php

/* @var $googleDriveModel app\models\DriveForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>

<div class="row">
    <div class="col-md-12">
        <h2>Google-диск:</h2>

        <?php $form = ActiveForm::begin([
            'id' => 'google-drive-form',
        ]); ?>

        <?= $form->field($googleDriveModel, 'link')->textInput() ?>

        <?= $form->field($googleDriveModel, 'username')->textInput() ?>

        <?= $form->field($googleDriveModel, 'password')->passwordInput() ?>

        <div class="form-group">
            <?= Html::submitButton('Синхронизировать с Яндекс-диском',
                ['class' => 'btn btn-success', 'name' => 'synchronization-button']) ?>
            <?= Html::a('Проверить', ['#'], ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>