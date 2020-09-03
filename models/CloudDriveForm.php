<?php

namespace app\models;

use yii\base\Model;

class CloudDriveForm extends Model
{
    // Ссылка на файл таблицы на Google Sheets
    const GOOGLE_FILE_LINK     = 'https://drive.google.com/file/d/1IW3b0wT03R8bnojqI6GnyZo2uKVsYvBy/view?usp=sharing';
    // Данные для подключения к аккаунту Google
    const GOOGLE_LOGIN         = 'centrasib@gmail.com';
    const GOOGLE_PASSWORD      = 'cnhjxrf1';

    // Ссылка на файл таблицы на Yandex-диске
    const YANDEX_FILE_LINK     = 'https://yadi.sk/i/0a9QyHFWieG4Og';
    // Данные для подключения к аккаунту Yandex
    const YANDEX_LOGIN         = 'info@centrasib.ru';
    const YANDEX_PASSWORD      = 'cnhjxrf1';

    public $googleFileLink; // Ссылка на файл Google-таблицы
    public $yandexFileLink; // Ссылка на файл Yandex-таблицы

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['googleFileLink', 'yandexFileLink'], 'required'],
            [['googleFileLink', 'yandexFileLink'], 'string'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'googleFileLink' => 'Ссылка на файл таблицы на Google Sheets',
            'yandexFileLink' => 'Ссылка на файл таблицы на Yandex-диске',
        ];
    }
}