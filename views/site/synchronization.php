<?php

/* @var $this yii\web\View */
/* @var $res app\controllers\SiteController */

$this->title = 'Система управления заявками и информирования';

use yii\helpers\Html;
?>

<div class="site-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="body-content">
        <?php
        echo '<pre>';
        print_r($res);
        echo '</pre>';
        ?>
    </div>
</div>