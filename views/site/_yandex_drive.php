<?php

/* @var $yandexDriveModel app\models\DriveForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
?>

<div class="row">
    <div class="col-md-12">
        <h2>Yandex-диск:</h2>

        <?php $form = ActiveForm::begin([
            'id' => 'yandex-drive-form',
        ]); ?>

        <?= $form->field($yandexDriveModel, 'link')->textInput() ?>

        <?= $form->field($yandexDriveModel, 'username')->textInput() ?>

        <?= $form->field($yandexDriveModel, 'password')->passwordInput() ?>

        <div class="form-group">
            <?= Html::submitButton('Синхронизировать с Google-диском',
                ['class' => 'btn btn-success', 'name' => 'synchronization-button']) ?>
            <?= Html::a('Проверить', ['#'], ['class' => 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>