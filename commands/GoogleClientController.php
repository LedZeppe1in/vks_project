<?php

namespace app\commands;

use Yii;
use yii\helpers\Console;
use yii\console\Controller;
use app\components\GoogleSpreadsheet;

/**
 * GoogleClientController реализует консольную команду для работы с Google Drive API (получение токена).
 */
class GoogleClientController extends Controller
{
    /**
     * Инициализация команд.
     */
    public function actionIndex()
    {
        echo 'yii google-client/get-token' . PHP_EOL;
    }

    /**
     * Команда получения токена.
     */
    public function actionGetToken()
    {
        // Пусть к файлу токена
        $oauthPath = Yii::$app->basePath . '/web/google-oauth/';
        // Инициализация объекта Google-таблицы
        $googleSpreadsheet = new GoogleSpreadsheet();
        // Получение и сохранение токена в json-файл
        $googleSpreadsheet->getToken($oauthPath);
    }

    /**
     * Вывод сообщений на экран (консоль)
     * @param bool $success
     */
    private function log($success)
    {
        if ($success) {
            $this->stdout('Success!', Console::FG_GREEN, Console::BOLD);
        } else {
            $this->stderr('Error!', Console::FG_RED, Console::BOLD);
        }
        echo PHP_EOL;
    }
}