<?php

/* @var $notificationModel app\models\NotificationForm */
/* @var $employees app\controllers\SiteController */

use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\bootstrap\Button;
use yii\bootstrap\ActiveForm;
use app\components\GoogleSpreadsheet;
?>

<!-- JS-скрипт -->
<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Сообщение об успешном сохранении файла с текстом шаблона сообщения
        let saveFileSuccessMessage = document.getElementById("save-file-success-message");

        // Обработка нажатия кнопки сохранения шаблона сообщения
        $("#save-message-template-button").click(function(e) {
            // Отмена поведения кнопки по умолчанию (submit)
            e.preventDefault();
            // Форма с полем шаблона текста сообщения
            let form = $("#notification-form");
            // Ajax-запрос
            $.ajax({
                url: "<?= Yii::$app->request->baseUrl . '/save-message-template' ?>",
                type: "post",
                data: form.serialize(),
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data["success"]) {
                        // Скрытие списка ошибок ввода
                        $("#notification-form .error-summary").hide();
                        // Активация слоя с сообщением об успешном сохранении файла с текстом шаблона сообщения
                        saveFileSuccessMessage.style.display = "block";
                    } else {
                        // Деактивация всех слоев с сообщениями
                        saveFileSuccessMessage.style.display = "none";
                        // Отображение ошибок ввода
                        viewErrors("#notification-form", data);
                    }
                },
                error: function() {
                    alert("Непредвиденная ошибка!");
                }
            });
        });
    });
</script>

<div class="row">
    <div class="col-md-12">

        <h2>Информирование</h2>

        <div id="save-file-success-message" class="alert alert-success alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Сообщение сохранено!</strong> Вы успешно сохранили текст с шаблоном сообщения.
        </div>

        <h3>Оповещение сотрудников:</h3>

        <?php $form = ActiveForm::begin([
            'id' => 'notification-form',
            'enableClientValidation' => true,
        ]); ?>

            <?= $form->errorSummary($notificationModel); ?>

            <?= $form->field($notificationModel, 'messageTemplate')->textarea(['rows' => 8]) ?>

            <div class="form-group">
                <?= Html::submitButton('<span class="glyphicon glyphicon-bell"></span> Оповестить',
                    ['class' => 'btn btn-success', 'name' => 'notification-button']); ?>
                <?= Button::widget([
                    'label' => '<span class="glyphicon glyphicon-save"></span> Сохранить шаблон сообщения',
                    'encodeLabel' => false,
                    'options' => [
                        'id' => 'save-message-template-button',
                        'class' => 'btn btn-primary'
                    ]
                ]); ?>
            </div>

        <?php ActiveForm::end(); ?>

        <h3>Список сотрудников для оповещения:</h3>

        <?php Pjax::begin(['id' => 'pjaxGrid']); ?>
            <?= GridView::widget([
                'dataProvider' => $employees,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],
                    [
                        'label' => 'ФИО',
                        'attribute' => '0',
                    ],
                    [
                        'label' => 'Табельный номер',
                        'attribute' => '1',
                    ],
                    [
                        'label' => 'Телефон',
                        'attribute' => '2',
                    ],
                    [
                        'label' => GoogleSpreadsheet::DATE_HEADING,
                        'attribute' => '3',
                    ],
                    [
                        'label' => GoogleSpreadsheet::START_TIME_HEADING,
                        'attribute' => '4',
                    ],
                    [
                        'label' => GoogleSpreadsheet::END_TIME_HEADING,
                        'attribute' => '5',
                    ],
                    [
                        'label' => GoogleSpreadsheet::ADDRESS_HEADING,
                        'attribute' => '6',
                    ],
                    [
                        'label' => GoogleSpreadsheet::WORK_TYPE_HEADING,
                        'attribute' => '7',
                    ],
                ],
            ]); ?>

            <?= Html::beginForm(['index'], 'post',
                ['id' => 'pjax-form', 'data-pjax' => '', 'style' => 'display:none']); ?>
                <?= Html::hiddenInput('google-file-link', '', ['id' => 'pjax-google-file-link-input']) ?>
                <?= Html::hiddenInput('from-date', '', ['id' => 'pjax-from-date-input']) ?>
                <?= Html::hiddenInput('to-date', '', ['id' => 'pjax-to-date-input']) ?>
                <?= Html::submitButton('Вычислить', ['id' => 'pjax-button', 'data-pjax' => '']) ?>
            <?= Html::endForm() ?>

        <?php Pjax::end(); ?>
    </div>
</div>