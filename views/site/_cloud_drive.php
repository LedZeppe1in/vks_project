<?php

/* @var $cloudDriveModel app\models\CloudDriveForm */

use yii\helpers\Html;
use yii\bootstrap\Button;
use yii\bootstrap\ActiveForm;
use app\models\CloudDriveForm;
?>

<!-- Подключение скрипта для проверки формы -->
<?php $this->registerJsFile('/js/form-validation.js', ['position' => yii\web\View::POS_HEAD]) ?>

<!-- JS-скрипт -->
<script type="text/javascript">
    // Выполнение скрипта при загрузке страницы
    $(document).ready(function() {
        // Сообщение об успешной проверке
        let checkingSuccessMessage = document.getElementById("checking-success-message");
        // Сообщение об ошибке
        let checkingErrorMessage = document.getElementById("checking-error-message");
        // Слои с подробной информацией google-таблицы
        let googleMetaInformationTitle = document.getElementById("google-meta-information-title");
        let googleMetaInformation = document.getElementById("google-meta-information");
        // Слои с подробной информацией yandex-таблицы
        let yandexMetaInformationTitle = document.getElementById("yandex-meta-information-title");
        let yandexMetaInformation = document.getElementById("yandex-meta-information");

        // Обработка нажатия кнопки проверки
        $("#checking-button").click(function(e) {
            // Отмена поведения кнопки по умолчанию (submit)
            e.preventDefault();
            // Форма с полями
            let form = $("#cloud-drive-form");
            // Скрытие слоев с подробной информацией о файлах таблиц
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
                            // Активация слоев с сообщениями
                            checkingSuccessMessage.style.display = "block";
                            checkingErrorMessage.style.display = "none";
                            // Активация слоев с подробной информацией о файле с yandex-диска
                            googleMetaInformationTitle.style.display = "block";
                            googleMetaInformation.style.display = "block";
                            // Формирование текста подробной информации о файле с yandex-диска
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
                            // Активация слоев с подробной информацией о файле с yandex-диска
                            yandexMetaInformationTitle.style.display = "block";
                            yandexMetaInformation.style.display = "block";
                        } else {
                            // Активация слоев с сообщениями
                            checkingSuccessMessage.style.display = "none";
                            checkingErrorMessage.style.display = "block";
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
    });
</script>

<div class="row">
    <div class="col-md-12">

        <h2>Данные с облачных дисков:</h2>

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
            <strong>Ошибка!</strong> Проверка прошла с ошибкой, возможно Вы неверно указали ссылки на файлы электронных таблиц.
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

        <div class="form-group">
            <?= Button::widget([
                'label' => '<span class="glyphicon glyphicon-check"></span> Проверить',
                'encodeLabel' => false,
                'options' => [
                    'id' => 'checking-button',
                    'class' => 'btn btn-primary',
                    'style' => 'margin:5px'
                ]
            ]); ?>
            <?= Html::submitButton('<span class="glyphicon glyphicon-refresh"></span> Синхронизировать с Яндекс-диском',
                ['class' => 'btn btn-success', 'name' => 'synchronization-button']); ?>
        </div>

        <?php ActiveForm::end(); ?>
    </div>
</div>

<h3 id="google-meta-information-title" style="display: none">Подробная информация о таблице с Google Sheet:</h3>
<div id="google-meta-information" class="well" style="display: none"></div>

<h3 id="yandex-meta-information-title" style="display: none">Подробная информация о таблице с Yandex-диска:</h3>
<div id="yandex-meta-information" class="well" style="display: none"></div>