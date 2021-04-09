<?php

/* @var $model app\models\NotificationResultForm */

$this->title = 'Проверка статусов сообщений за определенный период';

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use kartik\datetime\DateTimePicker;
?>

<!-- JS-скрипт -->
<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка появления индикатора прогресса
        let target = document.getElementById("center");
        let spinner = new Spinner(spinner_options).spin();
        $("button.btn-success").click(function() {
            $("#overlay").show();
            spinner.spin(target);
        });
        // Обработка события после валидации формы
        $("#notification-result-form").on("afterValidate", function (e, m, attr) {
            e.preventDefault();
            if(attr.length > 0) {
                // Скрытие индикатора прогресса
                $("#overlay").hide();
                spinner.stop(target);
            }
        });
    });
</script>

<div class="check-message-status">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="body-content">
        <?php $form = ActiveForm::begin([
            'id' => 'notification-result-form',
            'enableClientValidation' => true,
        ]); ?>

            <?= $form->errorSummary($model); ?>

            <?= $form->field($model, 'fromDateTime')->widget(DateTimePicker::classname(), [
                'language' => 'ru',
                'pluginOptions' => [
                    'format' => 'dd.mm.yyyy hh:ii',
                    'autoclose' => true
                ]
            ]); ?>

            <?= $form->field($model, 'toDateTime')->widget(DateTimePicker::classname(), [
                'language' => 'ru',
                'pluginOptions' => [
                    'format' => 'dd.mm.yyyy hh:ii',
                    'autoclose' => true
                ]
            ]); ?>

            <div class="form-group">
                <?= Html::submitButton('<span class="glyphicon glyphicon-ok"></span> Проверить',
                    ['class' => 'btn btn-success']); ?>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>