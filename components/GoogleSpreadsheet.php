<?php

namespace app\components;

/**
 * GoogleSpreadsheet - класс для представления сущностей электронной таблицы из Google Sheet.
 *
 * @package app\components
 */
class GoogleSpreadsheet
{
    // Названия вкладок таблицы
    const REQUESTS_SHEET  = 'Заявки';
    const CHECKING_SHEET  = 'Сверка';
    const EMPLOYEES_SHEET = 'Сотрудники';

    // Названия заголовков таблицы
    const CONTRACTOR_HEADING               = 'Контрагент';
    const DATE_HEADING                     = 'Дата выхода';
    const NUMBER_HEADING                   = '№ТТ';
    const ADDRESS_HEADING                  = 'Адрес';
    const DEPARTMENT_HEAD_CONTACT_HEADING  = 'Контакты руководителя подразделения';
    const WORK_TYPE_HEADING                = 'Вид работ';
    const DEPARTMENT_HEADING               = 'Отдел';
    const START_TIME_HEADING               = 'Время начала';
    const END_TIME_HEADING                 = 'Время окончания';
    const TOTAL_HOURS_HEADING              = 'Всего часов (без учета обеда)'; // TODO - неверное название заголовка!
    const EMPLOYEE_ID_HEADING              = 'табельный номер';
    const REQUEST_ADJUSTMENTS_HEADING      = 'Корректировки заявок';
}