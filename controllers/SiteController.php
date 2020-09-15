<?php

namespace app\controllers;

use Yii;
use yii\bootstrap\ActiveForm;
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
            // Копирование google-таблицы на сервер
            $googleSpreadsheet->copySpreadsheetToServer($cloudDriveModel->googleFileLink, $path);
            // Копирование yandex-таблицы на сервер
            $copyFlag = $yandexSpreadsheet->copySpreadsheetByPathToServer($cloudDriveModel->yandexFilePath, $path);
            // Если нет ошибки при копировании электронной таблицы на сервер
            if ($copyFlag) {
                // Получение всех строк из yandex-таблицы
                $yandexSpreadsheetRows = $yandexSpreadsheet->getAllRows($path);
                // Синхронизация с Yandex (поиск недостающих строк)
                $googleSpreadsheetRows = $googleSpreadsheet->synchronize($yandexSpreadsheetRows, $path);
                // Запись нового файла электронной таблицы с недостающими строками
                $yandexSpreadsheet->writeSpreadsheet($googleSpreadsheetRows, $path);
                // Загрузка нового файла электронной таблицы на Yandex-диск
                $uploadFlag = $yandexSpreadsheet->uploadSpreadsheetToYandexDrive($path);
                // Если нет ошибки при загрузке электронной таблицы на Yandex-диск
                if ($uploadFlag) {
                    // Сообщение об успешной синхронизации
                    Yii::$app->getSession()->setFlash('success', 'Синхронизация прошла успешно!');

                    return $this->render('synchronization', [
                        'res' => $googleSpreadsheetRows,
                    ]);
                } else
                    // Сообщение об ошибке синхронизации
                    Yii::$app->getSession()->setFlash('error',
                        'Ошибка синхронизации! При загрузке файла электронной таблицы на Yandex-диск возникли ошибки.');
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