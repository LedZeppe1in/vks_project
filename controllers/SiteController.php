<?php

namespace app\controllers;

use Yii;
use DateTime;
use Exception;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\bootstrap\ActiveForm;
use yii\data\ArrayDataProvider;
use app\models\User;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\CloudDriveForm;
use app\models\NotificationForm;
use app\components\GoogleSpreadsheet;
use app\components\YandexSpreadsheet;
use app\models\NotificationResultForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout', 'contact', 'data-synchronization', 'check-message-status'],
                'rules' => [
                    [
                        'actions' => ['logout', 'contact', 'data-synchronization', 'check-message-status'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Главная страница сайта.
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Проверка файлов электронных таблиц на Google Sheet и Yandex-диске.
     *
     * @return bool|\yii\console\Response|Response
     * @throws \Google_Exception
     */
    public function actionChecking()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели (формы) CloudDriveForm
            $cloudDriveModel = new CloudDriveForm();
            // Определение полей модели шаблона факта и валидация формы
            if ($cloudDriveModel->load(Yii::$app->request->post()) && $cloudDriveModel->validate()) {
                // Успешный ввод данных
                $data['success'] = true;
                // Содание объекта для работы с Google-таблицей
                $googleSpreadsheet = new GoogleSpreadsheet();
                // Содание объекта для работы с Yandex-таблицей
                $yandexSpreadsheet = new YandexSpreadsheet();
                // Пусть до папки с учетными данными Google
                $googleOAuthPath = Yii::$app->basePath . '/web/google-oauth/';
                // Проверка существования файла электронной таблицы на Google-диске
                $googleResource = $googleSpreadsheet->checkingSpreadsheet(
                    $googleOAuthPath,
                    Yii::$app->session,
                    $cloudDriveModel->googleFileLink
                );
                // Пусть до папки с файлом токена для доступа к Yandex-диску
                $yandexOAuthPath = Yii::$app->basePath . '/web/yandex-oauth/';
                // Проверка существования файла электронной таблицы на Yandex-диске
                $yandexResource = $yandexSpreadsheet->checkingSpreadsheet(
                    $yandexOAuthPath,
                    $cloudDriveModel->yandexFilePath
                );
                // Если проверка прошла успешно (файлы существуют)
                if ($googleResource !== false && $yandexResource !== false) {
                    // Наличие ошибки при проверке
                    $data['checkingError'] = false;
                    // Получение метаинформации о файле электронной таблицы от Google
                    $data['googleResource'] = $googleResource;
                    // Получение метаинформации о файле электронной таблицы от Yandex
                    $data['yandexResource'] = $yandexResource;
                } else
                    // Наличие ошибки при проверке
                    $data['checkingError'] = true;
            } else
                $data = ActiveForm::validate($cloudDriveModel);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Страница синхронизации табличных данных.
     *
     * @return string
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     * @throws \Google_Exception
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function actionDataSynchronization()
    {
        // Пусть до папки с таблицами
        $path = Yii::$app->basePath . '/web/spreadsheets/';
        // Формирование модели (формы) CloudDriveForm
        $cloudDriveModel = new CloudDriveForm();
        // Если существует файл с путем к электронной таблице на Google-диске
        if (file_exists(Yii::$app->basePath . '/web/' . CloudDriveForm::GOOGLE_SPREADSHEET_FILE_NAME))
            $cloudDriveModel->googleFileLink = file_get_contents(Yii::$app->basePath . '/web/' .
                CloudDriveForm::GOOGLE_SPREADSHEET_FILE_NAME);
        // Если существует файл с путем к электронной таблице на Yandex-диске
        if (file_exists(Yii::$app->basePath . '/web/' . CloudDriveForm::YANDEX_SPREADSHEET_FILE_NAME))
            $cloudDriveModel->yandexFilePath = file_get_contents(Yii::$app->basePath . '/web/' .
                CloudDriveForm::YANDEX_SPREADSHEET_FILE_NAME);
        // Формирование модели (формы) NotificationForm
        $notificationModel = new NotificationForm();
        // Если существует файл с текстом шаблона сообщения, то определяем значение поля текста с этого файла
        if (file_exists(Yii::$app->basePath . '/web/' . NotificationForm::MESSAGE_TEMPLATE_FILE_NAME))
            $notificationModel->messageTemplate = file_get_contents(Yii::$app->basePath . '/web/' .
                NotificationForm::MESSAGE_TEMPLATE_FILE_NAME);
        // Формирование DataProvider для отображения списка сотрудников
        $employees = new ArrayDataProvider([
            'allModels' => array(),
            'pagination' => [
                'pageSize' => 10000,
            ],
        ]);
        // Формирование модели (формы) NotificationResultForm
        $notificationResultModel = new NotificationResultForm();
        // Содание объекта для работы с Google-таблицей
        $googleSpreadsheet = new GoogleSpreadsheet();
        // Содание объекта для работы с Yandex-таблицей
        $yandexSpreadsheet = new YandexSpreadsheet();
        // Если загружена форма (POST-запрос)
        if ($cloudDriveModel->load(Yii::$app->request->post()) && $cloudDriveModel->validate()) {
            // Формирование массива дат для выборки строк
            $dates = $cloudDriveModel->getDates();
            // Копирование Google-таблицы на сервер
            $copyGoogleSuccess = $googleSpreadsheet->copySpreadsheetToServer($cloudDriveModel->googleFileLink, $path);
            // Пусть до папки с файлом токена для доступа к Yandex-диску
            $yandexOAuthPath = Yii::$app->basePath . '/web/yandex-oauth/';
            // Копирование Yandex-таблицы на сервер
            $copyYandexSuccess = $yandexSpreadsheet->copySpreadsheetByPathToServer(
                $yandexOAuthPath,
                $cloudDriveModel->yandexFilePath,
                $path
            );
            // Если нет ошибки при копировании электронных таблиц на сервер
            if ($copyGoogleSuccess && $copyYandexSuccess) {
                // Выбор обработки
                switch (Yii::$app->request->post('synchronization-button')) {
                    // Если нажата кнопка синхронизации с Yandex-диском
                    case 'yandex-synchronization':
                        // Получение всех строк из Yandex-таблицы
                        $yandexSpreadsheetRows = $yandexSpreadsheet->getRows($path, $dates);
                        // Синхронизация с Yandex (поиск недостающих строк)
                        list($googleSpreadsheetRows, $yandexSpreadsheetDeletedRows) = $googleSpreadsheet->syncWithYandex(
                            $yandexSpreadsheetRows,
                            $path,
                            $dates
                        );
                        // Если есть новые строки из Google-таблицы
                        if (!empty($googleSpreadsheetRows) || !empty($yandexSpreadsheetDeletedRows)) {
                            // Запись нового файла электронной таблицы с недостающими строками
                            $yandexSpreadsheet->writeSpreadsheet(
                                $googleSpreadsheetRows,
                                $yandexSpreadsheetDeletedRows,
                                $path
                            );
                            // Загрузка нового файла электронной таблицы на Yandex-диск
                            $uploadFlag = $yandexSpreadsheet->uploadSpreadsheetToYandexDrive(
                                $yandexOAuthPath,
                                $path,
                                $cloudDriveModel->yandexFilePath
                            );
                            // Если нет ошибки при загрузке электронной таблицы на Yandex-диск
                            if ($uploadFlag) {
                                // Формирование массива удаленных строк для вывода на экран
                                $yandexArray = array();
                                foreach ($yandexSpreadsheetRows as $foo => $yandexSpreadsheetRow)
                                    foreach ($yandexSpreadsheetDeletedRows as $rowNumber)
                                        if ($foo == $rowNumber) {
                                            $arrayRow = array();
                                            foreach ($yandexSpreadsheetRow as $key => $yandexSpreadsheetCell) {
                                                if ($key == 0)
                                                    array_push($arrayRow, $yandexSpreadsheetCell->format('d.m.Y'));
                                                if ($key == 1 || $key == 2)
                                                    array_push($arrayRow, $yandexSpreadsheetCell);
                                                if ($key == 3 || $key == 4)
                                                    array_push($arrayRow, $yandexSpreadsheetCell->format('H:i'));
                                            }
                                            array_push($yandexArray, $arrayRow);
                                        }
                                $deletedRows = new ArrayDataProvider([
                                    'allModels' => $yandexArray,
                                    'pagination' => [
                                        'pageSize' => 10000,
                                    ],
                                ]);
                                // Формирование массива добавленных строк для вывода на экран
                                $googleArray = array();
                                foreach ($googleSpreadsheetRows as $googleSpreadsheetRow) {
                                    $arrayRow = array();
                                    foreach ($googleSpreadsheetRow as $key => $googleSpreadsheetCell) {
                                        if ($key == 0)
                                            array_push($arrayRow, $googleSpreadsheetCell->format('d.m.Y'));
                                        if ($key == 1 || $key == 2 || $key == 5)
                                            array_push($arrayRow, $googleSpreadsheetCell);
                                        if ($key == 3 || $key == 4)
                                            array_push($arrayRow, $googleSpreadsheetCell->format('H:i'));
                                    }
                                    array_push($googleArray, $arrayRow);
                                }
                                $addedRows = new ArrayDataProvider([
                                    'allModels' => $googleArray,
                                    'pagination' => [
                                        'pageSize' => 10000,
                                    ],
                                ]);
                                // Сообщение об успешной синхронизации
                                Yii::$app->getSession()->setFlash('success', 'Синхронизация прошла успешно!');

                                return $this->render('yandex-synchronization', [
                                    'deletedRows' => $deletedRows,
                                    'addedRows' => $addedRows
                                ]);
                            } else
                                // Сообщение об ошибке синхронизации
                                Yii::$app->getSession()->setFlash('error',
                                    'Ошибка синхронизации! При загрузке файла электронной таблицы на Yandex-диск возникла ошибка.');
                        } else
                            // Сообщение о том, что синхронизация не требуется
                            Yii::$app->getSession()->setFlash('warning',
                                'Синхронизация не требуется! Все данные актуальны.');
                        break;
                    // Если нажата кнопка синхронизации с Google-диском
                    case 'google-synchronization':
                        // Получение всех строк из Google-таблицы
                        $googleSpreadsheetRows = $googleSpreadsheet->getRows($path, $dates);
                        // Синхронизация с Google (поиск строк c табельными номерами)
                        $yandexSpreadsheetRows = $yandexSpreadsheet->syncWithGoogle(
                            $googleSpreadsheetRows,
                            $path,
                            $dates
                        );
                        // Если есть обновленные строки с табельными номерами из Yandex-таблицы
                        if (!empty($yandexSpreadsheetRows)) {
                            // Пусть до папки с учетными данными Google
                            $oauthPath = Yii::$app->basePath . '/web/google-oauth/';
                            // Получение id файла по публичной ссылке на файл электронной таблицы на Google-диске
                            $fileId = GoogleSpreadsheet::getFileID($cloudDriveModel->googleFileLink);
                            // Запись новых данных в файла электронной таблицы на Google-диск
                            $uploadFlag = $googleSpreadsheet->writeSpreadsheetDataToGoogleDrive(
                                $oauthPath,
                                Yii::$app->session,
                                $fileId,
                                $yandexSpreadsheetRows
                            );
                            // Если нет ошибки при записи электронной таблицы на Google-диск
                            if ($uploadFlag) {
                                // Формирование массива добавленных строк для вывода на экран
                                $yandexArray = array();
                                foreach ($yandexSpreadsheetRows as $googleSpreadsheetKey => $yandexSpreadsheetRow) {
                                    $arrayRow = array();
                                    foreach ($yandexSpreadsheetRow as $key => $yandexSpreadsheetCell) {
                                        if ($key == 0)
                                            array_push($arrayRow, $yandexSpreadsheetCell->format('d.m.Y'));
                                        if ($key == 1 || $key == 2 || $key == 5 || $key == 6 || $key == 7)
                                            array_push($arrayRow, $yandexSpreadsheetCell);
                                        if ($key == 3 || $key == 4)
                                            array_push($arrayRow, $yandexSpreadsheetCell->format('H:i'));
                                    }
                                    array_push($arrayRow, $googleSpreadsheetKey);
                                    array_push($yandexArray, $arrayRow);
                                }
                                $dataProvider = new ArrayDataProvider([
                                    'allModels' => $yandexArray,
                                    'pagination' => [
                                        'pageSize' => 10000,
                                    ],
                                ]);
                                // Сообщение об успешной синхронизации
                                Yii::$app->getSession()->setFlash('success', 'Синхронизация прошла успешно!');

                                return $this->render('google-synchronization', [
                                    'dataProvider' => $dataProvider
                                ]);
                            } else
                                // Сообщение об ошибке синхронизации
                                Yii::$app->getSession()->setFlash('error',
                                    'Ошибка синхронизации! При загрузке файла электронной таблицы на Google-диск возникла ошибка.');
                        } else
                            // Сообщение о том, что синхронизация не требуется
                            Yii::$app->getSession()->setFlash('warning',
                                'Синхронизация не требуется! Все данные актуальны.');
                        break;
                }
            } else
                // Сообщение об ошибке синхронизации
                Yii::$app->getSession()->setFlash('error',
                    'Ошибка синхронизации! При копировании файла электронной таблицы на сервер возникли ошибки.');
        }

        // Pjax-запрос
        if (Yii::$app->request->isAjax) {
            // Формирование полей модели (формы)
            $cloudDriveModel->googleFileLink = Yii::$app->request->post('google-file-link');
            $cloudDriveModel->fromDate = Yii::$app->request->post('from-date');
            $cloudDriveModel->toDate = Yii::$app->request->post('to-date');
            // Копирование Google-таблицы на сервер
            $copyGoogleSuccess = $googleSpreadsheet->copySpreadsheetToServer(
                $cloudDriveModel->googleFileLink,
                $path
            );
            // Если нет ошибки при копировании электронной таблицы Google на сервер
            if ($copyGoogleSuccess) {
                // Формирование массива дат для выборки строк
                $dates = $cloudDriveModel->getDates();
                // Получение списка сотрудников для оповещения
                $googleSpreadsheetRows = $googleSpreadsheet->getEmployeesList($path, $dates);
                // Формирование массива для GridView
                $googleArray = array();
                foreach ($googleSpreadsheetRows as $googleSpreadsheetRow) {
                    $arrayRow = array();
                    foreach ($googleSpreadsheetRow as $key => $googleSpreadsheetCell) {
                        if ($key == 0 || $key == 1 || $key == 2 || $key == 6 || $key == 7)
                            array_push($arrayRow, $googleSpreadsheetCell);
                        if ($key == 3)
                            array_push($arrayRow, $googleSpreadsheetCell->format('d.m.Y'));
                        if ($key == 4 || $key == 5)
                            array_push($arrayRow, $googleSpreadsheetCell->format('H:i'));
                    }
                    array_push($googleArray, $arrayRow);
                }
                $employees = new ArrayDataProvider([
                    'allModels' => $googleArray,
                    'pagination' => [
                        'pageSize' => 10000,
                    ],
                ]);
            }
        }

        return $this->render('data-synchronization', [
            'cloudDriveModel' => $cloudDriveModel,
            'notificationModel' => $notificationModel,
            'employees' => $employees,
            'notificationResultModel' => $notificationResultModel,
        ]);
    }

    /**
     * Сохранение путей к файлам электронных таблиц на облачных дисках Google и Yandex.
     *
     * @return bool|\yii\console\Response|Response
     */
    public function actionSavePaths()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели (формы) CloudDriveForm
            $cloudDriveModel = new CloudDriveForm();
            // Определение полей модели (формы) оповещения и валидация формы
            if ($cloudDriveModel->load(Yii::$app->request->post()) && $cloudDriveModel->validate()) {
                // Успешный ввод данных
                $data['success'] = true;
                // Пусть до папки с текстовыми файлам путей к электронным таблицам
                $path = Yii::$app->basePath . '/web/';
                // Открытие файла на запись чтобы сохранить путь к электронной таблицы на Google-диске
                $googleFile = fopen($path . CloudDriveForm::GOOGLE_SPREADSHEET_FILE_NAME, 'w');
                // Запись в файл путь к электронной таблицы на Google-диске
                fwrite($googleFile, $cloudDriveModel->googleFileLink);
                // Закрытие файла
                fclose($googleFile);
                // Открытие файла на запись чтобы сохранить путь к электронной таблицы на Yandex-диске
                $yandexFile = fopen($path . CloudDriveForm::YANDEX_SPREADSHEET_FILE_NAME, 'w');
                // Запись в файл путь к электронной таблицы на Yandex-диске
                fwrite($yandexFile, $cloudDriveModel->yandexFilePath);
                // Закрытие файла
                fclose($yandexFile);
            } else
                $data = ActiveForm::validate($cloudDriveModel);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Формирование списка рассылки.
     *
     * @return bool|\yii\console\Response|Response
     * @throws \Exception
     */
    public function actionGetMailingList()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели (формы) CloudDriveForm
            $cloudDriveModel = new CloudDriveForm();
            // Определение полей модели шаблона факта и валидация формы
            if ($cloudDriveModel->load(Yii::$app->request->post()) && $cloudDriveModel->validate()) {
                // Успешный ввод данных
                $data['success'] = true;
                // Пусть до папки с таблицами
                $path = Yii::$app->basePath . '/web/spreadsheets/';
                // Содание объекта для работы с Google-таблицей
                $googleSpreadsheet = new GoogleSpreadsheet();
                // Копирование Google-таблицы на сервер
                $copyGoogleSuccess = $googleSpreadsheet->copySpreadsheetToServer(
                    $cloudDriveModel->googleFileLink,
                    $path
                );
                // Если нет ошибки при копировании электронной таблицы Google на сервер
                if ($copyGoogleSuccess) {
                    // Ошибки при копировании файла электронной таблицы нет
                    $data['copyError'] = false;
                    // Формирование массива дат для выборки строк
                    $dates = $cloudDriveModel->getDates();
                    // Получение списка сотрудников для оповещения
                    $employees = $googleSpreadsheet->getEmployeesList($path, $dates);
                    // Формирование списка сотрудников для оповещения
                    $data['employees'] = $employees;
                    // Переменная для хранения сообщений для всех сотрудников из списка оповещения
                    $allMessages = '';
                    // Если существует файл с текстом шаблона сообщения, то определяем значение поля текста с этого файла
                    if (file_exists(Yii::$app->basePath . '/web/' .
                        NotificationForm::MESSAGE_TEMPLATE_FILE_NAME)) {
                        // Получение текста шаблона сообщения
                        $messageTemplate = file_get_contents(Yii::$app->basePath . '/web/' .
                            NotificationForm::MESSAGE_TEMPLATE_FILE_NAME);
                        // Обход всех сотрудников из списка оповещения
                        foreach ($employees as $employee) {
                            // Массив поисковых маркеров в тексте
                            $search = array(
                                NotificationForm::DATETIME_MARKER,
                                NotificationForm::ADDRESS_MARKER,
                                NotificationForm::WORK_TYPE_MARKER
                            );
                            // Формирование даты и времени
                            $dateTime = $employee[3]->format('d.m.Y') . '; ' . $employee[4]->format('H:i') . '-' .
                                $employee[5]->format('H:i');
                            // Массив замены
                            $replace = array($dateTime, $employee[6], $employee[7]);
                            // Формирование конкретного сообщения из шаблона путем замены подстрок
                            $message = str_replace($search, $replace, $messageTemplate);
                            // Запоминание текущего текста сообщения
                            $allMessages .= $message;
                        }
                    }
                    // Формирование объема рассылки
                    $data['mailingVolume'] = round(strlen($allMessages) / 67);
                    // Формирование параметров для POST-запроса к СМС-Органайзеру
                    $parameters = array('login' => NotificationForm::LOGIN, 'passwd' => NotificationForm::PASSWORD);
                    // Отправка POST-запроса СМС-Органайзеру для проверки баланса
                    $handle = curl_init();
                    curl_setopt($handle, CURLOPT_URL,NotificationForm::CHECK_BALANCE_LINK);
                    curl_setopt($handle, CURLOPT_POST, 1);
                    curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($parameters));
                    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                    $serverOutput = curl_exec($handle);
                    curl_close ($handle);
                    // Формирование текущего баланса
                    $data['balance'] = $serverOutput;

                } else
                    // Наличие ошибки при копировании файла электронной таблицы
                    $data['copyError'] = true;
            } else
                $data = ActiveForm::validate($cloudDriveModel);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Оповещение сотрудников.
     *
     * @return bool|\yii\console\Response|Response
     * @throws \Exception
     */
    public function actionNotifyEmployees()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели (формы) NotificationForm
            $notificationModel = new NotificationForm();
            // Определение полей модели шаблона факта и валидация формы
            if ($notificationModel->load(Yii::$app->request->post()) && $notificationModel->validate()) {
                $serverOutputs = array();
                // Успешный ввод данных
                $data['success'] = true;
                // Получение списка сотрудников для оповещения
                $employees = json_decode(Yii::$app->request->post('employees'));
                // Обход всех сотрудников из списка оповещения
                foreach ($employees as $employee) {
                    // Массив поисковых маркеров в тексте
                    $search = array(
                        NotificationForm::DATETIME_MARKER,
                        NotificationForm::ADDRESS_MARKER,
                        NotificationForm::WORK_TYPE_MARKER
                    );
                    // Формирование даты и времени
                    $date = new DateTime($employee[3]->date);
                    $startTime = new DateTime($employee[4]->date);
                    $endTime = new DateTime($employee[5]->date);
                    $dateTime = $date->format('d.m.Y') . '; ' . $startTime->format('H:i') . '-' .
                        $endTime->format('H:i');
                    // Массив замены
                    $replace = array($dateTime, $employee[6], $employee[7]);
                    // Формирование конкретного сообщения из шаблона путем замены подстрок
                    $message = str_replace($search, $replace, $notificationModel->messageTemplate);
                    // Формирование параметров для POST-запроса к СМС-Органайзеру
                    $parameters = array(
                        'login' => NotificationForm::LOGIN,
                        'passwd' => NotificationForm::PASSWORD,
                        'date_send' => '',
                        'status' => NotificationForm::SENDING_STATUS,
                        'txt' => iconv('UTF-8', 'CP1251', $message),
                        'smscnt' => 2,
                        'to' => $employee[2], // реальный номер телефона сотрудника
                        'sign' => NotificationForm::SIGN
                    );
                    // Отправка POST-запроса СМС-Органайзеру для отправки сообщений
                    $handle = curl_init();
                    curl_setopt($handle, CURLOPT_URL,NotificationForm::SEND_SMS_LINK);
                    curl_setopt($handle, CURLOPT_POST, 1);
                    curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($parameters));
                    curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                    $serverOutput = curl_exec($handle);
                    curl_close ($handle);
                    // Формирование результата отправки для текущего сотрудника
                    array_push($serverOutputs, $serverOutput);
                }
                // Формирование результата отправки для всех сотрудников
                $data['smsoResponse'] = $serverOutputs;
                // Формирование параметров для POST-запроса к СМС-Органайзеру
                $parameters = array('login' => NotificationForm::LOGIN, 'passwd' => NotificationForm::PASSWORD);
                // Отправка POST-запроса СМС-Органайзеру для проверки баланса
                $handle = curl_init();
                curl_setopt($handle, CURLOPT_URL,NotificationForm::CHECK_BALANCE_LINK);
                curl_setopt($handle, CURLOPT_POST, 1);
                curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($parameters));
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                $serverOutput = curl_exec($handle);
                curl_close ($handle);
                // Формирование текущего баланса
                $data['balance'] = $serverOutput;
            } else
                $data = ActiveForm::validate($notificationModel);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Сохранение шаблона текста сообщения для оповещения сотрудников.
     *
     * @return bool|\yii\console\Response|Response
     * @throws \Exception
     */
    public function actionSaveMessageTemplate()
    {
        // Ajax-запрос
        if (Yii::$app->request->isAjax) {
            // Определение массива возвращаемых данных
            $data = array();
            // Установка формата JSON для возвращаемых данных
            $response = Yii::$app->response;
            $response->format = Response::FORMAT_JSON;
            // Формирование модели (формы) NotificationForm
            $notificationModel = new NotificationForm();
            // Определение полей модели (формы) оповещения и валидация формы
            if ($notificationModel->load(Yii::$app->request->post()) && $notificationModel->validate()) {
                // Успешный ввод данных
                $data['success'] = true;
                // Пусть до папки с текстовым файлом шаблона сообщения
                $path = Yii::$app->basePath . '/web/';
                // Открытие файла на запись для сохранения шаблона сообщения
                $file = fopen($path . NotificationForm::MESSAGE_TEMPLATE_FILE_NAME, 'w');
                // Запись в файл текста шаблона сообщения
                fwrite($file, $notificationModel->messageTemplate);
                // Закрытие файла
                fclose($file);
            } else
                $data = ActiveForm::validate($notificationModel);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Получение статусов сообщений за определенный период.
     *
     * @return string
     * @throws Exception
     */
    public function actionCheckMessageStatus()
    {
        $model = new NotificationResultForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            // Формирование параметров для POST-запроса к СМС-Органайзеру
            $parameters = array(
                'login' => NotificationForm::LOGIN,
                'passwd' => NotificationForm::PASSWORD,
                'date_start' => date('Y-m-d H:i:s', strtotime($model->fromDateTime)),
                'date_end' => date('Y-m-d H:i:s', strtotime($model->toDateTime))
            );
            // Отправка POST-запроса СМС-Органайзеру для проверки статусов сообщений
            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, NotificationForm::CHECK_MESSAGE_STATUS_LINK);
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($parameters));
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($handle);
            curl_close($handle);
            // Формирование массива для GridView из ответа
            $allValues = array();
            $responseArray = explode('^', $response);
            foreach ($responseArray as $item) {
                if (!empty($item)) {
                    $currentValues = array();
                    $parts = explode('~', $item);
                    foreach ($parts as $key => $part) {
                        $values = explode('=', $part);
                        if (isset($values[1])) {
                            $strReplace = str_replace('++', ' ', $values[1]);
                            if ($key == 2) {
                                $dateTime = new DateTime($strReplace);
                                array_push($currentValues, $dateTime->format('d.m.Y H:i'));
                            } else
                                array_push($currentValues, $strReplace);
                        }
                    }
                    array_push($allValues, $currentValues);
                }
            }
            $dataProvider = new ArrayDataProvider([
                'allModels' => $allValues,
                'pagination' => [
                    'pageSize' => 10000,
                ],
            ]);

            // Сообщение об успешной проверке статусов сообщений
            Yii::$app->getSession()->setFlash('success', 'Вы успешно проверили статусы сообщений!');

            return $this->render('notification-result', [
                'dataProvider' => $dataProvider
            ]);
        }

        return $this->render('check-message-status', [
            'model' => $model,
        ]);
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post())) {
            $parameters = array(
                'login' => $model->username,
                'passwd' => $model->password,
            );
            $handle = curl_init();
            curl_setopt($handle, CURLOPT_URL, User::CHECK_USER);
            curl_setopt($handle, CURLOPT_POST, 1);
            curl_setopt($handle, CURLOPT_POSTFIELDS, http_build_query($parameters));
            curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($handle);
            curl_close($handle);
            if ($response) $model->login();
            return $this->goBack();
        }
        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->redirect('login');
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Страница с политикой конфиденциальности.
     *
     * @return string
     */
    public function actionPrivacyPolicy()
    {
        return $this->render('privacy-policy');
    }
}