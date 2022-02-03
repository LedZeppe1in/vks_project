<?php

namespace app\models;

use yii\base\Model;

/**
 * Class NotificationForm - класс для определения формы оповещения сотрудников.
 *
 * @package app\models
 */
class NotificationForm extends Model
{
    // Ссылка отправки сообщений
    const SEND_SMS_LINK             = 'http://centrasib.ru/program/smso/1.00/usr_send_sms.php';
    // Ссылка проверки баланса
    const CHECK_BALANCE_LINK        = 'http://centrasib.ru/program/smso/1.00/usr_chk_balance.php';
    // Ссылка запроса статусов сообщений за определенный период
    const CHECK_MESSAGE_STATUS_LINK = 'http://centrasib.ru/program/smso/1.00/usr_get_msg_stat_for_period.php';

    const LOGIN          = 'vks';         // Имя пользователя
    const PASSWORD       = 'vks';         // Пароль
    const SENDING_STATUS = 0;             // Статус отправки: 0 - отправить сейчас, 1 - отправить позже
    const SIGN           = 'VKS company'; // Подпись

    const DATETIME_MARKER  = '<ДАТА; ВРЕМЯ>';          // Маркер даты и времени
    const ADDRESS_MARKER   = '<АДРЕС ТОРГОВОЙ ТОЧКИ>'; // Маркер адреса
    const WORK_TYPE_MARKER = '<ВИД РАБОТ>';            // Маркер вида работы (специальности)

    // Название файла для хранения текста шаблона сообщения
    const MESSAGE_TEMPLATE_FILE_NAME = 'message-template.txt';

    // Название файла для хранения текста шаблона общего сообщения сотрудникам
    const GENERAL_MESSAGE_TEMPLATE_FILE_NAME = 'general-message-template.txt';

    public $messageTemplate; // Шаблон текста сообщения

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['messageTemplate'], 'required'],
            [['messageTemplate'], 'string'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'messageTemplate' => 'Шаблон текста сообщения (возможные подстановки: <ДАТА; ВРЕМЯ>, <АДРЕС ТОРГОВОЙ ТОЧКИ>, <ВИД РАБОТ>)',
        ];
    }
}