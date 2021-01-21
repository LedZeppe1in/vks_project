<?php

/* @var $this yii\web\View */
/* @var $deletedRows app\controllers\SiteController */
/* @var $addedRows app\controllers\SiteController */
/* @var $foo app\controllers\SiteController */

$this->title = 'Система управления заявками и информирования';

use yii\helpers\Html;
use yii\grid\GridView;
use app\components\YandexSpreadsheet;
?>

<div class="yandex-synchronization">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="body-content">

        <?php
        echo '<pre>';
        print_r($foo);
        echo '</pre>'; ?>

        <h3>Удаленные строки:</h3>
        <?= GridView::widget([
            'dataProvider' => $deletedRows,
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
            ],
        ]); ?>
        <h3>Добавленные строки:</h3>
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