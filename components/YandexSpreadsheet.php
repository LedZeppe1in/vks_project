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

    // Цвета для подсветки добавленных строк
    const GREEN_COLOR_1 = 'dbead5';
    const GREEN_COLOR_2 = '7ef87e';

    public $tokenFileName = 'token.txt';                   // Название файла c токеном для доступа к Yandex-диску
    public $fileName      = 'yandex-spreadsheet.xlsx';     // Название файла электронной таблицы на сервере для обработки
    public $fooFileName   = 'foo-yandex-spreadsheet.xlsx';
    public $newFileName   = 'new-yandex-spreadsheet.xlsx'; // Название нового файла электронной таблицы на сервере после обработки
    public $colorFileName = 'current-cell-color.txt';      // Название файла с текущим цветом для подсветки добавленных строк

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
        $writer->save($path . $this->fooFileName);
    }

    public function addRows($googleSpreadsheetRows, $path)
    {
        $foo = array();
        // Чтение Yandex-таблицы
        $spoutReader = ReaderEntityFactory::createReaderFromFile($path . $this->fooFileName);
        $spoutReader->open($path . $this->fooFileName);
        foreach ($googleSpreadsheetRows as $key => $googleSpreadsheetRow) {
            foreach ($googleSpreadsheetRow as $keyd => $googleSpreadsheetCell)
                if ($keyd == 0)
                    //array_push($foo, $googleSpreadsheetCell->format('m/d/Y'));
                    foreach ($spoutReader->getSheetIterator() as $sheet)
                        if ($sheet->getName() === self::FIRST_SHEET_SHEET)
                            foreach ($sheet->getRowIterator() as $rowNumber => $row)
                                if ($rowNumber > 1)
                                    foreach ($row->getCells() as $cellNumber => $cell)
                                        if ($cellNumber == 0) {
//                                            $array = explode('/',  $cell->getValue());
//                                            if (isset($array[0]))
//                                                if (strlen($array[0]) == 1)
//                                                    $array[0] = '0' . $array[0];
//                                            if (isset($array[1]))
//                                                if (strlen($array[1]) == 1)
//                                                    $array[1] = '0' . $array[1];
//                                            $date = implode("/", $array);
                                            array_push($foo, [$googleSpreadsheetCell->format('m/d/Y'), date('m/d/Y', $cell->getValue())]);
//                                            if ($googleSpreadsheetCell->format('m/d/Y') == $date)
//                                                $foo[$key] = $rowNumber;
                                        }
        }
        $spoutReader->close();

//        // Чтение Yandex-таблицы
//        $reader = IOFactory::createReader("Xlsx");
//        $spreadsheet = $reader->load($path . $this->fooFileName);
//        $worksheet = $spreadsheet->setActiveSheetIndexByName(self::FIRST_SHEET_SHEET);
//        // Если массив с найденными строками в Google-таблице не пустой
//        if (!empty($googleSpreadsheetRows)) {
//            $currentColor = self::GREEN_COLOR_2;
//            $currentDate = date('d.m.Y');
//            // Если существует файл с цветом для подсветки добавленных строк
//            if (file_exists($path . $this->colorFileName)) {
//                // Получение текущего цвета подсветки добавленных строк из файла
//                $line = 0;
//                $fh = fopen($path . $this->colorFileName, 'r');
//                while (($buffer = fgets($fh)) !== FALSE) {
//                    if ($line == 0)
//                        $currentColor = $buffer;
//                    if ($line == 1)
//                        $currentDate = $buffer;
//                    $line++;
//                }
//                fclose($fh);
//            }
//            // Если файл с цветом для подсветки добавленных строк не существует или
//            // переменная с датой не равна текущей дате
//            if (file_exists($path . $this->colorFileName) == false || $currentDate != date('d.m.Y')) {
//                // Обновление переменной с текущей датой
//                $currentDate = date('d.m.Y');
//                // Смена цвета для подсветки добавленных строк
//                if ($currentColor == self::GREEN_COLOR_1 . PHP_EOL)
//                    $currentColor = self::GREEN_COLOR_2;
//                else
//                    $currentColor = self::GREEN_COLOR_1;
//                // Запись в файл текущего цвета для подсветки добавленных строк
//                file_put_contents($path . $this->colorFileName, $currentColor . PHP_EOL . $currentDate);
//            }
//            // Добавление новых строк в Yandex-таблицу
//            foreach ($googleSpreadsheetRows as $key => $googleSpreadsheetRow) {
//                foreach ($foo as $keyG => $goo)
//                    if ($key == $keyG) {
//                        // Добавление новой строки в конец электронной таблицы
//                        $worksheet->insertNewRowBefore($goo);
//                        // Определение стиля даты для ячеек с датой
//                        $worksheet->getStyle('A' . $goo)
//                            ->getNumberFormat()
//                            ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
//                        // Определение стилей времени для ячеек с временем
//                        $worksheet->getStyle('D' . $goo)
//                            ->getNumberFormat()
//                            ->setFormatCode(NumberFormat::FORMAT_DATE_TIME3);
//                        $worksheet->getStyle('E' . $goo)
//                            ->getNumberFormat()
//                            ->setFormatCode(NumberFormat::FORMAT_DATE_TIME3);
//                        // Определение значений ячеек
//                        $excelDateValue = Date::PHPToExcel($googleSpreadsheetRow[0]);
//                        $worksheet->setCellValue('A' . $goo, $excelDateValue);
//                        $worksheet->setCellValue('B' . $goo, $googleSpreadsheetRow[1]);
//                        $worksheet->setCellValue('C' . $goo, $googleSpreadsheetRow[2]);
//                        $excelStartTimeValue = Date::PHPToExcel($googleSpreadsheetRow[3]);
//                        $worksheet->setCellValue('D' . $goo, $excelStartTimeValue);
//                        $excelEndTimeValue = Date::PHPToExcel($googleSpreadsheetRow[4]);
//                        $worksheet->setCellValue('E' . $goo, $excelEndTimeValue);
//                        $worksheet->setCellValue('F' . $goo, $googleSpreadsheetRow[5]);
//                        // Задание цвета ячеек
//                        $worksheet->getStyle('A' . $goo . ':L' . $goo)
//                            ->getFill()
//                            ->setFillType(Fill::FILL_SOLID)
//                            ->getStartColor()
//                            ->setARGB($currentColor);
//                    }
//
////                // Добавление новой строки в конец электронной таблицы
////                $row = $worksheet->getHighestRow() + 1;
////                $worksheet->insertNewRowBefore($row);
////                // Определение стиля даты для ячеек с датой
////                $worksheet->getStyle('A' . $row)
////                    ->getNumberFormat()
////                    ->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
////                // Определение стилей времени для ячеек с временем
////                $worksheet->getStyle('D' . $row)
////                    ->getNumberFormat()
////                    ->setFormatCode(NumberFormat::FORMAT_DATE_TIME3);
////                $worksheet->getStyle('E' . $row)
////                    ->getNumberFormat()
////                    ->setFormatCode(NumberFormat::FORMAT_DATE_TIME3);
////                // Определение значений ячеек
////                $excelDateValue = Date::PHPToExcel($googleSpreadsheetRow[0]);
////                $worksheet->setCellValue('A' . $row, $excelDateValue);
////                $worksheet->setCellValue('B' . $row, $googleSpreadsheetRow[1]);
////                $worksheet->setCellValue('C' . $row, $googleSpreadsheetRow[2]);
////                $excelStartTimeValue = Date::PHPToExcel($googleSpreadsheetRow[3]);
////                $worksheet->setCellValue('D' . $row, $excelStartTimeValue);
////                $excelEndTimeValue = Date::PHPToExcel($googleSpreadsheetRow[4]);
////                $worksheet->setCellValue('E' . $row, $excelEndTimeValue);
////                $worksheet->setCellValue('F' . $row, $googleSpreadsheetRow[5]);
////                // Задание цвета ячеек
////                $worksheet->getStyle('A' . $row . ':L' . $row)
////                    ->getFill()
////                    ->setFillType(Fill::FILL_SOLID)
////                    ->getStartColor()
////                    ->setARGB($currentColor);
//            }
//        }
//        // Обновление файла Yandex-таблицы
//        $writer = new Xlsx($spreadsheet);
//        $writer->save($path . $this->newFileName);

        return $foo;
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
       $this->deleteRows($yandexSpreadsheetDeletedRows, $path);
       $foo = $this->addRows($googleSpreadsheetRows, $path);

       return $foo;
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