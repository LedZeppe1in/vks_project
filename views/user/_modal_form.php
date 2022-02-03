<?php

/* @var $model app\models\User */

use yii\helpers\Html;
use yii\bootstrap\Modal;
use yii\bootstrap\Button;
use yii\widgets\ActiveForm;

Modal::begin([
    'id' => 'removeUserModalForm',
    'header' => '<h3>Удаление пользователя</h3>',
]); ?>

    <div class="modal-body">
        <p style="font-size: 14px">Вы уверены, что хотите удалить этого пользователя?</p>
    </div>

    <?php $form = ActiveForm::begin([
        'id' => 'delete-user-form',
        'method' => 'post',
        'action' => ['delete', 'id' => $model->id],
        'enableAjaxValidation' => true,
        'enableClientValidation' => true,
    ]); ?>

        <?= Html::submitButton('<span class="glyphicon glyphicon-ok"></span> Удалить',
            ['class' => 'btn btn-success']) ?>

        <?= Button::widget([
            'label' => '<span class="glyphicon glyphicon-remove"></span> Отмена',
            'encodeLabel' => false,
            'options' => [
                'class' => 'btn-danger',
                'style' => 'margin:5px',
                'data-dismiss' => 'modal'
            ]
        ]); ?>

    <?php ActiveForm::end(); ?>

<?php Modal::end(); ?>