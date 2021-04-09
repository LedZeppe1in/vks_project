<?php

namespace app\models;

use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Class NotificationResultForm - класс для определения формы запроса статусов сообщений на определенный период.
 *
 * @package app\models
 */
class NotificationResultForm extends Model
{
    // Коды статусов сообщений
    const ERROR_STATUS     = 0; // Ошибка
    const DELIVERED_STATUS = 1; // Доставлено
    const SENT_STATUS      = 2; // Отправлено
    const QUEUE_STATUS     = 3; // В очереди
    const UNKNOWN_STATUS   = 4; // Неизвестен
    const REJECTED_STATUS  = 5; // Отклонено
    const EXPIRED_STATUS   = 6; // Просрочено

    public $fromDateTime; // Дата и время начала для выборки
    public $toDateTime;   // Дата и время окончания для выборки

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['fromDateTime', 'toDateTime'], 'required'],
            [['fromDateTime', 'toDateTime'], 'date', 'format' => 'php:d.m.Y H:i'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'fromDateTime' => 'Дата и время начала',
            'toDateTime' => 'Дата и время окончания',
        ];
    }

    /**
     * Получение списка всех возможных статусов сообщений.
     *
     * @return array - массив всех возможных статусов сообщений
     */
    public static function getAllStatuses()
    {
        return [
            self::ERROR_STATUS => 'Ошибка',
            self::DELIVERED_STATUS => 'Доставлено',
            self::SENT_STATUS => 'Отправлено',
            self::QUEUE_STATUS => 'В очереди',
            self::UNKNOWN_STATUS => 'Неизвестен',
            self::REJECTED_STATUS => 'Отклонено',
            self::EXPIRED_STATUS => 'Просрочено',
        ];
    }

    /**
     * Получение значения статуса сообщения по его коду.
     *
     * @param $statusCode - код статуса сообщения
     * @return mixed - текстовое значение статуса сообщения
     * @throws \Exception
     */
    public static function getStatusName($statusCode)
    {
        return ArrayHelper::getValue(self::getAllStatuses(), $statusCode);
    }
}