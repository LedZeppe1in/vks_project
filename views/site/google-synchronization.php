<?php

/* @var $this yii\web\View */
/* @var $dataProvider app\controllers\SiteController */

$this->title = 'Система управления заявками и информирования';
$this->params['breadcrumbs'][] = $this->title;

use yii\helpers\Html;
use yii\grid\GridView;
use app\components\YandexSpreadsheet;
?>

<div class="google-synchronization">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="body-content">
        <h3>Обновленные строки в Google-таблице:</h3>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'label' => '№',
                    'attribute' => '8',
                ],
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
                [
                    'label' => YandexSpreadsheet::EMPLOYEE_ID_HEADING,
                    'attribute' => '6',
                ],
                [
                    'label' => YandexSpreadsheet::SURNAME_HEADING,
                    'attribute' => '7',
                ],
            ],
        ]); ?>
    </div>
</div>