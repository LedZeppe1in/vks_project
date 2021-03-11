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

<div class="row">
    <div class="col-md-12">

        <h2>Информирование</h2>

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

        <span><b>Текущий баланс: </b></span>
        <span id="current-balance" class="badge" style="margin-bottom: 2px;"></span>
            <?= Html::a('Запрос счета на пополнение баланса', ['/site/balance-replenishment']); ?><br /><br />
        <span><b>Общий объём рассылки для всех сотрудников: </b></span>
        <span id="full-mailing-volume" class="badge" style="margin-bottom: 2px;"></span><br /><br />
        <span><b>Объём рассылки для выбранных сотрудников: </b></span>
        <span id="custom-mailing-volume" class="badge" style="margin-bottom: 2px;">0</span>

        <h3>Оповещение сотрудников:</h3>

        <?php $form = ActiveForm::begin([
            'id' => 'notification-form',
            'enableClientValidation' => true,
        ]); ?>

            <?= $form->errorSummary($notificationModel); ?>

            <?= $form->field($notificationModel, 'messageTemplate')->textarea(['rows' => 8]) ?>

            <div class="form-group">
                <?= Button::widget([
                    'label' => '<span class="glyphicon glyphicon-bell"></span> Оповестить выбранных сотрудников',
                    'encodeLabel' => false,
                    'options' => [
                        'id' => 'notification-button',
                        'class' => 'btn btn-success'
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
                    [
                        'label' => 'Статус',
                    ],
                ],
            ]); ?>

            <?= Html::beginForm(['data-synchronization'], 'post',
                ['id' => 'pjax-form', 'data-pjax' => '', 'style' => 'display:none']); ?>
                <?= Html::hiddenInput('google-file-link', '', ['id' => 'pjax-google-file-link-input']) ?>
                <?= Html::hiddenInput('from-date', '', ['id' => 'pjax-from-date-input']) ?>
                <?= Html::hiddenInput('to-date', '', ['id' => 'pjax-to-date-input']) ?>
                <?= Html::submitButton('Вычислить', ['id' => 'pjax-button', 'data-pjax' => '']) ?>
            <?= Html::endForm() ?>

        <?php Pjax::end(); ?>
    </div>
</div>