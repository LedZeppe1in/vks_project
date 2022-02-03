<?php

/* @var $this yii\web\View */
/* @var $cloudDriveModel app\models\CloudDriveForm */
/* @var $notificationModel app\models\NotificationForm */
/* @var $employees app\models\NotificationForm */

$this->title = 'Синхронизация данных';
$this->params['breadcrumbs'][] = $this->title;

use yii\helpers\Html;
use yii\bootstrap\Tabs;
?>

<!-- Подключение скрипта для проверки формы -->
<?php $this->registerJsFile('/js/form-validation.js', ['position' => yii\web\View::POS_HEAD]) ?>

<!-- JS-скрипт -->
<script type="text/javascript">
    // Переменная для хранения общего объема рассылки
    let fullMailingVolume;
    // Переменная для хранения списка сотрудников
    let employees;

    // Обработка выбора общего чекбокса в GridView
    function checkAllEmployees(item_id, checked) {
        // Слой с отображением объема рассылки для выбранных сотрудников
        let customMailingVolumeTitle = document.getElementById("custom-mailing-volume");
        // Определение состояния чекбокса
        if (checked === true)
            customMailingVolumeTitle.innerHTML = fullMailingVolume;
        else
            customMailingVolumeTitle.innerHTML = "0";
    }

    // Обработка выбора индивидуального чекбокса в GridView
    function checkEmployee(item_id, checked) {
        // Формирование массива с выбранными сотрудниками
        let checked_employees = [];
        employees.forEach(function(item, i) {
            if (document.querySelectorAll("input[type='checkbox']")[i + 1].checked)
                checked_employees.push(item);
        });
        // Ajax-запрос
        $.ajax({
            url: "<?= Yii::$app->request->baseUrl . '/get-mailing-volume' ?>",
            type: "post",
            data: "employees=" + JSON.stringify(checked_employees),
            dataType: "json",
            success: function(data) {
                // Слой с отображением объема рассылки для выбранных сотрудников
                let customMailingVolumeTitle = document.getElementById("custom-mailing-volume");
                // Отображение текущего объема рассылки для выбранных сотрудников
                customMailingVolumeTitle.innerHTML = data["mailingVolume"];
            },
            error: function() {
                alert("Непредвиденная ошибка!");
            }
        });
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

        // Сообщение об успешной проверке таблиц
        let checkingSuccessMessage = document.getElementById("checking-success-message");
        // Сообщение об ошибке проверки таблиц
        let checkingErrorMessage = document.getElementById("checking-error-message");
        // Сообщение об успешнос сохранении путей к файлам электронных таблиц
        let savePathsSuccessMessage = document.getElementById("save-paths-success-message");
        // Сообщение об ошибке копировании электронной таблицы Google
        let copyErrorMessage = document.getElementById("copy-error-message");
        // Сообщение о не сформированном списке рассылки
        let employeesWarningMessage = document.getElementById("employees-warning-message");
        // Сообщение об успешном формировании списка рассылки
        let employeesSuccessMessage = document.getElementById("employees-success-message");
        // Слои с подробной информацией Google-таблицы
        let googleMetaInformationTitle = document.getElementById("google-meta-information-title");
        let googleMetaInformation = document.getElementById("google-meta-information");
        // Слои с подробной информацией Yandex-таблицы
        let yandexMetaInformationTitle = document.getElementById("yandex-meta-information-title");
        let yandexMetaInformation = document.getElementById("yandex-meta-information");
        // Ссылка на вкладке "Информирование"
        let informationTabLink = document.getElementById("information-tab-link");
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

        // Обработка нажатия кнопки проверки
        $("#checking-button").click(function(e) {
            // Отмена поведения кнопки по умолчанию (submit)
            e.preventDefault();
            // Форма с полями
            let form = $("#cloud-drive-form");
            // Скрытие слоев с подробной информацией о файлах электронных таблиц
            googleMetaInformationTitle.style.display = "none";
            googleMetaInformation.style.display = "none";
            yandexMetaInformationTitle.style.display = "none";
            yandexMetaInformation.style.display = "none";
            // Ajax-запрос
            $.ajax({
                url: "<?= Yii::$app->request->baseUrl . '/checking' ?>",
                type: "post",
                data: form.serialize(),
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data["success"]) {
                        // Скрытие списка ошибок ввода
                        $("#cloud-drive-form .error-summary").hide();
                        // Если ошибки при проверке таблиц нет
                        if (!data["checkingError"]) {
                            // Активация слоя с сообщением об успешной проверке таблиц
                            checkingSuccessMessage.style.display = "block";
                            // Деативация всех остальных слоев с сообщениями
                            checkingErrorMessage.style.display = "none";
                            savePathsSuccessMessage.style.display = "none";
                            copyErrorMessage.style.display = "none";
                            employeesWarningMessage.style.display = "none";
                            employeesSuccessMessage.style.display = "none";
                            // Формирование текста подробной информации о файле с Google-диска
                            googleMetaInformation.innerHTML = "<b>ID файла:</b> " +
                                data["googleResource"]["id"] + "<br/>";
                            googleMetaInformation.innerHTML += "<b>Имя файла:</b> " +
                                data["googleResource"]["name"] + "<br/>";
                            googleMetaInformation.innerHTML += "<b>Тип:</b> " +
                                data["googleResource"]["kind"] + "<br/>";
                            googleMetaInformation.innerHTML += "<b>MIME-тип:</b> " +
                                data["googleResource"]["mimeType"] + "<br/>";
                            // Активация слоев с подробной информацией о файле с Yandex-диска
                            googleMetaInformationTitle.style.display = "block";
                            googleMetaInformation.style.display = "block";
                            // Формирование текста подробной информации о файле с Yandex-диска
                            yandexMetaInformation.innerHTML = "<b>Ключ опубликованного файла:</b> " +
                                data["yandexResource"]["public_key"] + "<br/>";
                            yandexMetaInformation.innerHTML += "<b>Ссылка на опубликованный файл:</b> " +
                                data["yandexResource"]["public_url"] + "<br/>";
                            yandexMetaInformation.innerHTML += "<b>Имя файла:</b> " +
                                data["yandexResource"]["name"] + "<br/>";
                            yandexMetaInformation.innerHTML += "<b>Размер файла:</b> " +
                                data["yandexResource"]["size"] + "<br/>";
                            yandexMetaInformation.innerHTML += "<b>Полный путь к файлу на диске:</b> " +
                                data["yandexResource"]["path"] + "<br/>";
                            yandexMetaInformation.innerHTML += "<b>Дата и время создания файла:</b> " +
                                data["yandexResource"]["created"] + "<br/>";
                            yandexMetaInformation.innerHTML += "<b>Дата и время изменения файла:</b> " +
                                data["yandexResource"]["modified"];
                            // Активация слоев с подробной информацией о файле с Yandex-диска
                            yandexMetaInformationTitle.style.display = "block";
                            yandexMetaInformation.style.display = "block";
                            // Скрытие индикатора прогресса
                            $("#overlay").hide();
                            spinner.stop(target);
                            //
                            console.log(data["googleResource"]);
                        } else {
                            // Активация слоя с сообщением об ошибке проверки таблиц
                            checkingErrorMessage.style.display = "block";
                            // Деативация всех остальных слоев с сообщениями
                            checkingSuccessMessage.style.display = "none";
                            savePathsSuccessMessage.style.display = "none";
                            copyErrorMessage.style.display = "none";
                            employeesWarningMessage.style.display = "none";
                            employeesSuccessMessage.style.display = "none";
                            // Скрытие индикатора прогресса
                            $("#overlay").hide();
                            spinner.stop(target);
                        }
                    } else {
                        // Деактивация всех слоев с сообщениями
                        savePathsSuccessMessage.style.display = "none";
                        copyErrorMessage.style.display = "none";
                        employeesWarningMessage.style.display = "none";
                        employeesSuccessMessage.style.display = "none";
                        checkingErrorMessage.style.display = "none";
                        checkingSuccessMessage.style.display = "none";
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

        // Обработка нажатия кнопки сохранения путей
        $("#save-paths-button").click(function(e) {
            // Отмена поведения кнопки по умолчанию (submit)
            e.preventDefault();
            // Форма с полями
            let form = $("#cloud-drive-form");
            // Скрытие слоев с подробной информацией о файлах электронных таблиц
            googleMetaInformationTitle.style.display = "none";
            googleMetaInformation.style.display = "none";
            yandexMetaInformationTitle.style.display = "none";
            yandexMetaInformation.style.display = "none";
            // Ajax-запрос
            $.ajax({
                url: "<?= Yii::$app->request->baseUrl . '/save-paths' ?>",
                type: "post",
                data: form.serialize(),
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data["success"]) {
                        // Скрытие списка ошибок ввода
                        $("#cloud-drive-form .error-summary").hide();
                        // Активация слоя с сообщением об успешном сохранении файла с путями к электронным таблицам
                        savePathsSuccessMessage.style.display = "block";
                        // Деактивация всех слоев с сообщениями
                        copyErrorMessage.style.display = "none";
                        employeesWarningMessage.style.display = "none";
                        employeesSuccessMessage.style.display = "none";
                        checkingErrorMessage.style.display = "none";
                        checkingSuccessMessage.style.display = "none";
                        // Скрытие индикатора прогресса
                        $("#overlay").hide();
                        spinner.stop(target);
                    } else {
                        // Деактивация всех слоев с сообщениями
                        savePathsSuccessMessage.style.display = "none";
                        copyErrorMessage.style.display = "none";
                        employeesWarningMessage.style.display = "none";
                        employeesSuccessMessage.style.display = "none";
                        checkingErrorMessage.style.display = "none";
                        checkingSuccessMessage.style.display = "none";
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

        // Обработка нажатия кнопки формирования списка рассылки
        $("#mailing-button").click(function(e) {
            // Отмена поведения кнопки по умолчанию (submit)
            e.preventDefault();
            // Форма с полями
            let form = $("#cloud-drive-form");
            // Скрытие слоев с подробной информацией о файлах электронных таблиц
            googleMetaInformationTitle.style.display = "none";
            googleMetaInformation.style.display = "none";
            yandexMetaInformationTitle.style.display = "none";
            yandexMetaInformation.style.display = "none";
            // Ajax-запрос
            $.ajax({
                url: "<?= Yii::$app->request->baseUrl . '/get-mailing-list' ?>",
                type: "post",
                data: form.serialize() + "&all_employees=false",
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
                                // Если нет ошибок, то вывод текущего баланса
                                if (data["balance"] != '-1' && data["balance"] != '-2')
                                    currentBalanceTitle.innerHTML = data["balance"] + " СМС";
                                else
                                    currentBalanceTitle.innerHTML = "не удалось проверить баланс";
                                // Формирование информации об общем объеме рассылки для всех сотрудников
                                fullMailingVolume = data["mailingVolume"]
                                fullMailingVolumeTitle.innerHTML = fullMailingVolume;
                            } else {
                                // Активация слоя с сообщением о не успешном формировании списка сотрудников для оповещения
                                employeesWarningMessage.style.display = "block";
                                // Деативация всех остальных слоев с сообщениями
                                savePathsSuccessMessage.style.display = "none";
                                copyErrorMessage.style.display = "none";
                                employeesSuccessMessage.style.display = "none";
                                checkingErrorMessage.style.display = "none";
                                checkingSuccessMessage.style.display = "none";
                                // Деактивация вкладки информирования
                                $("#information-tab").addClass("disabled");
                                informationTabLink.dataset.toggle = "";
                                // Скрытие индикатора прогресса
                                $("#overlay").hide();
                                spinner.stop(target);
                            }
                        } else {
                            // Активация слоя с сообщением об ошибке копировании Google-таблицы
                            copyErrorMessage.style.display = "block";
                            // Деативация всех остальных слоев с сообщениями
                            savePathsSuccessMessage.style.display = "none";
                            employeesWarningMessage.style.display = "none";
                            employeesSuccessMessage.style.display = "none";
                            checkingErrorMessage.style.display = "none";
                            checkingSuccessMessage.style.display = "none";
                            // Деактивация вкладки информирования
                            $("#information-tab").addClass("disabled");
                            informationTabLink.dataset.toggle = "";
                            // Скрытие индикатора прогресса
                            $("#overlay").hide();
                            spinner.stop(target);
                        }
                    } else {
                        // Деактивация всех слоев с сообщениями
                        savePathsSuccessMessage.style.display = "none";
                        copyErrorMessage.style.display = "none";
                        employeesWarningMessage.style.display = "none";
                        employeesSuccessMessage.style.display = "none";
                        checkingErrorMessage.style.display = "none";
                        checkingSuccessMessage.style.display = "none";
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
            savePathsSuccessMessage.style.display = "none";
            copyErrorMessage.style.display = "none";
            employeesWarningMessage.style.display = "none";
            checkingErrorMessage.style.display = "none";
            checkingSuccessMessage.style.display = "none";
            // Активация вкладки информирования
            $("#information-tab").removeClass("disabled");
            informationTabLink.dataset.toggle = "tab";
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
            console.log(checked_employees);
            // Ajax-запрос
            $.ajax({
                url: "<?= Yii::$app->request->baseUrl . '/notify-employees' ?>",
                type: "post",
                data: form.serialize() + "&employees=" + JSON.stringify(checked_employees),
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data["success"]) {
                        // Скрытие списка ошибок ввода
                        $("#notification-form .error-summary").hide();
                        //
                        console.log(data["smsoResponse"]);
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
                        if (data["balance"] != '-1' && data["balance"] != '-2')
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
        <?php echo Tabs::widget([
            'items' => [
                [
                    'label' => 'Данные с облачных дисков',
                    'content' => $this->render('_cloud_drive', [
                        'cloudDriveModel' => $cloudDriveModel
                    ]),
                ],
                [
                    'label' => 'Информирование',
                    'content' => $this->render('_notification', [
                        'notificationModel' => $notificationModel,
                        'employees' => $employees,
                    ]),
                    'headerOptions' => [
                        'id' => 'information-tab',
                        'class' => 'disabled'
                    ],
                    'linkOptions' => [
                        'id' => 'information-tab-link',
                        'data-toggle' => ''
                    ]
                ]
            ]
        ]); ?>
    </div>

</div>