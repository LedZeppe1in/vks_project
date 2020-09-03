<?php

/* @var $cloudDriveModel app\models\CloudDriveForm */

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\models\CloudDriveForm;
?>

<div class="row">
    <div class="col-md-12">
        <h2>Данные с облачных дисков:</h2>

        <?php $form = ActiveForm::begin([
            'id' => 'cloud-drive-form',
        ]); ?>

        <?= $form->field($cloudDriveModel, 'googleFileLink')
            ->textInput(['value' => CloudDriveForm::GOOGLE_FILE_LINK]) ?>
        <?= $form->field($cloudDriveModel, 'yandexFileLink')
            ->textInput(['value' => CloudDriveForm::YANDEX_FILE_LINK]) ?>

        <div class="form-group">
            <?= Html::a('Проверить', ['#'], ['class' => 'btn btn-primary']) ?>
            <?= Html::submitButton('Синхронизировать',
                ['class' => 'btn btn-success', 'name' => 'synchronization-button']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>