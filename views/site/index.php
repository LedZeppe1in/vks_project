<?php

/* @var $this yii\web\View */
/* @var $cloudDriveModel app\models\CloudDriveForm */
/* @var $notificationModel app\models\NotificationForm */
/* @var $employees app\models\NotificationForm */

$this->title = 'Система управления заявками и информирования';

use yii\helpers\Html;
use yii\bootstrap\Tabs;
?>

<div class="site-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="body-content">
        <?php echo Tabs::widget([
            'items' => [
                [
                    'label' => 'Данные с облачных дисков',
                    'content' => $this->render('_cloud_drive', [
                        'cloudDriveModel' => $cloudDriveModel
                    ]),
                ],
                [
                    'label' => 'Информирование',
                    'content' => $this->render('_notification', [
                        'notificationModel' => $notificationModel,
                        'employees' => $employees,
                    ]),
                    'headerOptions' => [
                        'id' => 'information-tab',
                        'class' => 'disabled'
                    ],
                    'linkOptions' => [
                        'id' => 'information-tab-link',
                        'data-toggle' => ''
                    ]
                ]
            ]
        ]); ?>
    </div>

</div>