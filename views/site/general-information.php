<?php

/* @var $this yii\web\View */
/* @var $cloudDriveModel app\models\CloudDriveForm */
/* @var $notificationModel app\models\NotificationForm */
/* @var $employees app\models\NotificationForm */
/* @var $currentBalance app\controllers\SiteController */

$this->title = 'Общее информирование';

use kartik\date\DatePicker;
use yii\helpers\Html;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\bootstrap\Button;
use yii\bootstrap\ActiveForm;
?>

<!-- Подключение скрипта для проверки формы -->
<?php $this->registerJsFile('/js/form-validation.js', ['position' => yii\web\View::POS_HEAD]) ?>

<!-- JS-скрипт -->
<script type="text/javascript">
    // Переменная для хранения общего объема рассылки
    let fullMailingVolume = null;
    // Переменная для хранения списка сотрудников
    let employees = null;

    // Обработка выбора общего чекбокса в GridView
    function checkAllEmployees(item_id, checked) {
        // Слой с отображением общего объема рассылки для всех сотрудников
        let fullMailingVolumeTitle = document.getElementById("full-mailing-volume");
        // Слой с отображением объема рассылки для выбранных сотрудников
        let customMailingVolumeTitle = document.getElementById("custom-mailing-volume");
        // Определение состояния чекбокса
        if (checked === true) {
            if (fullMailingVolume !== null) {
                // Текст сообщения сотрудникам
                let message = document.getElementById("notificationform-messagetemplate").value;
                // Общий текст всех сообщений
                let allMessages = "";
                for (let i = 0; i < employees.length; i++)
                    allMessages += message;
                // Определение объема рассылки
                fullMailingVolume = Math.ceil(allMessages.length / 67);
                // Вывод объема рассылки
                fullMailingVolumeTitle.innerHTML = fullMailingVolume;
                customMailingVolumeTitle.innerHTML = fullMailingVolume;
            }
        } else
            customMailingVolumeTitle.innerHTML = "0";
    }

    // Обработка выбора индивидуального чекбокса в GridView
    function checkEmployee(item_id, checked) {
        // Слой с отображением объема рассылки для выбранных сотрудников
        let customMailingVolumeTitle = document.getElementById("custom-mailing-volume");
        // Текст сообщения сотрудникам
        let message = document.getElementById("notificationform-messagetemplate").value;
        // Общий текст всех сообщений
        let allMessages = "";
        employees.forEach(function(item, i) {
           if (document.querySelectorAll("input[type='checkbox']")[i + 1].checked)
               allMessages += message;
        });
        // Определение объема рассылки
        fullMailingVolume = Math.ceil(allMessages.length / 67);
        // Вывод объема рассылки
        customMailingVolumeTitle.innerHTML = fullMailingVolume;
    }

    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Обработка появления индикатора прогресса
        let target = document.getElementById("center");
        let spinner = new Spinner(spinner_options).spin();
        $("button.btn-primary").click(function() {
            $("#overlay").show();
            spinner.spin(target);
        });
        $("button.btn-success").click(function() {
            $("#overlay").show();
            spinner.spin(target);
        });
        // Обработка события после валидации формы
        $("#cloud-drive-form").on("afterValidate", function (e, m, attr) {
            e.preventDefault();
            if(attr.length > 0) {
                // Скрытие индикатора прогресса
                $("#overlay").hide();
                spinner.stop(target);
            }
        });

        // Сообщение о не сформированном списке рассылки
        let employeesWarningMessage = document.getElementById("employees-warning-message");
        // Сообщение об успешном формировании списка рассылки
        let employeesSuccessMessage = document.getElementById("employees-success-message");
        // Сообщение об успешном сохранении файла с текстом шаблона сообщения
        let saveFileSuccessMessage = document.getElementById("save-file-success-message");
        // Сообщение об успешном оповещении сотрудников
        let notificationSuccessMessage = document.getElementById("notification-success-message");
        // Сообщение о не выбранных сотрудниках
        let notificationWarningMessage = document.getElementById("notification-warning-message");
        // Слой с отображением текущего баланса
        let currentBalanceTitle = document.getElementById("current-balance");
        // Слой с отображением общего объема рассылки для всех сотрудников
        let fullMailingVolumeTitle = document.getElementById("full-mailing-volume");

        // Обработка нажатия кнопки формирования списка рассылки
        $("#mailing-button").click(function(e) {
            // Отмена поведения кнопки по умолчанию (submit)
            e.preventDefault();
            // Форма с полями
            let form = $("#cloud-drive-form");
            // Текст сообщения сотрудникам
            let message = document.getElementById("notificationform-messagetemplate").value;
            // Ajax-запрос
            $.ajax({
                url: "<?= Yii::$app->request->baseUrl . '/get-mailing-list' ?>",
                type: "post",
                data: form.serialize() + "&all_employees=true&message=" + JSON.stringify(message),
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data["success"]) {
                        // Скрытие списка ошибок ввода
                        $("#cloud-drive-form .error-summary").hide();
                        // Если ошибки при копировании электронной таблицы Google нет
                        if (!data["copyError"]) {
                            // Если список сотрудников для оповещения сформирован
                            if (data["employees"].length !== 0) {
                                // Присваивание значениям скрытых полей значений из формы CloudDriveForm
                                document.getElementById("pjax-google-file-link-input").value =
                                    document.getElementById("clouddriveform-googlefilelink").value;
                                document.getElementById("pjax-from-date-input").value =
                                    document.getElementById("clouddriveform-fromdate").value;
                                document.getElementById("pjax-to-date-input").value =
                                    document.getElementById("clouddriveform-todate").value;
                                // Вызов события нажатия кнопки для pjax
                                document.getElementById("pjax-button").click();
                                // Формирование списка сотрудников для оповещения
                                employees = data["employees"];
                                // Формирование информации об общем объеме рассылки для всех сотрудников
                                fullMailingVolume = data["mailingVolume"];
                                fullMailingVolumeTitle.innerHTML = fullMailingVolume;
                            } else {
                                // Активация слоя с сообщением о не успешном формировании списка сотрудников для оповещения
                                employeesWarningMessage.style.display = "block";
                                // Деативация всех остальных слоев с сообщениями
                                employeesSuccessMessage.style.display = "none";
                                // Скрытие индикатора прогресса
                                $("#overlay").hide();
                                spinner.stop(target);
                            }
                        } else {
                            // Деативация всех остальных слоев с сообщениями
                            employeesWarningMessage.style.display = "none";
                            employeesSuccessMessage.style.display = "none";
                            // Скрытие индикатора прогресса
                            $("#overlay").hide();
                            spinner.stop(target);
                        }
                    } else {
                        // Деактивация всех слоев с сообщениями
                        employeesWarningMessage.style.display = "none";
                        employeesSuccessMessage.style.display = "none";
                        // Отображение ошибок ввода
                        viewErrors("#cloud-drive-form", data);
                        // Скрытие индикатора прогресса
                        $("#overlay").hide();
                        spinner.stop(target);
                    }
                },
                error: function() {
                    // Скрытие индикатора прогресса
                    $("#overlay").hide();
                    spinner.stop(target);
                    alert("Непредвиденная ошибка!");
                }
            });
        });

        // Обработка после выполнения pjax
        $(document).on("ready pjax:success", function() {
            // Активация слоя с сообщением об успешном формировании списка сотрудников для оповещения
            employeesSuccessMessage.style.display = "block";
            // Деативация всех остальных слоев с сообщениями
            employeesWarningMessage.style.display = "none";
            // Активация кнопки оповещения
            document.getElementById("notification-button").disabled = false;
            // Скрытие индикатора прогресса
            $("#overlay").hide();
            spinner.stop(target);
        });

        // Обработка нажатия кнопки сохранения шаблона сообщения
        $("#save-message-template-button").click(function(e) {
            // Отмена поведения кнопки по умолчанию (submit)
            e.preventDefault();
            // Форма с полем шаблона текста сообщения
            let form = $("#notification-form");
            // Ajax-запрос
            $.ajax({
                url: "<?= Yii::$app->request->baseUrl . '/save-general-message-template' ?>",
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
                        // Деактивация всех слоев с сообщениями
                        notificationSuccessMessage.style.display = "none";
                        notificationWarningMessage.style.display = "none";
                        // Скрытие индикатора прогресса
                        $("#overlay").hide();
                        spinner.stop(target);
                    } else {
                        // Деактивация всех слоев с сообщениями
                        saveFileSuccessMessage.style.display = "none";
                        notificationSuccessMessage.style.display = "none";
                        notificationWarningMessage.style.display = "none";
                        // Отображение ошибок ввода
                        viewErrors("#notification-form", data);
                        // Скрытие индикатора прогресса
                        $("#overlay").hide();
                        spinner.stop(target);
                    }
                },
                error: function() {
                    // Скрытие индикатора прогресса
                    $("#overlay").hide();
                    spinner.stop(target);
                    alert("Непредвиденная ошибка!");
                }
            });
        });

        // Обработка нажатия кнопки оповещения сотрудников
        $("#notification-button").click(function(e) {
            // Отмена поведения кнопки по умолчанию (submit)
            e.preventDefault();
            // Форма с полем шаблона текста сообщения
            let form = $("#notification-form");
            // Формирование массива с выбранными сотрудниками
            var checked_employees = [];
            employees.forEach(function(item, i) {
                if (document.querySelectorAll("input[type='checkbox']")[i + 1].checked)
                    checked_employees.push(item);
            });
            // Текст сообщения сотрудникам
            let message = document.getElementById("notificationform-messagetemplate").value;
            // Ajax-запрос
            $.ajax({
                url: "<?= Yii::$app->request->baseUrl . '/notify-employees' ?>",
                type: "post",
                data: form.serialize() + "&employees=" + JSON.stringify(checked_employees) + JSON.stringify(message),
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data["success"]) {
                        // Скрытие списка ошибок ввода
                        $("#notification-form .error-summary").hide();
                        // Если нет сотрудников для оповещения
                        if (data["smsoResponse"].length !== 0) {
                            // Активация слоя с сообщением об успешной отправке сообщений сотрудникам
                            notificationSuccessMessage.style.display = "block";
                            notificationWarningMessage.style.display = "none";
                        } else {
                            // Активация слоя с сообщением о не выбранных сотрудниках
                            notificationSuccessMessage.style.display = "none";
                            notificationWarningMessage.style.display = "block";
                        }
                        // Деактивация всех слоев с сообщениями
                        saveFileSuccessMessage.style.display = "none";
                        // Если нет ошибок, то вывод текущего баланса
                        if (data["balance"] !== '-1' && data["balance"] !== '-2')
                            currentBalanceTitle.innerHTML = data["balance"] + " СМС";
                        else
                            currentBalanceTitle.innerHTML = "не удалось проверить баланс";
                        // Изменение статусов
                        $("#employees-list table tr").each(function () {
                            let cell = $(this).find("td:last-of-type");
                            cell.html("Отправлено");
                        });
                        // Скрытие индикатора прогресса
                        $("#overlay").hide();
                        spinner.stop(target);
                    } else {
                        // Деактивация всех слоев с сообщениями
                        saveFileSuccessMessage.style.display = "none";
                        notificationSuccessMessage.style.display = "none";
                        notificationWarningMessage.style.display = "none";
                        // Отображение ошибок ввода
                        viewErrors("#notification-form", data);
                        // Скрытие индикатора прогресса
                        $("#overlay").hide();
                        spinner.stop(target);
                    }
                },
                error: function() {
                    // Скрытие индикатора прогресса
                    $("#overlay").hide();
                    spinner.stop(target);
                    alert("Непредвиденная ошибка!");
                }
            });
        });
    });
</script>

<div class="data-synchronization">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="body-content">

        <div id="employees-warning-message" class="alert alert-warning alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Внимание!</strong> Список сотрудников для оповещения не сформирован.
        </div>

        <div id="employees-success-message" class="alert alert-success alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Список рассылки сформирован!</strong> Вы успешно сформировали список сотрудников для оповещения.
        </div>

        <div id="save-file-success-message" class="alert alert-success alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Сообщение сохранено!</strong> Вы успешно сохранили текст с шаблоном сообщения.
        </div>

        <div id="notification-success-message" class="alert alert-success alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Оповещение прошло успешно!</strong> Вы успешно оповестили всех выбранных сотрудников из списка.
        </div>

        <div id="notification-warning-message" class="alert alert-warning alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Внимание!</strong> Сотрудники для оповещения не выбраны.
        </div>

        <hr />

        <h3>Статистика СМС:</h3>

        <span style="margin-left: 10px;"><b>Текущий баланс: </b></span>
        <span id="current-balance" class="badge" style="margin-bottom: 2px;"><?= $currentBalance ?> СМС</span>
            <?= Html::a('Запрос счета на пополнение баланса', ['/site/balance-replenishment']); ?><br /><br />
        <span style="margin-left: 10px;"><b>Общий объём рассылки для всех сотрудников: </b></span>
        <span id="full-mailing-volume" class="badge" style="margin-bottom: 2px;">0</span><br /><br />
        <span style="margin-left: 10px;"><b>Объём рассылки для выбранных сотрудников: </b></span>
        <span id="custom-mailing-volume" class="badge" style="margin-bottom: 2px;">0</span><br /><br />

        <h3>Оповещение сотрудников:</h3>

        <?php $form = ActiveForm::begin([
            'id' => 'notification-form',
            'enableClientValidation' => true,
        ]); ?>

            <?= $form->errorSummary($notificationModel); ?>

            <?= $form->field($notificationModel, 'messageTemplate')->textarea(['rows' => 8])
                ->label('Текст сообщения') ?>

            <div class="form-group">
                <?= Button::widget([
                    'label' => '<span class="glyphicon glyphicon-bell"></span> Оповестить выбранных сотрудников',
                    'encodeLabel' => false,
                    'options' => [
                        'id' => 'notification-button',
                        'class' => 'btn btn-success',
                        'disabled' => 'disabled'
                    ]
                ]); ?>
                <?= Button::widget([
                    'label' => '<span class="glyphicon glyphicon-save"></span> Сохранить шаблон сообщения',
                    'encodeLabel' => false,
                    'options' => [
                        'id' => 'save-message-template-button',
                        'class' => 'btn btn-primary'
                    ]
                ]); ?>
            </div>

        <?php ActiveForm::end(); ?><br />

        <?php $form = ActiveForm::begin([
            'id' => 'cloud-drive-form',
            'enableClientValidation' => true
        ]); ?>

            <?= $form->errorSummary($cloudDriveModel); ?>

            <?= $form->field($cloudDriveModel, 'googleFileLink')->hiddenInput()->label(false) ?>

            <?= $form->field($cloudDriveModel, 'yandexFilePath')->hiddenInput()->label(false) ?>

            <div style="width: 300px; margin-bottom: 10px; display: none">
                <label class="control-label has-star">Даты выборки</label>
                <?= DatePicker::widget([
                    'model' => $cloudDriveModel,
                    'form' => $form,
                    'type' => DatePicker::TYPE_RANGE,
                    'language' => 'ru',
                    'attribute' => 'fromDate',
                    'attribute2' => 'toDate',
                    'options' => [
                        'placeholder' => 'Дата начала',
                    ],
                    'options2' => [
                        'placeholder' => 'Дата окончания'
                    ],
                    'separator' => ' до ',
                    'pluginOptions' => [
                        'format' => 'dd.mm.yyyy',
                        'autoclose' => true
                    ]
                ]); ?>
            </div>

            <div class="form-group">
                <?= Button::widget([
                    'label' => '<span class="glyphicon glyphicon-list-alt"></span> Сформировать список рассылки',
                    'encodeLabel' => false,
                    'options' => [
                        'id' => 'mailing-button',
                        'class' => 'btn btn-success'
                    ]
                ]); ?>
            </div>

        <?php ActiveForm::end(); ?>

        <h3>Список сотрудников для оповещения:</h3>

        <?php Pjax::begin(['id' => 'pjaxGrid']); ?>

            <?= GridView::widget([
                'dataProvider' => $employees,
                'id' => 'employees-list',
                'columns' => [
                    [
                        'class' => 'yii\grid\CheckboxColumn',
                        'header' => Html::checkBox('selection_all', false, [
                            'id' => 'select-all-employees',
                            'class' => 'select-on-check-all',
                            'onclick' => 'js:checkAllEmployees(this.value, this.checked)'
                        ]),
                        'checkboxOptions' => ['onclick' => 'js:checkEmployee(this.value, this.checked)']
                    ],
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
                        'label' => 'Карта',
                        'attribute' => '3',
                    ],
                    [
                        'label' => 'Сеть',
                        'attribute' => '4',
                    ],
                    [
                        'label' => 'Виды работ',
                        'attribute' => '5',
                    ],
                    [
                        'label' => 'Статус',
                    ],
                ],
            ]); ?>

            <?= Html::beginForm(['general-information'], 'post',
                ['id' => 'pjax-form', 'data-pjax' => '', 'style' => 'display:none']); ?>
                <?= Html::hiddenInput('google-file-link', '', ['id' => 'pjax-google-file-link-input']) ?>
                <?= Html::hiddenInput('from-date', '', ['id' => 'pjax-from-date-input']) ?>
                <?= Html::hiddenInput('to-date', '', ['id' => 'pjax-to-date-input']) ?>
                <?= Html::submitButton('Вычислить', ['id' => 'pjax-button', 'data-pjax' => '']) ?>
            <?= Html::endForm() ?>

        <?php Pjax::end(); ?>
    </div>
</div>