<?php

namespace app\components;

/**
 * YandexSpreadsheet - класс для представления сущностей электронной таблицы из Yandex-диска.
 *
 * @package app\components
 */
class YandexSpreadsheet
{
    // Название вкладки таблицы
    const FIRST_SHEET_SHEET  = 'Лист1';

    // Названия заголовков таблицы
    const DATE_HEADING        = 'Дата';
    const ADDRESS_HEADING     = 'Адрес';
    const WORK_TYPE_HEADING   = 'Вид работ';
    const START_TIME_HEADING  = 'Начало';
    const END_TIME_HEADING    = 'Окончание';
    const TOTAL_HOURS_HEADING = 'Часы';
    const EMPLOYEE_ID_HEADING = 'ТБ';
    const SURNAME_HEADING     = 'Фамилия';
    const COMMENT_HEADING     = 'Комментарий';
}