<?php

namespace app\commands;

use yii\helpers\Console;
use yii\console\Controller;
use app\models\User;

/**
 * UserController - реализует консольные команды для работы с пользователями.
 * @package app\commands
 */
class UserController extends Controller
{
    /**
     * Инициализация команд.
     */
    public function actionIndex()
    {
        echo 'yii user/create-default-user' . PHP_EOL;
    }

    /**
     * Команда создания пользователя (администратора) по умолчанию.
     */
    public function actionCreateDefaultUser()
    {
        // Создание пользователя администратора в БД
        $model = new User();
        $model->username = 'admin';
        $model->setPassword('admin');
        $model->role = User::ROLE_ADMINISTRATOR;
        $model->status = User::STATUS_ACTIVE;
        $this->log($model->save());
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