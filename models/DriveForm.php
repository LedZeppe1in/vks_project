<?php

namespace app\models;

use yii\base\Model;

class DriveForm extends Model
{
    public $link;
    public $username;
    public $password;

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['link'], 'required'],
            ['link', 'string'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'link' => 'Ссылка на файл',
            'username' => 'Логин',
            'password' => 'Пароль',
        ];
    }
}