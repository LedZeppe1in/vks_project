<?php

namespace app\components;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Border;
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

    // Цвета для подсветки добавленных и удаленных строк
    const GREEN_COLOR  = 'dbead5';
    const YELLOW_COLOR = 'fff5a5';

    // Название папки с копиями таблиц (лог синхронизации с Yandex-диском)
    const SPREADSHEET_LOG_PATH = '/web/yandex-synchronization-logs/';

    // Название файла c токеном для доступа к Yandex-диску
    public $tokenFileName        = 'token.txt';
    // Название файла электронной таблицы на сервере для обработки
    public $fileName             = 'yandex-spreadsheet.xlsx';
    // Название промежуточного файла электронной таблицы на сервере
    public $intermediateFileName = 'intermediate-yandex-spreadsheet.xlsx';
    // Название нового файла электронной таблицы на сервере после обработки (с пустыми строками)
    public $newFileName          = 'new-yandex-spreadsheet.xlsx';

    /**
     * Проверка существования файла электронной таблицы на Yandex-диске.
     *
     * @param $oauthPath - путь к файлу c токеном для доступа к Yandex-диску
     * @param $yandexFilePath - полный путь к файлу электронной таблицы на Yandex-диске
     * @return bool|mixed - метаинформация о файле электронной таблицы от Yandex, иначе false
     */
    public function checkingSpreadsheet($oauthPath, $yandexFilePath)
    {
        // Получение токена для доступа к Yandex-диску
        if (file_exists($oauthPath . $this->tokenFileName))
            $accessToken = file_get_contents($oauthPath . $this->tokenFileName);
        else
            $accessToken = '';
        // Формирование URL для проверки файла электронной таблицы с Yandex-диска
        $urlYandexDisk  = 'https://cloud-api.yandex.net/v1/disk/resources?path=' . urlencode($yandexFilePath);
        // Получение URL-ссылки на файл электронной таблицы с Yandex-диска
        $handle = curl_init();
        curl_setopt($handle, CURLOPT_URL, $urlYandexDisk);
        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $accessToken));
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
     * @param $oauthPath - путь к файлу c токеном для доступа к Yandex-диску
     * @param $yandexFilePath - полный путь к файлу электронной таблицы на Yandex-диске
     * @param $path - путь сохранения файла электронной таблицы на сервере
     * @return bool - успешность копирования
     */
    public function copySpreadsheetByPathToServer($oauthPath, $yandexFilePath, $path)
    {
        // Получение токена для доступа к Yandex-диску
        if (file_exists($oauthPath . $this->tokenFileName))
            $accessToken = file_get_contents($oauthPath . $this->tokenFileName);
        else
            $accessToken = '';
        // Получение ссылки на скачивание файла таблицы с Yandex-диска
        $handle = curl_init('https://cloud-api.yandex.net/v1/disk/resources/download?path=' .
            urlencode($yandexFilePath));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $accessToken));
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
     * Получение строк электронной таблицы на соответствующие даты.
     *
     * @param $path - путь к файлу электронной таблицы на сервере
     * @param $dates - массив дат для выборки строк
     * @return array - массив считанных строк таблицы
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public function getRows($path, $dates)
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
                        // Если массив дат пуст или если текущая дата в строке входит в диапозон дат выборки
                        if (isset($spreadsheetRow[0]))
                            if (empty($dates) || in_array($spreadsheetRow[0], $dates))
                                // Добавление текущей строки в массив
                                $spreadsheetRows[$rowNumber] = $spreadsheetRow;
                    }
        $reader->close();

        return $spreadsheetRows;
    }

    /**
     * Удаление строк из Yandex-таблице.
     *
     * @param $yandexSpreadsheetDeletedRows - массив удаляемых строк из Yandex-таблицы
     * @param $path - путь к папке с электронной таблицей на сервере
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function deleteRows($yandexSpreadsheetDeletedRows, $path)
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
        // Обновление файла Yandex-таблицы
        $writer = new Xlsx($spreadsheet);
        $writer->save($path . $this->intermediateFileName);
    }

    /**
     * Установка цвета строк в Yandex-таблице, которые были удалены в Google-таблице.
     *
     * @param $yandexSpreadsheetDeletedRows - массив строк из Yandex-таблицы, которые необходимо отметить цветом
     * (как удаленные из Google-таблицы)
     * @param $path - путь к папке с электронной таблицей на сервере
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function setColorForRows($yandexSpreadsheetDeletedRows, $path)
    {
        // Чтение Yandex-таблицы
        $reader = IOFactory::createReader("Xlsx");
        $spreadsheet = $reader->load($path . $this->fileName);
        $worksheet = $spreadsheet->setActiveSheetIndexByName(self::FIRST_SHEET_SHEET);
        // Если массив с номерами строк из Yandex-таблицы не пустой
        if (!empty($yandexSpreadsheetDeletedRows)) {
            // Обход всех строк
            foreach ($yandexSpreadsheetDeletedRows as $rowNumber) {
                // Задание цвета ячеек
                $worksheet->getStyle('A' . $rowNumber . ':L' . $rowNumber)
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB(self::YELLOW_COLOR);
            }
        }
        // Обновление файла Yandex-таблицы
        $writer = new Xlsx($spreadsheet);
        $writer->save($path . $this->intermediateFileName);
    }

    /**
     * Добавление недостающих новых строк в Yandex-таблицу.
     *
     * @param $googleSpreadsheetRows - массив добавляемых строк в Yandex-таблицу
     * @param $yandexSpreadsheetRows - массив всех строк Yandex-таблицы
     * @param $path - путь к папке с электронной таблицей на сервере
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function addRows($googleSpreadsheetRows, $yandexSpreadsheetRows, $path)
    {
        // Поиск и формирование позиции вставки новых строк
        $rowPositions = array();
        foreach ($googleSpreadsheetRows as $googleRowKey => $googleSpreadsheetRow) {
            $currentPosition = null;
            foreach ($yandexSpreadsheetRows as $yandexRowKey => $yandexSpreadsheetRow)
                if (isset($googleSpreadsheetRow[0]) && isset($yandexSpreadsheetRow[0]) &&
                    isset($googleSpreadsheetRow[1]) && isset($yandexSpreadsheetRow[1]) &&
                    isset($googleSpreadsheetRow[2]) && isset($yandexSpreadsheetRow[2])) {
                    // Если у строк из таблиц совпадают даты
                    if ($googleSpreadsheetRow[0] == $yandexSpreadsheetRow[0]) {
                        $googleAddressCode = mb_substr($googleSpreadsheetRow[1], 3, 2);
                        $yandexAddressCode = mb_substr($yandexSpreadsheetRow[1], 3, 2);
                        // Запоминание позиции (если такого адреса еще не было и его код меньше)
                        if ((int)$googleAddressCode < (int)$yandexAddressCode &&
                            isset($rowPositions[$googleRowKey]) == false)
                            $rowPositions[$googleRowKey] = $yandexRowKey - 1;
                        // Если адреса совпадают
                        if ((int)$googleAddressCode == (int)$yandexAddressCode)
                            // Запоминание позиции (если виды работ совпадают)
                            if ($googleSpreadsheetRow[2] == $yandexSpreadsheetRow[2])
                                $rowPositions[$googleRowKey] = $yandexRowKey;
                        // Запоминание позиции (если такого адреса еще не было и его код больше)
                        if ((int)$googleAddressCode > (int)$yandexAddressCode)
                            $currentPosition = $yandexRowKey;
                    }
                }
            // Запоминание позиции для вставки в конец
            if (isset($rowPositions[$googleRowKey]) == false && isset($foo))
                $rowPositions[$googleRowKey] = $currentPosition;
        }
        // Чтение Yandex-таблицы
        $reader = IOFactory::createReader("Xlsx");
        $spreadsheet = $reader->load($path . $this->intermediateFileName);
        $worksheet = $spreadsheet->setActiveSheetIndexByName(self::FIRST_SHEET_SHEET);
        // Если массив с найденными строками в Google-таблице не пустой
        if (!empty($googleSpreadsheetRows)) {
            // Если массив с позициями вставок строк не пустой
            if (!empty($rowPositions)) {
                $i = 1;
                // Добавление новых строк в Yandex-таблицу
                foreach ($googleSpreadsheetRows as $googleKey => $googleSpreadsheetRow) {
                    foreach ($rowPositions as $positionKey => $position)
                        if ($googleKey == $positionKey) {
                            $currentPosition = $position + $i;
                            // Добавление новой строки в позицию (в конец строк по определенной дате)
                            $worksheet->insertNewRowBefore($currentPosition);
                            // Определение стиля даты для ячеек с датой
                            $worksheet->getStyle('A' . $currentPosition)
                                ->getNumberFormat()
                                ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
                            // Определение стилей времени для ячеек с временем
                            $worksheet->getStyle('D' . $currentPosition)
                                ->getNumberFormat()
                                ->setFormatCode(NumberFormat::FORMAT_DATE_TIME3);
                            $worksheet->getStyle('E' . $currentPosition)
                                ->getNumberFormat()
                                ->setFormatCode(NumberFormat::FORMAT_DATE_TIME3);
                            // Определение значений ячеек
                            $excelDateValue = Date::PHPToExcel($googleSpreadsheetRow[0]);
                            $worksheet->setCellValue('A' . $currentPosition, $excelDateValue);
                            $worksheet->setCellValue('B' . $currentPosition, $googleSpreadsheetRow[1]);
                            $worksheet->setCellValue('C' . $currentPosition, $googleSpreadsheetRow[2]);
                            $excelStartTimeValue = Date::PHPToExcel($googleSpreadsheetRow[3]);
                            $worksheet->setCellValue('D' . $currentPosition, $excelStartTimeValue);
                            $excelEndTimeValue = Date::PHPToExcel($googleSpreadsheetRow[4]);
                            $worksheet->setCellValue('E' . $currentPosition, $excelEndTimeValue);
                            $worksheet->setCellValue('F' . $currentPosition, $googleSpreadsheetRow[5]);
                            // Задание цвета ячеек
                            $worksheet->getStyle('A' . $currentPosition . ':L' . $currentPosition)
                                ->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()
                                ->setARGB(self::GREEN_COLOR);
                            $i++;
                        }
                }
            } else {
                // Добавление новых строк в Yandex-таблицу
                foreach ($googleSpreadsheetRows as $key => $googleSpreadsheetRow) {
                    // Добавление новой строки в конец электронной таблицы
                    $row = $worksheet->getHighestRow() + 1;
                    $worksheet->insertNewRowBefore($row);
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
                    // Задание границ ячеек
                    $border = array(
                        'borders' => array(
                            'outline' => array(
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => array('argb' => '000000'),
                            ),
                            'inside' => array(
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => array('argb' => '000000'),
                            ),
                        ),
                    );
                    $worksheet->getStyle('A' . $row . ':L' . $row)->applyFromArray($border);
                    // Задание цвета ячеек
                    $worksheet->getStyle('A' . $row . ':L' . $row)
                        ->getFill()
                        ->setFillType(Fill::FILL_SOLID)
                        ->getStartColor()
                        ->setARGB(self::GREEN_COLOR);
                }
            }
        }
        // Обновление файла Yandex-таблицы
        $writer = new Xlsx($spreadsheet);
        $writer->save($path . $this->newFileName);
    }

    /**
     * Загрузка нового файла электронной таблицы на Yandex-диск.
     *
     * @param $oauthPath - путь к файлу c токеном для доступа к Yandex-диску
     * @param $path - путь к файлу электронной таблицы на сервере
     * @param $fileName - путь к файлу электронной таблицы на Yandex-диске для записи
     * @return bool - успешность загрузки файла
     */
    public function uploadSpreadsheetToYandexDrive($oauthPath, $path, $fileName)
    {
        // Получение токена для доступа к Yandex-диску
        if (file_exists($oauthPath . $this->tokenFileName))
            $accessToken = file_get_contents($oauthPath . $this->tokenFileName);
        else
            $accessToken = '';
        // Запрашивание URL для загрузки файла
        $handle = curl_init('https://cloud-api.yandex.net/v1/disk/resources/upload?path=' .
            urlencode($fileName) . '&overwrite=true');
        curl_setopt($handle, CURLOPT_HTTPHEADER, array('Authorization: OAuth ' . $accessToken));
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
     * @param $dates - массив дат для выборки строк
     * @return array - массив строк с проставленными табельными номерами и сотрудниками,
     * которых нет в электронной таблице Google
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public function syncWithGoogle($googleSpreadsheetRows, $path, $dates)
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
                        // Если массив дат пуст или если текущая дата в строке входит в диапозон дат выборки
                        if (empty($dates) || in_array($yandexSpreadsheetRow[0], $dates)) {
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
                    }
        $reader->close();

        return $yandexSpreadsheetRows;
    }
}