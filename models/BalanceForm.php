<?php

namespace app\models;

use yii\base\Model;

/**
 * Class BalanceForm - класс для определения формы запроса счета на пополнение баланса.
 *
 * @package app\models
 */
class BalanceForm extends Model
{
    // Ссылка на запрос счета на оплату (пополнение баланса)
    const INVOICE_REQUEST_LINK_FOR_PAYMENT = 'http://centrasib.ru/program/smso/1.00/usr_get_invoice.php';

    public $balance; // Запрашиваемый баланс
    public $email;   // Адрес электронной почты

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['balance', 'email'], 'required'],
            [['balance'], 'integer', 'min' => 1],
            [['email'], 'email'],
        ];
    }

    /**
     * @return array customized attribute labels
     */
    public function attributeLabels()
    {
        return [
            'balance' => 'Запрашиваемый баланс',
            'email' => 'Адрес электронной почты',
        ];
    }
}