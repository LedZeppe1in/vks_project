<?php

/* @var $this yii\web\View */
/* @var $dataProvider app\controllers\SiteController */

$this->title = 'Результаты информирования';

use yii\helpers\Html;
use yii\grid\GridView;
use app\models\NotificationResultForm;
?>

<div class="notification-result">

    <h1><?= Html::encode($this->title) ?></h1>

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