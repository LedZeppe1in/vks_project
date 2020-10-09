<?php

/* @var $this \yii\web\View */
/* @var $content string */

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
        'brandUrl' => Yii::$app->user->isGuest ? '/site/login' : Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-left'],
        'encodeLabels' => false,
        'items' => [
            Yii::$app->user->isGuest ? '' : [
                'label' => '<span class="glyphicon glyphicon-ok-circle"></span> Проверка статусов сообщений',
                'url' => ['/site/check-message-status']
            ],
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            Yii::$app->user->isGuest ? '' : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    '<span class="glyphicon glyphicon-log-out"></span> ' . 'Выход (' .
                    Yii::$app->user->identity->username . ')', ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            )
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
        <p class="pull-left"> &copy; <?= date('Y') ?> ООО "ВКС"</p>
        <p class="pull-right">Разработано ООО "ЦентраСиб"</p>
    </div>
</footer>

<?php $this->endBody() ?>

<div id ="overlay"></div><!-- div for js spinner -->
<div id ="center"></div><!-- div for js spinner -->

</body>
</html>
<?php $this->endPage() ?>