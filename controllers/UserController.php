<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use app\models\User;
use app\models\UserSearch;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends Controller
{
    public $layout = 'main';

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['list', 'view', 'create', 'update', 'delete',
                    'profile', 'update-profile', 'change-password'],
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['list', 'view', 'create', 'update', 'delete',
                            'profile', 'update-profile', 'change-password'],
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Lists all User models.
     * @return mixed
     */
    public function actionList()
    {
        $searchModel = new UserSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->get());

        return $this->render('list', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionView($id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Creates a new User model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return string|\yii\web\Response
     * @throws \yii\base\Exception
     */
    public function actionCreate()
    {
        $model = new User();
        $model->scenario = User::CREATE_NEW_USER_SCENARIO;

        if (Yii::$app->request->post()) {
            $model->attributes = Yii::$app->request->post('User');
            $model->setPassword($model->password);
            if ($model->save()) {
                // Вывод сообщения об удачном создании пользователя
                Yii::$app->getSession()->setFlash('success', 'Вы успешно добавили нового пользователя!');

                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Вывод сообщения об удачном изменении данных пользователя
            Yii::$app->getSession()->setFlash('success', 'Вы успешно обновили данные пользователя!');

            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Deletes an existing User model.
     * If deletion is successful, the browser will be redirected to the 'list' page.
     * @param $id
     * @return \yii\web\Response
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        // Авторизованный пользователь не может удалить сам себя
        if (Yii::$app->user->getId() == $id) {
            Yii::$app->getSession()->setFlash('warning', 'Вы не можете удалить себя!');
        } else {
            $this->findModel($id)->delete();
            // Вывод сообщения об успешном удалении пользователя
            Yii::$app->getSession()->setFlash('success', 'Вы успешно удалили пользователя!');
        }

        return $this->redirect(['list']);
    }

    /**
     * Displays a single User model.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionProfile($id)
    {
        return $this->render('profile', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * Updates an existing User model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     * @throws NotFoundHttpException if the model cannot be found
     */
    public function actionUpdateProfile($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            // Вывод сообщения об успешном удалении пользователя
            Yii::$app->getSession()->setFlash('success', 'Вы успешно обновили данные своего аккаунта!');

            return $this->redirect(['profile', 'id' => $model->id]);
        }

        return $this->render('update-profile', [
            'model' => $model,
        ]);
    }

    /**
     * Смена пароля пользователю.
     *
     * @param $id - идентификатор пользователя
     * @return string
     * @throws NotFoundHttpException
     * @throws \Throwable
     * @throws \yii\base\Exception
     * @throws \yii\db\StaleObjectException
     */
    public function actionChangePassword($id)
    {
        $model = $this->findModel($id);
        $model->scenario = User::CHANGE_PASSWORD_SCENARIO;
        if (Yii::$app->request->post()) {
            $model->attributes = Yii::$app->request->post('User');
            $model->setPassword($model->password);
            if ($model->validate()) {
                $model->updateAttributes(['password_hash']);
                Yii::$app->getSession()->setFlash('success', 'Вы успешно поменяли пароль!');

                return $this->render('profile', [
                    'model' => $this->findModel($id),
                ]);
            }
        }

        return $this->render('change-password', [
            'model' => $model,
        ]);
    }

    /**
     * Finds the User model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return User the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = User::findOne($id)) !== null) {
            return $model;
        }

        throw new NotFoundHttpException('Запрашиваемая страница не существует.');
    }
}