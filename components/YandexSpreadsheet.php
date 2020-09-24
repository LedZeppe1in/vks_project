<?php

namespace app\components;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
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
     * Проверка существования файла электронной таблицы на Yandex-диске.
     *
     * @param $yandexFilePath - полный путь к файлу электронной таблицы на Yandex-диске
     * @return bool|mixed - метаинформация о файле электронной таблицы от Yandex, иначе false
     */
    public function checkingSpreadsheet($yandexFilePath)
    {
        // Формирование URL для проверки файла электронной таблицы с Yandex-диска
        $urlYandexDisk  = 'https://cloud-api.yandex.net/v1/disk/resources?path=' . urlencode($yandexFilePath);
        // Получение URL-ссылки на файл электронной таблицы с Yandex-диска
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $urlYandexDisk);
        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . self::TOKEN));
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        // Если нет ошибки
        if ($code == 200) {
            // Получение метаинформации о файле электронной таблицы от Yandex
            $resource = json_decode($response, true);

            return $resource;
        } else
            return false;
    }

    /**
     * Копирование файла электронной таблицы с Yandex-диска на сервер по публичной URL-ссылке.
     *
     * @param $fileLink - публичная ссылка на файл электронной таблицы на Yandex-диске
     * @param $path - путь сохранения файла электронной таблицы на сервере
     * @return bool - успешность копирования
     */
    public function copySpreadsheetByURLToServer($fileLink, $path)
    {
        // Формирование URL для скачивания файла таблицы с Yandex-диска
        $urlYandexDisk  = 'https://cloud-api.yandex.net/v1/disk/public/resources/download?public_key=' .
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
        curl_close($handle);
        // Если нет ошибки
        if ($code == 200) {
            $resource = json_decode($response, true);
            // Получение содержимого Yandex-таблицы
            file_put_contents($path . $this->fileName, file_get_contents($resource['href']));

            return true;
        } else
            return false;
    }

    /**
     * Копирование файла электронной таблицы с Yandex-диска на сервер по полному пути файла.
     *
     * @param $yandexFilePath - полный путь к файлу электронной таблицы на Yandex-диске
     * @param $path - путь сохранения файла электронной таблицы на сервере
     * @return bool - успешность копирования
     */
    public function copySpreadsheetByPathToServer($yandexFilePath, $path)
    {
        // Получение ссылки на скачивание файла таблицы с Yandex-диска
        $handle = curl_init('https://cloud-api.yandex.net/v1/disk/resources/download?path=' .
            urlencode($yandexFilePath));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . self::TOKEN));
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_HEADER, false);
        $response = curl_exec($handle);
        $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);
        // Если нет ошибки
        if ($code == 200) {
            $resource = json_decode($response, true);
            // Получение содержимого Yandex-таблицы
            file_put_contents($path . $this->fileName, file_get_contents($resource['href']));

            return true;
        } else
            return false;
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
        $reader->setShouldPreserveEmptyRows(true);
        $reader->open($path . $this->fileName);
        foreach ($reader->getSheetIterator() as $sheet)
            if ($sheet->getName() === self::FIRST_SHEET_SHEET)
                foreach ($sheet->getRowIterator() as $rowNumber => $row)
                    if ($rowNumber > 1) {
                        // Запоминание текущей строки из электронной таблицы
                        $spreadsheetRow = array();
                        foreach ($row->getCells() as $cellNumber => $cell)
                            if ($cellNumber == 0 || $cellNumber == 1 || $cellNumber == 2 ||
                                $cellNumber == 3 || $cellNumber == 4)
                                array_push($spreadsheetRow, $cell->getValue());
                        // Добавление текущей строки в массив
                        $spreadsheetRows[$rowNumber] = $spreadsheetRow;
                    }
        $reader->close();

        return $spreadsheetRows;
    }

    /**
     * Запись обновленной электронной таблицы в файл.
     *
     * @param $googleSpreadsheetRows - массив найденных строк в Google-таблице для вставки
     * @param $yandexSpreadsheetDeletedRows - массив номеров строк из Yandex-таблицы, которые необходимо удалить
     * @param $path - путь к файлу электронной таблицы на сервере
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function writeSpreadsheet($googleSpreadsheetRows, $yandexSpreadsheetDeletedRows, $path)
    {
        // Чтение Yandex-таблицы
        $reader = IOFactory::createReader("Xlsx");
        $spreadsheet = $reader->load($path . $this->fileName);
        $worksheet = $spreadsheet->setActiveSheetIndexByName(self::FIRST_SHEET_SHEET);
        // Если массив с номерами строк из Yandex-таблицы не пустой
        if (!empty($yandexSpreadsheetDeletedRows)) {
            // Удаление строк из Yandex-таблицы
            $i = 0;
            foreach ($yandexSpreadsheetDeletedRows as $rowNumber) {
                $worksheet->removeRow($rowNumber - $i);
                $i++;
            }
        }
        // Если массив с найденными строками в Google-таблице не пустой
        if (!empty($googleSpreadsheetRows))
            // Добавление новых строк в Yandex-таблицу
            foreach ($googleSpreadsheetRows as $googleSpreadsheetRow) {
                // Добавление новой строки в конец электронной таблицы
                $row = $worksheet->getHighestRow();
                $worksheet->insertNewRowBefore($row + 1);
                // Определение стиля даты для ячеек с датой
                $worksheet->getStyle('A' . $row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                // Определение стилей времени для ячеек с временем
                $worksheet->getStyle('D' . $row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_DATE_TIME3);
                $worksheet->getStyle('E' . $row)
                    ->getNumberFormat()
                    ->setFormatCode(NumberFormat::FORMAT_DATE_TIME3);
                // Определение значений ячеек
                $excelDateValue = Date::PHPToExcel($googleSpreadsheetRow[0]);
                $worksheet->setCellValue('A' . $row, $excelDateValue);
                $worksheet->setCellValue('B' . $row, $googleSpreadsheetRow[1]);
                $worksheet->setCellValue('C' . $row, $googleSpreadsheetRow[2]);
                $excelStartTimeValue = Date::PHPToExcel($googleSpreadsheetRow[3]);
                $worksheet->setCellValue('D' . $row, $excelStartTimeValue);
                $excelEndTimeValue = Date::PHPToExcel($googleSpreadsheetRow[4]);
                $worksheet->setCellValue('E' . $row, $excelEndTimeValue);
                $worksheet->setCellValue('F' . $row, $googleSpreadsheetRow[5]);
                // Задание цвета ячеек
                $worksheet->getStyle('A'.$row.':L'.$row)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('dbead5');
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

    /**
     * Синхронизация с Google (поиск всех строк с проставленными табельными номерами и сотрудниками из Yandex-таблицы,
     * которых нет в Google-таблице).
     *
     * @param $googleSpreadsheetRows - массив всех строк электронной таблицы Google
     * @param $path - путь к файлу электронной таблицы на сервере
     * @return array - массив строк с проставленными табельными номерами и сотрудниками,
     * которых нет в электронной таблице Google
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public function syncWithGoogle($googleSpreadsheetRows, $path)
    {
        // Массив для строк с табельными номерами, которых нет в Google-таблице
        $yandexSpreadsheetRows = array();
        // Чтение Yandex-таблицы
        $reader = ReaderEntityFactory::createReaderFromFile($path . $this->fileName);
        $reader->open($path . $this->fileName);
        foreach ($reader->getSheetIterator() as $sheet)
            if ($sheet->getName() === self::FIRST_SHEET_SHEET)
                foreach ($sheet->getRowIterator() as $rowNumber => $row)
                    if ($rowNumber > 1) {
                        // Запоминание текущей строки из Yandex-таблицы
                        $yandexSpreadsheetRow = array();
                        foreach ($row->getCells() as $cellNumber => $cell)
                            if ($cellNumber == 0 || $cellNumber == 1 || $cellNumber == 2 || $cellNumber == 3 ||
                                $cellNumber == 4 || $cellNumber == 5 || $cellNumber == 6 || $cellNumber == 7)
                                array_push($yandexSpreadsheetRow, $cell->getValue());
                        // Обход всех строк Google-таблицы
                        foreach ($googleSpreadsheetRows as $googleSpreadsheetKey => $googleSpreadsheetRow)
                            // Проверка совпадания составного ключа строки из Google-таблицы и проверка на табельный номер
                            if ($googleSpreadsheetRow[0] == $yandexSpreadsheetRow[0] &&
                                $googleSpreadsheetRow[1] == $yandexSpreadsheetRow[1] &&
                                $googleSpreadsheetRow[2] == $yandexSpreadsheetRow[2] &&
                                $googleSpreadsheetRow[3] == $yandexSpreadsheetRow[3] &&
                                $googleSpreadsheetRow[4] == $yandexSpreadsheetRow[4] &&
                                $googleSpreadsheetRow[5] != $yandexSpreadsheetRow[6])
                                // Если массив не содержит строку из Yandex-таблицы для указанного ключа из Google-таблицы
                                if (!array_key_exists($googleSpreadsheetKey, $yandexSpreadsheetRows)) {
                                    // Добавление в массив строки из Yandex-таблицы
                                    $yandexSpreadsheetRows[$googleSpreadsheetKey] = $yandexSpreadsheetRow;
                                    // Выход из цикла
                                    break;
                                }
                    }
        $reader->close();

        return $yandexSpreadsheetRows;
    }
}