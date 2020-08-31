<?php

/* @var $this yii\web\View */
/* @var $googleDriveModel app\models\DriveForm */
/* @var $yandexDriveModel app\models\DriveForm */

$this->title = 'Система управления заявками и информирования';

use yii\bootstrap\Tabs;
use yii\helpers\Html;
?>

<div class="site-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="body-content">
        <?php echo Tabs::widget([
            'items' => [
                [
                    'label' => 'Google-диск',
                    'content' => $this->render('_google_drive', [
                        'googleDriveModel' => $googleDriveModel
                    ]),
                ],
                [
                    'label' => 'Yandex-диск',
                    'content' => $this->render('_yandex_drive', [
                        'yandexDriveModel' => $yandexDriveModel
                    ]),
                ],
                [
                    'label' => 'Информирование',
                    'content' => $this->render('_information'),
                ]
            ]
        ]); ?>
    </div>
</div>