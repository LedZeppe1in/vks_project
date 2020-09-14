<?php

namespace app\components;

use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

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

    public $fileName = 'google-spreadsheet.xlsx'; // Название файла с электронной таблицей на сервере

    /**
     * Копирование файла электронной таблицей с Yandex-диска на сервер.
     *
     * @param $fileLink - публичная ссылка на файл электронной таблицы на Yandex-диске
     * @param $path - путь сохранения файла электронной таблицы на сервере
     * @return bool - успешность копирования
     */
    public function copySpreadsheetToServer($fileLink, $path)
    {
        // Формирование URL для скачивания файла таблицы с Google Doc
        $urlGoogleDisk = 'https://docs.google.com/spreadsheets/d/1yXDjKGhsQj69q1xi4LYBXCCZCw8ktDre/export?format=xlsx&id=1yXDjKGhsQj69q1xi4LYBXCCZCw8ktDre';
        // Получение содержимого Google-таблицы
        file_put_contents($path . $this->fileName, file_get_contents($urlGoogleDisk));

        return true;
    }

    /**
     * Синхронизация с Yandex (поиск всех строк из google-таблицы, которых нет в yandex-таблице).
     *
     * @param $yandexSpreadsheetRows - массив всех строк электронной таблицы Yandex
     * @param $path - путь к файлу электронной таблицы на сервере
     * @return array - массив строк, которых нет в электронной таблице Yandex
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public function synchronize($yandexSpreadsheetRows, $path)
    {
        $googleSpreadsheetRows = array();
        $reader = ReaderEntityFactory::createReaderFromFile($path . $this->fileName);
        $reader->open($path . $this->fileName);
        foreach ($reader->getSheetIterator() as $sheet)
            if ($sheet->getName() === GoogleSpreadsheet::REQUESTS_SHEET)
                foreach ($sheet->getRowIterator() as $rowNumber => $row)
                    if ($rowNumber > 1) {
                        $currentRow = array();
                        foreach ($row->getCells() as $cellNumber => $cell) {
                            // Добавление даты
                            if ($cellNumber == 1) {
                                $date = $cell->getValue();
                                array_push($currentRow, $date->format('Y-m-d'));
                            }
                            // Добавление адреса и вида работ
                            if ($cellNumber == 3 || $cellNumber == 5)
                                array_push($currentRow, $cell->getValue());
                            // Добавление времени начала и окончания
                            if ($cellNumber == 7 || $cellNumber == 8) {
                                $date = $cell->getValue();
                                array_push($currentRow, $date->format('H:i'));
                            }
                        }
                        // Проверка совпадания строки
                        $equality = false;
                        foreach ($yandexSpreadsheetRows as $yandexSpreadsheetRow)
                            if ($yandexSpreadsheetRow[0] === $currentRow[0] &&
                                $yandexSpreadsheetRow[1] === $currentRow[1] &&
                                $yandexSpreadsheetRow[2] === $currentRow[2] &&
                                $yandexSpreadsheetRow[3] === $currentRow[3] &&
                                $yandexSpreadsheetRow[4] === $currentRow[4])
                                $equality = true;
                        $googleSpreadsheetRow = array();
                        if (!$equality) {
                            foreach ($row->getCells() as $cellNumber => $cell) {
                                // Добавление даты
                                if ($cellNumber == 1) {
                                    $date = $cell->getValue();
                                    array_push($googleSpreadsheetRow, $date->format('d.m.Y'));
                                }
                                // Добавление адреса, вида работ и кол-ва часов
                                if ($cellNumber == 3 || $cellNumber == 5 || $cellNumber == 9)
                                    array_push($googleSpreadsheetRow, $cell->getValue());
                                // Добавление времени начала и окончания
                                if ($cellNumber == 7 || $cellNumber == 8) {
                                    $date = $cell->getValue();
                                    array_push($googleSpreadsheetRow, $date->format('H:i:s'));
                                }
                            }
                        }
                        array_push($googleSpreadsheetRows, $googleSpreadsheetRow);
                    }
        $reader->close();

        return $googleSpreadsheetRows;
    }
}