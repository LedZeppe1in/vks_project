<?php

namespace app\models;

use yii\base\Model;

class NotificationForm extends Model
{
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