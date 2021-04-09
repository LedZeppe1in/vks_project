<?php

/* @var $addedRows app\controllers\SiteController */

use yii\grid\GridView;
use app\components\YandexSpreadsheet;
?>

<div class="row">
    <div class="col-md-12">

        <h2>Добавленные новые строки</h2>

        <?= GridView::widget([
            'dataProvider' => $addedRows,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],
                [
                    'label' => YandexSpreadsheet::DATE_HEADING,
                    'attribute' => '0',
                ],
                [
                    'label' => YandexSpreadsheet::ADDRESS_HEADING,
                    'attribute' => '1',
                ],
                [
                    'label' => YandexSpreadsheet::WORK_TYPE_HEADING,
                    'attribute' => '2',
                ],
                [
                    'label' => YandexSpreadsheet::START_TIME_HEADING,
                    'attribute' => '3',
                ],
                [
                    'label' => YandexSpreadsheet::END_TIME_HEADING,
                    'attribute' => '4',
                ],
                [
                    'label' => YandexSpreadsheet::TOTAL_HOURS_HEADING,
                    'attribute' => '5',
                ],
            ],
        ]); ?>
    </div>
</div>