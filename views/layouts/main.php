<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\models\User;
use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
<?php $this->beginBody() ?>

<!-- Подключение js-скриптов для индикатора прогресса -->
<?php $this->registerJsFile('/js/spin.min.js', ['position' => yii\web\View::POS_HEAD]) ?>
<?php $this->registerJsFile('/js/spinner-options.js', ['position' => yii\web\View::POS_HEAD]) ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-left'],
        'encodeLabels' => false,
        'items' => [
            (Yii::$app->user->isGuest || Yii::$app->user->identity->status == User::STATUS_INACTIVE) ? '' : [
                'label' => '<span class="glyphicon glyphicon-refresh"></span> Синхронизация данных',
                'url' => ['/site/data-synchronization']
            ],
            (Yii::$app->user->isGuest || Yii::$app->user->identity->status == User::STATUS_INACTIVE) ? '' : [
                'label' => '<span class="glyphicon glyphicon-envelope"></span> СМС-Органайзер',
                'url' => '#',
                'items' => [
                    [
                        'label' => 'Общее информирование',
                        'url' => ['/site/general-information']
                    ],
                    [
                        'label' => 'Проверка статусов сообщений',
                        'url' => ['/site/check-message-status']
                    ],
                    [
                        'label' => 'Запрос счета',
                        'url' => ['/site/balance-replenishment']
                    ]
                ]
            ],
            (!Yii::$app->user->isGuest && Yii::$app->user->identity->role == User::ROLE_ADMINISTRATOR &&
                Yii::$app->user->identity->status == User::STATUS_ACTIVE) ?
                [
                    'label' => '<span class="glyphicon glyphicon-user"></span> Пользователи',
                    'url' => ['/user/list']
                ] : '',
            [
                'label' => '<span class="glyphicon glyphicon-bullhorn"></span> Политика конфиденциальности',
                'url' => ['/site/privacy-policy']
            ],
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'encodeLabels' => false,
        'items' => [
            Yii::$app->user->isGuest ?
                [
                    'label' => '<span class="glyphicon glyphicon-log-in"></span> Вход',
                    'url' => ['/site/sing-in']
                ] : [
                    'label' => '<span class="glyphicon glyphicon-home"></span> Аккаунт',
                    'url' => ['#'], 'items' => [
                        ['label' => 'Добро пожаловать,'],
                        ['label' => '<b style="font-size:small">' . Yii::$app->user->identity->username . '</b>'],
                        '<li class="divider"></li>',
                        ['label' => '<span class="glyphicon glyphicon-bookmark"></span> Профиль',
                            'url' => '/user/profile/' . Yii::$app->user->identity->getId()],
                        ['label' => '<span class="glyphicon glyphicon-log-out"></span> Выход',
                            'url' => ['/site/sing-out'], 'linkOptions' => ['data-method' => 'post']]
                    ],
                ],
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <div class="row footer-information">
            <div class="col-sm-6">
                &copy; <?= date('Y') ?> <?= Html::a('ООО «ВКС»', 'http://koopwork.ru/o-kompanii/') ?>
                , 105425, г. Москва, ул. 3-я Парковая, 41а.
                <div>тел.: <a href="tel:8 (495) 740-98-28">8 (495) 740-98-28</a>, <a href="tel:8 (800) 444-41-02">8 (800) 444-41-02</a><br /></div>
                <a href="mailto:info@koopwork.ru">info@koopwork.ru</a><br />
            </div>
            <div class="col-sm-6" style="float: right; text-align: right">
                Разработано <?= Html::a('ООО "ЦентраСиб"', 'http://centrasib.ru') ?>
            </div>
        </div><br />
    </div>
</footer>

<?php $this->endBody() ?>

<div id ="overlay"></div><!-- div for js spinner -->
<div id ="center"></div><!-- div for js spinner -->

</body>
</html>
<?php $this->endPage() ?>