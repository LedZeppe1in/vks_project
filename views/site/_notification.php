<?php

/* @var $notificationModel app\models\NotificationForm */
/* @var $employees app\controllers\SiteController */

use yii\grid\GridView;
use yii\helpers\Html;
use yii\bootstrap\ActiveForm;
use app\components\GoogleSpreadsheet;
use yii\widgets\Pjax;

?>

<div class="row">
    <div class="col-md-12">

        <h2>Информирование</h2>

        <h3>Оповещение сотрудников:</h3>

        <?php $form = ActiveForm::begin([
            'id' => 'notification-form',
            'enableClientValidation' => true,
        ]); ?>

            <?= $form->errorSummary($notificationModel); ?>

            <?= $form->field($notificationModel, 'messageTemplate')->textarea(['rows' => 8, 'value' => 'Добрый день. Ваша смена: _____ (дата; время), по адресу: ______ адрес торговой точки (ТТ), специальность: ____ (грузчик, РТЗ, гастрономист, кассир, продавец-универсал).']) ?>

            <div class="form-group">
                <?= Html::submitButton('<span class="glyphicon glyphicon-bell"></span> Оповестить',
                    ['class' => 'btn btn-success', 'name' => 'notification-button']); ?>
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