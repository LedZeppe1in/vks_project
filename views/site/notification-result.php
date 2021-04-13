<?php

/* @var $this yii\web\View */
/* @var $dataProvider app\controllers\SiteController */
/* @var $deliveredMessageNumber app\controllers\SiteController */
/* @var $sentMessageNumber app\controllers\SiteController */
/* @var $queueMessageNumber app\controllers\SiteController */
/* @var $rejectedMessageNumber app\controllers\SiteController */
/* @var $expiredMessageNumber app\controllers\SiteController */

$this->title = 'Результаты информирования';

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\NotificationResultForm;
?>

<div class="notification-result">

    <h1><?= Html::encode($this->title) ?></h1>

    <h3>Статистика сообщений:</h3>

    <div class="well">
        <div class="row">
            <div class="col-sm-2" style="color: green">Доставлено: <?= $deliveredMessageNumber ?></div>
            <div class="col-sm-2" style="color: blue">Отправлено: <?= $sentMessageNumber ?></div>
            <div class="col-sm-2" style="color: darkgoldenrod">В очереди: <?= $queueMessageNumber ?></div>
            <div class="col-sm-2" style="color: red">Отклонено: <?= $rejectedMessageNumber ?></div>
            <div class="col-sm-2" style="color: darkmagenta">Просрочено: <?= $expiredMessageNumber ?></div>
        </div>
    </div>

    <h3>Результат:</h3>

    <div class="body-content">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'label' => 'Номер телефона',
                    'attribute' => '0',
                ],
                [
                    'label' => 'Текст сообщения',
                    'attribute' => '1',
                ],
                [
                    'label' => 'Дата отправки',
                    'attribute' => '2',
                ],
                [
                    'label' => 'Статус',
                    'attribute' => '3',
                    'format' => 'raw',
                    'value' => function($data) {
                        return (isset($data['3'])) ? NotificationResultForm::getStatusName($data['3']) : '';
                    },
                ],
            ],
        ]); ?>
    </div>
</div>