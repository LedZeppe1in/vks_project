<?php

/* @var $this yii\web\View */
/* @var $dataProvider app\controllers\SiteController */
/* @var $deliveredMessageNumber app\controllers\SiteController */
/* @var $sentAndQueueMessageNumber app\controllers\SiteController */
/* @var $allRejectedMessageNumber app\controllers\SiteController */
/* @var $deliveredMessageNumberPerWeek app\controllers\SiteController */
/* @var $rejectedMessageNumberPerWeek app\controllers\SiteController */
/* @var $weekDays app\controllers\SiteController */

$this->title = 'Результаты информирования';

use yii\helpers\Html;
use yii\grid\GridView;
use dosamigos\chartjs\ChartJs;
use app\models\NotificationResultForm;
?>

<div class="notification-result">

    <h1><?= Html::encode($this->title) ?></h1>

    <h3>Статистика сообщений:</h3>

    <div class="well">
        <div class="row">
            <div class="col-sm-2" style="color: green">Доставлено: <?= $deliveredMessageNumber ?></div>
            <div class="col-sm-3" style="color: blue">Отправлено / В очереди: <?= $sentAndQueueMessageNumber ?></div>
            <div class="col-sm-2" style="color: red">Не доставлено: <?= $allRejectedMessageNumber ?></div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-6">
            <?= ChartJs::widget([
                'type' => 'pie',
                'data' => [
                    'labels' => ['Доставлено', 'Отправлено / В очереди', 'Не доставлено'],
                    'datasets' => [
                        [
                            'label' => 'Статистика',
                            'backgroundColor' => [
                                'rgba(0, 225, 0, 0.4)',
                                'rgba(0, 0, 225, 0.4)',
                                'rgba(255, 0, 0, 0.4)',
                            ],
                            'borderColor' => 'rgba(179, 181, 198, 1)',
                            'pointBackgroundColor' => 'rgba(179, 181, 198, 1)',
                            'pointBorderColor' => '#fff',
                            'pointHoverBackgroundColor' => '#fff',
                            'pointHoverBorderColor' => 'rgba(179, 181, 198, 1)',
                            'data' => [
                                $deliveredMessageNumber,
                                $sentAndQueueMessageNumber,
                                $allRejectedMessageNumber
                            ]
                        ]
                    ]
                ]
            ]); ?>
        </div>
        <div class="col-sm-6">
            <?= ChartJs::widget([
                'type' => 'line',
                'data' => [
                    'labels' => $weekDays,
                    'datasets' => [
                        [
                            'label' => 'Доставлено',
                            'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                            'borderColor' => 'rgba(75, 192, 192, 1)',
                            'pointBackgroundColor' => 'rgba(75, 192, 192, 1)',
                            'pointBorderColor' => '#fff',
                            'pointHoverBackgroundColor' => '#fff',
                            'pointHoverBorderColor' => 'rgba(75, 192, 192, 1)',
                            'data' => $deliveredMessageNumberPerWeek
                        ],
                        [
                            'label' => 'Не доставлено',
                            'backgroundColor' => 'rgba(255, 99, 132, 0.2)',
                            'borderColor' => 'rgba(255, 99, 132, 1)',
                            'pointBackgroundColor' => 'rgba(255, 99, 132, 1)',
                            'pointBorderColor' => '#fff',
                            'pointHoverBackgroundColor' => '#fff',
                            'pointHoverBorderColor' => 'rgba(255, 99, 132, 1)',
                            'data' => $rejectedMessageNumberPerWeek
                        ]
                    ]
                ]
            ]); ?>
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
                    'label' => 'ФИО',
                    'attribute' => '1',
                ],
                [
                    'label' => 'Текст сообщения',
                    'attribute' => '2',
                ],
                [
                    'label' => 'Дата отправки',
                    'attribute' => '3',
                ],
                [
                    'label' => 'Статус',
                    'attribute' => '4',
                    'format' => 'raw',
                    'value' => function($data) {
                        return (isset($data['4'])) ? NotificationResultForm::getStatusName($data['4']) : '';
                    },
                ],
            ],
        ]); ?>
    </div>
</div>