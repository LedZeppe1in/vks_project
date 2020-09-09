<?php

namespace app\controllers;

use Box\Spout\Common\Entity\Style\Border;
use Box\Spout\Writer\Common\Creator\Style\BorderBuilder;
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
use Box\Spout\Common\Entity\Style\Color;
use Box\Spout\Common\Entity\Style\CellAlignment;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Reader\Common\Creator\ReaderEntityFactory;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;

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
                // Формирование URL для скачивания файла таблицы с Yandex-диска
                $urlYandexDisk  = 'https://cloud-api.yandex.net:443/v1/disk/public/resources?public_key=' .
                    urlencode($cloudDriveModel->yandexFileLink);
                // Получение ссылки на скачивание файла таблицы с Yandex-диска
                $handle = curl_init();
                curl_setopt($handle, CURLOPT_URL, $urlYandexDisk);
                curl_setopt($handle, CURLOPT_HTTPHEADER, array());
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                $yandexResponse = curl_exec($handle);
                $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);
                // Если нет ошибки
                if ($code == 200) {
                    // Наличие ошибки при проверке
                    $data["checking_error"] = false;
                    // Получение метаинформации от Yandex
                    $yandexResource = json_decode($yandexResponse, true);
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
     * @throws \Box\Spout\Writer\Exception\WriterNotOpenedException
     */
    public function actionIndex()
    {
        // Формирование модели (формы) CloudDriveForm
        $cloudDriveModel = new CloudDriveForm();
        // Если загружена форма (POST-запрос)
        if ($cloudDriveModel->load(Yii::$app->request->post()) && $cloudDriveModel->validate()) {
            // Формирование URL для скачивания файла таблицы с Google Doc
            $urlGoogleDisk = 'https://docs.google.com/spreadsheets/d/1IW3b0wT03R8bnojqI6GnyZo2uKVsYvBy/export?format=xlsx&id=1IW3b0wT03R8bnojqI6GnyZo2uKVsYvBy';
            // Формирование URL для скачивания файла таблицы с Yandex-диска
            $urlYandexDisk  = 'https://cloud-api.yandex.net:443/v1/disk/public/resources/download?public_key=' .
                urlencode($cloudDriveModel->yandexFileLink);
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
                // Пусть до папки с таблицами
                $path = Yii::$app->basePath . '/web/spreadsheets/';
                // Название файла таблицы, полученного с Google Sheets
                $googleFileName = 'google-spreadsheet.xlsx';
                // Название файла таблицы, полученного с Yandex-диска
                $yandexFileName = 'yandex-spreadsheet.xlsx';
                // Получение содержимого Google-таблицы
                file_put_contents($path . $googleFileName, file_get_contents($urlGoogleDisk));
                // Получение содержимого Yandex-таблицы
                file_put_contents($path . $yandexFileName, file_get_contents($resource['href']));
                // Сообщение об успешной синхронизации
                Yii::$app->getSession()->setFlash('success', 'Синхронизация прошла успешно!');

                return $this->render('synchronization', [
                    'res' => $resource,
                ]);
            } else
                // Сообщение об ошибке
                Yii::$app->getSession()->setFlash('error', 'Ошибка синхронизации!');
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