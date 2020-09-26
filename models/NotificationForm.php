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
    const MESSAGE_TEMPLATE_FILE_NAME = 'message-template.txt'; // Название файла для хранения текста шаблона сообщения

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
            'messageTemplate' => 'Шаблон текста сообщения',
        ];
    }
}