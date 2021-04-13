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
        // Обработка выбора периода
        $("#notificationresultform-period").change(function() {
            let fromDatetimeField = document.getElementById("notificationresultform-fromdatetime");
            let toDatetimeField = document.getElementById("notificationresultform-todatetime");
            let fromDatetime = new Date().toLocaleDateString();
            if (this.value !== "")
                toDatetimeField.value = fromDatetime + "  00:00";
            if (this.value === "0")
                fromDatetimeField.value = fromDatetime + "  00:00";
            if (this.value === "1") {
                let toDatetime = new Date(new Date().setDate(new Date().getDate() - 1)).toLocaleDateString();
                fromDatetimeField.value = toDatetime + "  00:00";
            }
            if (this.value === "2") {
                let toDatetime = new Date(new Date().setDate(new Date().getDate() - 3)).toLocaleDateString();
                fromDatetimeField.value = toDatetime + "  00:00";
            }
            if (this.value === "3") {
                let toDatetime = new Date(new Date().setDate(new Date().getDate() - 7)).toLocaleDateString();
                fromDatetimeField.value = toDatetime + "  00:00";
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

            <?= $form->field($model, 'period')->dropDownList(
                [
                    '0' => 'сегодня',
                    '1' => 'вчера',
                    '2' => 'за три дня',
                    '3' => 'за неделю',
                ],
                ['prompt' => 'выберите период ...']
            ); ?>

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