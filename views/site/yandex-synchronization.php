<?php

/* @var $this yii\web\View */
/* @var $deletedRows app\controllers\SiteController */
/* @var $addedRows app\controllers\SiteController */

$this->title = 'Система управления заявками и информирования';
$this->params['breadcrumbs'][] = $this->title;

use yii\helpers\Html;
use yii\bootstrap\Tabs;
?>

<div class="yandex-synchronization">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="body-content">
        <?php echo Tabs::widget([
            'items' => [
                [
                    'label' => 'Добавленные новые строки',
                    'content' => $this->render('_added_rows', [
                        'addedRows' => $addedRows,
                    ]),
                ],
                [
                    'label' => 'Строки отмеченные как удаленные',
                    'content' => $this->render('_deleted_rows', [
                        'deletedRows' => $deletedRows
                    ]),
                ],
            ]
        ]); ?>
    </div>
</div>