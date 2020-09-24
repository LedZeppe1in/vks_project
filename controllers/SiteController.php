<?php

namespace app\controllers;

use Google_Service_Drive;
use Yii;
use yii\bootstrap\ActiveForm;
use yii\data\ArrayDataProvider;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\CloudDriveForm;
use app\components\GoogleSpreadsheet;
use app\components\YandexSpreadsheet;

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
                'only' => ['index', 'logout', 'contact'],
                'rules' => [
                    [
                        'actions' => ['index', 'logout', 'contact'],
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
     * Проверка файлов электронных таблиц на Google Sheet и Yandex-диске.
     *
     * @return bool|\yii\console\Response|Response
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
                $data["success"] = true;
                // Содание объекта для работы с Yandex-таблицей
                $yandexSpreadsheet = new YandexSpreadsheet();
                // Проверка существования файла электронной таблицы на Yandex-диске
                $yandexResource = $yandexSpreadsheet->checkingSpreadsheet($cloudDriveModel->yandexFilePath);
                // Если проверка прошла успешно (файл существует)
                if ($yandexResource !== false) {
                    // Наличие ошибки при проверке
                    $data["checking_error"] = false;
                    // Получение метаинформации о файле электронной таблицыот от Yandex
                    $data["yandexResource"] = $yandexResource;
                } else
                    // Наличие ошибки при проверке
                    $data["checking_error"] = true;
            } else
                $data = ActiveForm::validate($cloudDriveModel);
            // Возвращение данных
            $response->data = $data;

            return $response;
        }

        return false;
    }

    /**
     * Главная страница сайта - синхронизация таблиц.
     *
     * @return string
     * @throws \Box\Spout\Common\Exception\IOException
     * @throws \Box\Spout\Common\Exception\UnsupportedTypeException
     * @throws \Box\Spout\Reader\Exception\ReaderNotOpenedException
     * @throws \PhpOffice\PhpSpreadsheet\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     */
    public function actionIndex()
    {
        // Формирование модели (формы) CloudDriveForm
        $cloudDriveModel = new CloudDriveForm();
        // Если загружена форма (POST-запрос)
        if ($cloudDriveModel->load(Yii::$app->request->post()) && $cloudDriveModel->validate()) {
            // Пусть до папки с таблицами
            $path = Yii::$app->basePath . '/web/spreadsheets/';
            // Содание объекта для работы с Google-таблицей
            $googleSpreadsheet = new GoogleSpreadsheet();
            // Содание объекта для работы с Yandex-таблицей
            $yandexSpreadsheet = new YandexSpreadsheet();
            // Копирование Google-таблицы на сервер
            $copyGoogleSuccess = $googleSpreadsheet->copySpreadsheetToServer($cloudDriveModel->googleFileLink, $path);
            // Копирование Yandex-таблицы на сервер
            $copyYandexSuccess = $yandexSpreadsheet->copySpreadsheetByPathToServer(
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
                        $yandexSpreadsheetRows = $yandexSpreadsheet->getAllRows($path);
                        // Синхронизация с Yandex (поиск недостающих строк)
                        list($googleSpreadsheetRows, $yandexSpreadsheetDeletedRows) = $googleSpreadsheet->syncWithYandex(
                            $yandexSpreadsheetRows,
                            $path
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
                            $uploadFlag = $yandexSpreadsheet->uploadSpreadsheetToYandexDrive($path);
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
                                                    array_push($arrayRow, $yandexSpreadsheetCell->format('H:m'));
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
                                            array_push($arrayRow, $googleSpreadsheetCell->format('H:m'));
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
                                    'addedRows' => $addedRows,
                                ]);
                            } else
                                // Сообщение об ошибке синхронизации
                                Yii::$app->getSession()->setFlash('error',
                                    'Ошибка синхронизации! При загрузке файла электронной таблицы на Yandex-диск возникли ошибки.');
                        } else
                            // Сообщение о том, что синхронизация не требуется
                            Yii::$app->getSession()->setFlash('warning',
                                'Синхронизация не требуется! Все данные актуальны.');

                        break;
                    // Если нажата кнопка синхронизации с Google-диском
                    case 'google-synchronization':
                        // Получение всех строк из Google-таблицы
                        $googleSpreadsheetRows = $googleSpreadsheet->getAllRows($path);
                        // Синхронизация с Google (поиск строк c табельными номерами)
                        $yandexSpreadsheetRows = $yandexSpreadsheet->syncWithGoogle($googleSpreadsheetRows, $path);
                        // Запись нового файла электронной таблицы с обновленными табельными номерами
                        $googleSpreadsheet->writeSpreadsheet($yandexSpreadsheetRows, $path);

                        $oauthPath = Yii::$app->basePath . '/web/google-oauth/';
                        $res = $googleSpreadsheet->uploadSpreadsheetToGoogleDrive($oauthPath, Yii::$app->session, $path);

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
                                    array_push($arrayRow, $yandexSpreadsheetCell->format('H:m'));
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
                            'dataProvider' => $dataProvider,
                            'res' => $res
                        ]);
                        break;
                }
            } else
                // Сообщение об ошибке синхронизации
                Yii::$app->getSession()->setFlash('error',
                    'Ошибка синхронизации! При копировании файла электронной таблицы на сервер возникли ошибки.');
        }

        return $this->render('index', [
            'cloudDriveModel' => $cloudDriveModel,
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
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
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
}