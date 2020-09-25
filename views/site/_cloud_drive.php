<?php

/* @var $cloudDriveModel app\models\CloudDriveForm */

use yii\helpers\Html;
use yii\bootstrap\Button;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
use app\models\CloudDriveForm;
?>

<!-- Подключение скрипта для проверки формы -->
<?php $this->registerJsFile('/js/form-validation.js', ['position' => yii\web\View::POS_HEAD]) ?>

<!-- JS-скрипт -->
<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Сообщение об успешной проверке таблиц
        let checkingSuccessMessage = document.getElementById("checking-success-message");
        // Сообщение об ошибке проверки таблиц
        let checkingErrorMessage = document.getElementById("checking-error-message");
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
                        // Если ошибки при проверке таблиц нет
                        if (!data["checking_error"]) {
                            // Активация слоя с сообщением об успешной проверке таблиц
                            checkingSuccessMessage.style.display = "block";
                            // Деативация всех остальных слоев с сообщениями
                            checkingErrorMessage.style.display = "none";
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
                        } else {
                            // Активация слоя с сообщением об ошибке проверки таблиц
                            checkingErrorMessage.style.display = "block";
                            // Деативация всех остальных слоев с сообщениями
                            checkingSuccessMessage.style.display = "none";
                            copyErrorMessage.style.display = "none";
                            employeesWarningMessage.style.display = "none";
                            employeesSuccessMessage.style.display = "none";
                        }
                    } else
                        // Отображение ошибок ввода
                        viewErrors("#cloud-drive-form", data);
                },
                error: function() {
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
            // Ajax-запрос
            $.ajax({
                url: "<?= Yii::$app->request->baseUrl . '/get-mailing-list' ?>",
                type: "post",
                data: form.serialize(),
                dataType: "json",
                success: function(data) {
                    // Если валидация прошла успешно (нет ошибок ввода)
                    if (data["success"]) {
                        // Если ошибки при копировании электронной таблицы Google нет
                        if (!data["copy_error"]) {
                            // Если список сотрудников для оповещения не сформирован
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
                            } else {
                                // Активация слоя с сообщением о не успешном формировании списка сотрудников для оповещения
                                employeesWarningMessage.style.display = "block";
                                // Деативация всех остальных слоев с сообщениями
                                copyErrorMessage.style.display = "none";
                                employeesSuccessMessage.style.display = "none";
                                checkingErrorMessage.style.display = "none";
                                checkingSuccessMessage.style.display = "none";
                                // Деактивация вкладки информирования
                                $("#information-tab").addClass("disabled");
                                informationTabLink.dataset.toggle = "";
                            }
                        } else {
                            // Активация слоя с сообщением об ошибке копировании Google-таблицы
                            copyErrorMessage.style.display = "block";
                            // Деативация всех остальных слоев с сообщениями
                            employeesWarningMessage.style.display = "none";
                            employeesSuccessMessage.style.display = "none";
                            checkingErrorMessage.style.display = "none";
                            checkingSuccessMessage.style.display = "none";
                            // Деактивация вкладки информирования
                            $("#information-tab").addClass("disabled");
                            informationTabLink.dataset.toggle = "";
                        }
                    } else
                        // Отображение ошибок ввода
                        viewErrors("#cloud-drive-form", data);
                },
                error: function() {
                    alert("Непредвиденная ошибка!");
                }
            });
        });

        //
        $(document).on('ready pjax:success', function() {
            // Активация слоя с сообщением об успешном формировании списка сотрудников для оповещения
            employeesSuccessMessage.style.display = "block";
            // Деативация всех остальных слоев с сообщениями
            copyErrorMessage.style.display = "none";
            employeesWarningMessage.style.display = "none";
            checkingErrorMessage.style.display = "none";
            checkingSuccessMessage.style.display = "none";
            // Активация вкладки информирования
            $("#information-tab").removeClass("disabled");
            informationTabLink.dataset.toggle = "tab";
        });
    });
</script>

<div class="row">
    <div class="col-md-12">

        <h2>Данные с облачных дисков</h2>

        <div id="checking-success-message" class="alert alert-success alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Успешная проверка!</strong> Проверка прошла успешно, ниже представлена подробная информация.
        </div>

        <div id="checking-error-message" class="alert alert-danger alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Ошибка!</strong> Проверка прошла с ошибкой, возможно Вы неверно указали ссылку или путь на файлы электронных таблиц.
        </div>

        <div id="copy-error-message" class="alert alert-danger alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Ошибка!</strong> Возможно Вы неверно указали ссылку на файл электронной таблиц на Google-диске.
        </div>

        <div id="employees-warning-message" class="alert alert-warning alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Внимание!</strong> Список сотрудников для оповещения не сформирован, так как нет подтвержденных заявок.
        </div>

        <div id="employees-success-message" class="alert alert-success alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Список рассылки сформирован!</strong> Вы успешно сформировали список сотрудников для оповещения. Перейдите на вкладку "Информирование" для оповещения сотрудников.
        </div>

        <?php $form = ActiveForm::begin([
            'id' => 'cloud-drive-form',
            'enableClientValidation' => true,
        ]); ?>

            <?= $form->errorSummary($cloudDriveModel); ?>

            <?= $form->field($cloudDriveModel, 'googleFileLink')
                ->textInput(['value' => CloudDriveForm::GOOGLE_FILE_LINK]) ?>

            <?= $form->field($cloudDriveModel, 'yandexFilePath')
                ->textInput(['value' => CloudDriveForm::YANDEX_FILE_PATH]) ?>

            <div style="width: 300px; margin-bottom: 10px;">
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
            <span class="badge" style="margin-bottom: 10px;">
                Если Вы хотите использовать даты для выборки определенных строк, то введите обе даты.
            </span>

            <div class="form-group">
                <?= Button::widget([
                    'label' => '<span class="glyphicon glyphicon-check"></span> Проверить',
                    'encodeLabel' => false,
                    'options' => [
                        'id' => 'checking-button',
                        'class' => 'btn btn-primary'
                    ]
                ]); ?>
                <?= Html::submitButton('<span class="glyphicon glyphicon-refresh"></span> Синхронизировать с Яндекс-диском',
                    ['class' => 'btn btn-success', 'name' => 'synchronization-button', 'value' => 'yandex-synchronization']); ?>
                <?= Html::submitButton('<span class="glyphicon glyphicon-refresh"></span> Синхронизировать с  Google-диском',
                    ['class' => 'btn btn-success', 'name' => 'synchronization-button', 'value' => 'google-synchronization']); ?>
                <?= Button::widget([
                    'label' => '<span class="glyphicon glyphicon-list-alt"></span> Сформировать список рассылки',
                    'encodeLabel' => false,
                    'options' => [
                        'id' => 'mailing-button',
                        'class' => 'btn btn-primary'
                    ]
                ]); ?>
            </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<h3 id="google-meta-information-title" style="display: none">Подробная информация о таблице с Google Sheet:</h3>
<div id="google-meta-information" class="well" style="display: none"></div>

<h3 id="yandex-meta-information-title" style="display: none">Подробная информация о таблице с Yandex-диска:</h3>
<div id="yandex-meta-information" class="well" style="display: none"></div>