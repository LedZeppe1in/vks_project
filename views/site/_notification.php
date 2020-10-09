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
            <strong>Оповещение прошло успешно!</strong> Вы успешно оповестили всех сотрудников из списка.
        </div>

        <span><b>Текущий баланс: </b></span>
        <span id="current-balance" class="badge" style="margin-bottom: 2px;"></span><br /><br />
        <span><b>Объём рассылки: </b></span>
        <span id="mailing-volume" class="badge" style="margin-bottom: 2px;"></span>

        <h3>Оповещение сотрудников:</h3>

        <?php $form = ActiveForm::begin([
            'id' => 'notification-form',
            'enableClientValidation' => true,
        ]); ?>

            <?= $form->errorSummary($notificationModel); ?>

            <?= $form->field($notificationModel, 'messageTemplate')->textarea(['rows' => 8]) ?>

            <div class="form-group">
                <?= Button::widget([
                    'label' => '<span class="glyphicon glyphicon-bell"></span> Оповестить',
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