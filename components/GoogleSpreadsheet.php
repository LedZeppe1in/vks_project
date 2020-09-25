<?php

namespace app\components;

use Exception;
use Google_Client;
use Google_Service_Drive;
use Google_Service_Drive_DriveFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use yii\debug\panels\EventPanel;

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

    public $oauthCredentials = 'client_id.json';         // Название файла с учетными данными от Google
    public $tokenFileName = 'token.json';                // Название файла с token от Google
    public $fileName = 'google-spreadsheet.xlsx';        // Название файла с электронной таблицей на сервере
    public $newFileName = 'new-google-spreadsheet.xlsx'; // Название нового файла электронной таблицы на сервере после обработки

    /**
     * Получение id файла по публичной ссылке на файл электронной таблицы на Google-диске.
     *
     * @param $fileLink - публичная ссылка на файл электронной таблицы на Google-диске
     * @return mixed
     */
    public static function getFileID($fileLink)
    {
        $needle1 = 'https://drive.google.com/file/d/';
        $needle2 = '/view?usp=sharing';
        $str = str_replace($needle1, '', $fileLink);
        $fileId = str_replace($needle2, '', $str);

        return $fileId;
    }

    /**
     * Получение токена для Google Drive API.
     *
     * @param $oauthPath - путь к файлу учетных данных для Google Drive API
     * @throws \Google_Exception
     */
    function getToken($oauthPath)
    {
        $client = new Google_Client();
        $client->setApplicationName('vks-project');
        $client->setScopes(Google_Service_Drive::DRIVE);
        $client->setAuthConfig($oauthPath . $this->oauthCredentials);
        $client->setRedirectUri('http://localhost');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');
        // Load previously authorized token from a file, if it exists.
        // The file token.json stores the user's access and refresh tokens, and is
        // created automatically when the authorization flow completes for the first time.
        if (file_exists($oauthPath . $this->tokenFileName)) {
            $accessToken = json_decode(file_get_contents($oauthPath . $this->tokenFileName), true);
            $client->setAccessToken($accessToken);
        }
        // If there is no previous token or it's expired.
        if ($client->isAccessTokenExpired()) {
            // Refresh the token if possible, else fetch a new one.
            if ($client->getRefreshToken())
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            else {
                // Request authorization from the user.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));
                // Exchange authorization code for an access token.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);
                // Check to see if there was an error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Save the token to a file.
            if (!file_exists(dirname($oauthPath . $this->tokenFileName)))
                mkdir(dirname($oauthPath . $this->tokenFileName), 0700, true);
            file_put_contents($oauthPath . $this->tokenFileName, json_encode($client->getAccessToken()));
        }
    }

    /**
     * Загрузка нового файла электронной таблицы на Google-диск.
     *
     * @param $oauthPath - путь к файлам авторизации (учетным данным и токену) для Google Drive API
     * @param $session - текущая сессия пользователя
     * @param $filePath - путь к файлу электронной таблицы на сервере
     * @return bool - успешность загрузки файла
     * @throws \Google_Exception
     */
    function uploadSpreadsheetToGoogleDrive($oauthPath, $session, $filePath)
    {
        $client = new Google_Client();
        $client->setAuthConfig($oauthPath . $this->oauthCredentials);
        $client->setRedirectUri('http://localhost');
        $client->addScope(Google_Service_Drive::DRIVE);
        // Автоматическое обновление токена
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
//        if ($client->isAccessTokenExpired())
//            $client->revokeToken();
        // Если токен уже в сессии, то он мог быть обновлен - сохранение его в json-файл
        if ($session->get('upload_token')) {
            $fh = fopen($oauthPath . $this->tokenFileName, 'w');
            fwrite($fh, json_encode($session->get('upload_token')));
            fclose($fh);
        } else
            // Если токена еще нет в сессии, то получение его из json-файла и добавление в сессию
            $session->set('upload_token',
                json_decode(file_get_contents($oauthPath . $this->tokenFileName), true));
        // Установка токена
        $client->setAccessToken($session->get('upload_token'));
        // Если токен установлен
        if ($client->getAccessToken()) {
            $drive = new Google_Service_Drive($client);
            $fileList = $drive->files->listFiles();
            $flag = false;
            foreach ($fileList['files'] as $file)
                if ($file['name'] == $this->newFileName) {
                    $emptyFile = new Google_Service_Drive_DriveFile();
                    $drive->files->update($file['id'], $emptyFile, array(
                        'data' => file_get_contents($filePath . $this->newFileName),
                        'mimeType' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'uploadType' => 'multipart'
                    ));
                    $flag = true;
                }
            if (!$flag) {
                // Копирование в корень Google-диска файл электронной таблицы
                $fileArray = array('data' => file_get_contents($filePath . $this->newFileName),
                    'mimeType' => 'application/octet-stream', 'uploadType' => 'multipart');
                $file = new Google_Service_Drive_DriveFile();
                $file->setName($this->newFileName);
                $drive->files->create($file, $fileArray);
            }

            return true;
        } else
            return false;
    }

    /**
     * Проверка существования файла электронной таблицы на Google-диске.
     *
     * @param $oauthPath - путь к файлам авторизации (учетным данным и токену) для Google Drive API
     * @param $session - текущая сессия пользователя
     * @param $fileLink - публичная ссылка на файл электронной таблицы на Google-диске
     * @return array|bool - метаинформация о файле электронной таблицы от Google, иначе false
     * @throws \Google_Exception
     */
    public function checkingSpreadsheet($oauthPath, $session, $fileLink)
    {
        $client = new Google_Client();
        $client->setAuthConfig($oauthPath . $this->oauthCredentials);
        $client->setRedirectUri('http://localhost');
        $client->addScope(Google_Service_Drive::DRIVE);
        // Автоматическое обновление токена
        $client->setAccessType('offline');
        $client->setApprovalPrompt('force');
        // Если токен уже в сессии, то он мог быть обновлен - сохранение его в json-файл
        if ($session->get('upload_token')) {
            $fh = fopen($oauthPath . $this->tokenFileName, 'w');
            fwrite($fh, json_encode($session->get('upload_token')));
            fclose($fh);
        } else
            // Если токена еще нет в сессии, то получение его из json-файла и добавление в сессию
            $session->set('upload_token',
                json_decode(file_get_contents($oauthPath . $this->tokenFileName), true));
        // Установка токена
        $client->setAccessToken($session->get('upload_token'));
        // Если токен установлен
        if ($client->getAccessToken()) {
            $resource = array();
            // Получение id файла по публичной ссылке на файл электронной таблицы на Google-диске
            $fileId = self::getFileID($fileLink);
            // Инициализация объекта Google-диска
            $drive = new Google_Service_Drive($client);
            // Получение списка всех файлов и каталогов на Google-диске
            $fileList = $drive->files->listFiles();
            // Обход всех файлов и каталогов на Google-диске
            foreach ($fileList['files'] as $file)
                if ($file['id'] == $fileId) {
                    // Получение метаинформации о файле электронной таблицы от Google
                    $resource = (array)$file;

                    return $resource;
                }
            return false;
        } else
            return false;
    }

    /**
     * Копирование файла электронной таблицей с Yandex-диска на сервер.
     *
     * @param $fileLink - публичная ссылка на файл электронной таблицы на Google-диске
     * @param $path - путь сохранения файла электронной таблицы на сервере
     * @return bool - успешность копирования
     */
    public function copySpreadsheetToServer($fileLink, $path)
    {
        // Получение id файла по публичной ссылке на файл электронной таблицы на Google-диске
        $fileId = self::getFileID($fileLink);
        // Формирование URL для скачивания файла таблицы с Google Doc
        $urlGoogleDisk = 'https://docs.google.com/spreadsheets/d/' . $fileId . '/export?format=xlsx&id=' . $fileId;
        try {
            // Получение содержимого Google-таблицы
            file_put_contents($path . $this->fileName, file_get_contents($urlGoogleDisk));

            return true;
        }
        catch (Exception $e) {
            return false;
        }
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
            if ($sheet->getName() === self::REQUESTS_SHEET)
                foreach ($sheet->getRowIterator() as $rowNumber => $row)
                    if ($rowNumber > 1) {
                        // Запоминание текущей строки из электронной таблицы
                        $spreadsheetRow = array();
                        foreach ($row->getCells() as $cellNumber => $cell)
                            if ($cellNumber == 1 || $cellNumber == 3 || $cellNumber == 5 ||
                                $cellNumber == 7 || $cellNumber == 8 || $cellNumber == 10)
                                array_push($spreadsheetRow, $cell->getValue());
                        // Если массив дат пуст или если текущая дата в строке входит в диапозон дат выборки
                        if (empty($dates) || in_array($spreadsheetRow[0], $dates))
                            // Добавление текущей строки в массив
                            $spreadsheetRows[$rowNumber] = $spreadsheetRow;
                    }
        $reader->close();

        return $spreadsheetRows;
    }

    /**
     * Синхронизация с Yandex (поиск всех строк из Google-таблицы, которых нет в Yandex-таблице).
     *
     * @param $yandexSpreadsheetRows - массив всех строк электронной таблицы Yandex
     * @param $path - путь к файлу электронной таблицы на сервере
     * @param $dates - массив дат для выборки строк
     * @return array - массив из: массива строк, которых нет в электронной таблице Yandex,
     *                            массива с номерами строк из Yandex-таблицы, которые необходимо удалить
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public function syncWithYandex($yandexSpreadsheetRows, $path, $dates)
    {
        // Массив для всех строк из Google-таблицы, соответствующих определенным датам
        $allGoogleSpreadsheetRows = array();
        // Массив для строк, которых нет в Yandex-таблице
        $googleSpreadsheetRows = array();
        // Массив для номеров строк из Yandex-таблицы, которые необходимо удалить
        $yandexSpreadsheetDeletedRows = array();
        // Чтение Google-таблицы
        $reader = ReaderEntityFactory::createReaderFromFile($path . $this->fileName);
        $reader->open($path . $this->fileName);
        foreach ($reader->getSheetIterator() as $sheet)
            if ($sheet->getName() === self::REQUESTS_SHEET)
                foreach ($sheet->getRowIterator() as $rowNumber => $row)
                    if ($rowNumber > 1) {
                        // Запоминание текущей строки из Google-таблицы
                        $googleSpreadsheetRow = array();
                        foreach ($row->getCells() as $cellNumber => $cell)
                            if ($cellNumber == 1 || $cellNumber == 3 || $cellNumber == 5 ||
                                $cellNumber == 7 || $cellNumber == 8 || $cellNumber == 9)
                                array_push($googleSpreadsheetRow, $cell->getValue());
                        // Если массив дат пуст или если текущая дата в строке входит в диапозон дат выборки
                        if (empty($dates) || in_array($googleSpreadsheetRow[0], $dates)) {
                            // Проверка совпадания строки из Yandex-таблицы
                            $equality = false;
                            foreach ($yandexSpreadsheetRows as $yandexSpreadsheetRow)
                                if ($yandexSpreadsheetRow[0] == $googleSpreadsheetRow[0] &&
                                    $yandexSpreadsheetRow[1] == $googleSpreadsheetRow[1] &&
                                    $yandexSpreadsheetRow[2] == $googleSpreadsheetRow[2] &&
                                    $yandexSpreadsheetRow[3] == $googleSpreadsheetRow[3] &&
                                    $yandexSpreadsheetRow[4] == $googleSpreadsheetRow[4])
                                    $equality = true;
                            // Если совпадения нет, то добавление текущей строки в массив
                            if ($equality == false)
                                array_push($googleSpreadsheetRows, $googleSpreadsheetRow);
                            // Формирование массива со всеми строками из Google-таблицы, соответствующих определенным датам
                            array_push($allGoogleSpreadsheetRows, $googleSpreadsheetRow);
                        }
                    }
        $reader->close();
        // Формирование массива с номерами строк из Yandex-таблицы, которые необходимо удалить
        foreach ($yandexSpreadsheetRows as $yKey => $yRow)
            if ($yRow[0] != null && $yRow[1] != null && $yRow[2] != null && $yRow[3] != null && $yRow[4] != null) {
                $equality = false;
                foreach ($allGoogleSpreadsheetRows as $gRow)
                    if ($yRow[0] == $gRow[0] && $yRow[1] == $gRow[1] && $yRow[2] == $gRow[2]
                        && $yRow[3] == $gRow[3] && $yRow[4] == $gRow[4])
                        $equality = true;
                if ($equality == false)
                    array_push($yandexSpreadsheetDeletedRows, $yKey);
            }

        return array($googleSpreadsheetRows, $yandexSpreadsheetDeletedRows);
    }

    /**
     * Запись обновленной электронной таблицы в файл.
     *
     * @param $yandexSpreadsheetRows - массив найденных строк с табельными номерами из Yandex-таблицы для обновления
     * @param $path - путь к файлу электронной таблицы на сервере
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function writeSpreadsheet($yandexSpreadsheetRows, $path)
    {
        // Чтение Google-таблицы
        $reader = IOFactory::createReader("Xlsx");
        $spreadsheet = $reader->load($path . $this->fileName);
        $worksheet = $spreadsheet->setActiveSheetIndexByName(self::REQUESTS_SHEET);
        // Если массив с найденными строками в Yandex-таблице не пустой
        if (!empty($yandexSpreadsheetRows))
            // Обновление строк в Google-таблице (подстановка табельных номеров)
            foreach ($yandexSpreadsheetRows as $googleSpreadsheetKey => $yandexSpreadsheetRow)
                $worksheet->setCellValue('K' . $googleSpreadsheetKey, $yandexSpreadsheetRow[6]);
        // Обновление файла Google-таблицы
        $writer = new Xlsx($spreadsheet);
        $writer->save($path . $this->newFileName);
    }

    /**
     * Получение списка сотрудников для оповещения.
     *
     * @param $path - путь к файлу электронной таблицы на сервере
     * @param $dates - массив дат для выборки строк
     * @return array - массив с информацией о сотрудниках для оповещения
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     */
    public function getEmployeesList($path, $dates)
    {
        // Массив c информацией о сотрудниках для оповещения
        $employees = array();
        // Массив для строк c подтвержденными заявками из Google-таблицы
        $googleSpreadsheetRows = array();
        // Чтение Google-таблицы
        $reader = ReaderEntityFactory::createReaderFromFile($path . $this->fileName);
        $reader->open($path . $this->fileName);
        // Обход строк на вкладке "Заявка"
        foreach ($reader->getSheetIterator() as $sheet)
            if ($sheet->getName() === self::REQUESTS_SHEET)
                foreach ($sheet->getRowIterator() as $rowNumber => $row)
                    if ($rowNumber > 1) {
                        // Запоминание текущей строки из Google-таблицы
                        $googleSpreadsheetRow = array();
                        foreach ($row->getCells() as $cellNumber => $cell)
                            if ($cellNumber == 1 || $cellNumber == 3 || $cellNumber == 5 ||
                                $cellNumber == 7 || $cellNumber == 8 || $cellNumber == 10)
                                array_push($googleSpreadsheetRow, $cell->getValue());
                        // Если массив дат пуст или если текущая дата в строке входит в диапозон дат выборки
                        if (empty($dates) || in_array($googleSpreadsheetRow[0], $dates))
                            // Если есть тебельный номер
                            if ($googleSpreadsheetRow[5] != '')
                                // Добавление строки c подтвержденной заявкой в массив
                                array_push($googleSpreadsheetRows, $googleSpreadsheetRow);
                    }
        // Обход строк на вкладке "Сотрудники"
        foreach ($reader->getSheetIterator() as $sheet)
            if ($sheet->getName() === self::EMPLOYEES_SHEET)
                foreach ($sheet->getRowIterator() as $rowNumber => $row)
                    if ($rowNumber > 1) {
                        // Запоминание текущей строки с информацией о сотруднике из Google-таблицы
                        $employee = array();
                        $fullName = '';
                        foreach ($row->getCells() as $cellNumber => $cell) {
                            if ($cellNumber == 0)
                                $fullName = $cell->getValue();
                            if ($cellNumber == 1)
                                $fullName .= ' ' . $cell->getValue();
                            if ($cellNumber == 2) {
                                $fullName .= ' ' . $cell->getValue();
                                array_push($employee, $fullName);
                            }
                            if ($cellNumber == 3 || $cellNumber == 5)
                                array_push($employee, $cell->getValue());
                        }
                        // Цикл по всем найденным строкам подтвержденных заявок
                        foreach ($googleSpreadsheetRows as $googleSpreadsheetRow)
                            // Если табельные номера совпадают
                            if ($googleSpreadsheetRow[5] == $employee[1]) {
                                // Добавление в массив сотрудника недостающей информации
                                array_push($employee, $googleSpreadsheetRow[0]);
                                array_push($employee, $googleSpreadsheetRow[3]);
                                array_push($employee, $googleSpreadsheetRow[4]);
                                array_push($employee, $googleSpreadsheetRow[1]);
                                array_push($employee, $googleSpreadsheetRow[2]);
                                // Добавление текущей строки с информацией о сотруднике в массив
                                array_push($employees, $employee);
                            }
                    }
        $reader->close();

        return $employees;
    }
}