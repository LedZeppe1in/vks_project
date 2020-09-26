<?php

namespace app\models;

use DateTime;
use DatePeriod;
use DateInterval;
use yii\base\Model;

/**
 * Class CloudDriveForm - класс для определения формы взаимодействия с облачными дисками Google и Yandex.
 *
 * @package app\models
 */
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
    public $fromDate;       // Дата начала для выборки
    public $toDate;         // Дата окончания для выборки

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['googleFileLink', 'yandexFilePath'], 'required'],
            [['googleFileLink', 'yandexFilePath'], 'string'],
            [['fromDate', 'toDate'], 'date', 'format' => 'php:d.m.Y'],
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
            'fromDate' => 'Дата начала',
            'toDate' => 'Дата окончания',
        ];
    }

    /**
     * Формирование массива диапазона дат для выборки строк электронных таблиц.
     *
     * @return array - массив дат для выборки строк
     * @throws \Exception
     */
    public function getDates()
    {
        $dates = array();
        if ($this->fromDate != null && $this->toDate != null)
            if ($this->fromDate == $this->toDate) {
                $date = new DateTime($this->fromDate);
                array_push($dates, $date);
            } else {
                $start = new DateTime($this->fromDate);
                $interval = new DateInterval('P1D');
                $end = new DateTime($this->toDate);
                $end->setTime(0,0,1);
                $period = new DatePeriod($start, $interval, $end);
                foreach ($period as $date)
                    array_push($dates, $date);
            }

        return $dates;
    }
}