<?php

/* @var $cloudDriveModel app\models\CloudDriveForm */

use yii\helpers\Html;
use yii\bootstrap\Button;
use kartik\date\DatePicker;
use kartik\form\ActiveForm;
?>

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

        <div id="save-paths-success-message" class="alert alert-success alert-dismissible" role="alert" style="display: none">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
            <strong>Пути сохранены!</strong> Вы успешно сохранили пути к файлам электронных таблиц.
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

            <?= $form->field($cloudDriveModel, 'googleFileLink')->textInput() ?>

            <?= $form->field($cloudDriveModel, 'yandexFilePath')->textInput() ?>

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
                <?= Button::widget([
                    'label' => '<span class="glyphicon glyphicon-floppy-save"></span> Запомнить пути',
                    'encodeLabel' => false,
                    'options' => [
                        'id' => 'save-paths-button',
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
                        'class' => 'btn btn-success'
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