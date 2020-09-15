<?php

namespace app\models;

use yii\base\Model;

class CloudDriveForm extends Model
{
    // Ссылка на файл электронной таблицы на Google Sheets
    const GOOGLE_FILE_LINK = 'https://drive.google.com/file/d/1yXDjKGhsQj69q1xi4LYBXCCZCw8ktDre/view?usp=sharing';
    // Данные для подключения к аккаунту Google
    const GOOGLE_LOGIN     = 'centrasib@gmail.com';
    const GOOGLE_PASSWORD  = 'cnhjxrf1';

    // Путь к файлу электронной таблицы на Yandex-диске
    const YANDEX_FILE_PATH = '/ВКС/yandex-test-spreadsheet.xlsx';
    // Данные для подключения к аккаунту Yandex
    const YANDEX_LOGIN     = 'info@centrasib.ru';
    const YANDEX_PASSWORD  = 'cnhjxrf1';

    public $googleFileLink; // Ссылка на файл Google-таблицы
    public $yandexFilePath; // Путь к файлу Yandex-таблицы

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['googleFileLink', 'yandexFilePath'], 'required'],
            [['googleFileLink', 'yandexFilePath'], 'string'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'googleFileLink' => 'Ссылка на файл электронной таблицы на Google Sheets',
            'yandexFilePath' => 'Путь к файлу электронной таблице на Yandex-диске',
        ];
    }
}