<?php

/* @var $model app\models\BalanceForm */
/* @var $currentBalance app\controllers\SiteController */

$this->title = 'Запрос счета на пополнение баланса';

use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
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
        $("#balance-replenishment-form").on("afterValidate", function (e, m, attr) {
            e.preventDefault();
            if(attr.length > 0) {
                // Скрытие индикатора прогресса
                $("#overlay").hide();
                spinner.stop(target);
            }
        });
    });
</script>

<div class="balance-replenishment">

    <h1><?= Html::encode($this->title) ?></h1><br />

    <span><b>Текущий баланс: </b></span>
    <span id="current-balance" class="badge" style="margin-bottom: 2px;"><?= $currentBalance ?></span><br /><br />

    <div class="body-content">
        <?php $form = ActiveForm::begin([
            'id' => 'balance-replenishment-form',
            'enableClientValidation' => true,
        ]); ?>

        <?= $form->errorSummary($model); ?>

        <?= $form->field($model, 'balance')->textInput(); ?>

        <?= $form->field($model, 'email')->textInput(); ?>

        <p>
            Пожалуйста, укажите адрес электронной почты, куда будет отправлен счет на пополнение баланса.
        </p>

        <div class="form-group">
            <?= Html::submitButton('<span class="glyphicon glyphicon-ok"></span> Пополнить',
                ['class' => 'btn btn-success']); ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>