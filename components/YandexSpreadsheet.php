<?php

namespace app\components;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;

/**
 * YandexSpreadsheet - класс для представления сущностей электронной таблицы из Yandex-диска.
 *
 * @package app\components
 */
class YandexSpreadsheet
{
    // Токен для Яндекс.Диск REST API
    const TOKEN  = 'AgAEA7qgt0CSAAaU22zcxLZGt0T9jHjkiESKl6o';

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

    public $fileName = 'yandex-spreadsheet.xlsx';        // Название файла электронной таблицы на сервере для обработки
    public $newFileName = 'new-yandex-spreadsheet.xlsx'; // Название нового файла электронной таблицы на сервере после обработки

    /**
     * Копирование файла электронной таблицей с Yandex-диска на сервер.
     *
     * @param $fileLink - публичная ссылка на файл электронной таблицы на Yandex-диске
     * @param $path - путь сохранения файла электронной таблицы на сервере
     * @return bool - успешность копирования
     */
    public function copySpreadsheetToServer($fileLink, $path)
    {
        // Формирование URL для скачивания файла таблицы с Yandex-диска
        $urlYandexDisk  = 'https://cloud-api.yandex.net:443/v1/disk/public/resources/download?public_key=' .
            urlencode($fileLink);
        // Получение ссылки на скачивание файла таблицы с Yandex-диска
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $urlYandexDisk);
        curl_setopt($handle, CURLOPT_HTTPHEADER, array());
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        // Если нет ошибки
        if ($code == 200) {
            $resource = json_decode($response, true);
            // Получение содержимого Yandex-таблицы
            file_put_contents($path . $this->fileName, file_get_contents($resource['href']));
        } else
            return false;

        return true;
    }

    /**
     * Получение всех строк электронной таблицы.
     *
     * @param $path - путь к файлу электронной таблицы на сервере
     * @return array - массив считанных строк таблицы
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public function getAllRows($path)
    {
        $spreadsheetRows = array();
        $reader = ReaderEntityFactory::createReaderFromFile($path . $this->fileName);
        $reader->open($path . $this->fileName);
        $reader->setShouldPreserveEmptyRows(true);
        foreach ($reader->getSheetIterator() as $sheet)
            if ($sheet->getName() === self::FIRST_SHEET_SHEET)
                foreach ($sheet->getRowIterator() as $rowNumber => $row)
                    if ($rowNumber > 1) {
                        $spreadsheetRow = array();
                        foreach ($row->getCells() as $cellNumber => $cell) {
                            // Добавление даты
                            if ($cellNumber == 0)
                                if (is_string($cell->getValue()))
                                    array_push($spreadsheetRow, $cell->getValue());
                                else {
                                    $date = $cell->getValue();
                                    array_push($spreadsheetRow, $date->format('Y-m-d'));
                                }
                            // Добавление адреса и вида работ
                            if ($cellNumber == 1 || $cellNumber == 2)
                                array_push($spreadsheetRow, $cell->getValue());
                            // Добавление времени начала и окончания
                            if ($cellNumber == 3 || $cellNumber == 4)
                                if (is_string($cell->getValue()))
                                    array_push($spreadsheetRow, $cell->getValue());
                                else {
                                    $date = $cell->getValue();
                                    array_push($spreadsheetRow, $date->format('H:i'));
                                }
                        }
                        array_push($spreadsheetRows, $spreadsheetRow);
                    }
        $reader->close();

        return $spreadsheetRows;
    }

    /**
     * Запись новых строк в файл электронной таблицы.
     *
     * @param $googleSpreadsheetRows - массив найденных строк в google-таблице для вставки
     * @param $path - путь к файлу электронной таблицы на сервере
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function writeSpreadsheet($googleSpreadsheetRows, $path)
    {
        // Чтение Yandex-таблицы
        $reader = IOFactory::createReader("Xlsx");
        $spreadsheet = $reader->load($path . $this->fileName);
        $worksheet = $spreadsheet->setActiveSheetIndexByName(self::FIRST_SHEET_SHEET);
        // Добавление новых строк в Yandex-таблицу
        foreach ($googleSpreadsheetRows as $googleSpreadsheetRow) {
            $row = $worksheet->getHighestRow() + 1;
            $worksheet->insertNewRowBefore($row);
            // Определение значений ячеек
            $worksheet->setCellValue('A' . $row, $googleSpreadsheetRow[0]);
            $worksheet->setCellValue('B' . $row, $googleSpreadsheetRow[1]);
            $worksheet->setCellValue('C' . $row, $googleSpreadsheetRow[2]);
            $worksheet->setCellValue('D' . $row, $googleSpreadsheetRow[3]);
            $worksheet->setCellValue('E' . $row, $googleSpreadsheetRow[4]);
            $worksheet->setCellValue('F' . $row, $googleSpreadsheetRow[5]);
            // Задание цвета ячейки
            $worksheet->getStyle('A'.$row.':L'.$row)->getFill()
                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('dbead5');
        }
        // Обновление файла Yandex-таблицы
        $writer = new Xlsx($spreadsheet);
        $writer->save($path . $this->newFileName);
    }

    /**
     * Загрузка нового файла электронной таблицы на Yandex-диск.
     *
     * @param $path - путь к файлу электронной таблицы на сервере
     * @return bool - успешность загрузки файла
     */
    public function uploadSpreadsheetToYandexDrive($path)
    {
        // Запрашивание URL для загрузки файла
        $handle = curl_init('https://cloud-api.yandex.net/v1/disk/resources/upload?path=' .
            urlencode('/' . $this->newFileName) . '&overwrite=true');
        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . self::TOKEN));
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_HEADER, false);
        $resource = curl_exec($handle);
        curl_close($handle);
        $resource = json_decode($resource, true);
        // Если нет ошибки
        if (empty($resource['error'])) {
            // Если ошибки нет, то отправляем файл на полученный URL
            $file = fopen($path . $this->newFileName, 'r');
            $handle = curl_init($resource['href']);
            curl_setopt($handle, CURLOPT_PUT, true);
            curl_setopt($handle, CURLOPT_UPLOAD, true);
            curl_setopt($handle, CURLOPT_INFILESIZE, filesize($path . $this->newFileName));
            curl_setopt($handle, CURLOPT_INFILE, $file);
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($handle, CURLOPT_HEADER, false);
            curl_exec($handle);
            $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            curl_close($handle);
            fclose($file);
            if ($code = 201)
                return true;
            else
                return false;
        } else
            return false;
    }
}