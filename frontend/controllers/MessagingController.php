<?php
declare(strict_types=1);

namespace frontend\controllers;

use Yii;
use frontend\models\Messaging;
use yii\data\ActiveDataProvider;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;

class MessagingController extends Controller
{
   
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' =>VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
            'access' => 
                [
                'class' => \yii\filters\AccessControl::class,
                'only' => ['index','create', 'update','delete','view'],
                'rules' => [
                [
                  'allow' => true,
                  'roles' => ['admin'],
                ],
                ],
            ], 
        ];
    }

    /**
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Messaging::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * @param int $id
     * @return string
     */
    public function actionView(int $id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * @return Response|string 
     */
    public function actionCreate()
    {
        $model = new Messaging();
        if ($model->load((array)Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->getId()]);
        }
        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $id
     * @return Response|string
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        if ($model->load((array)Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->getId()]);
        }
        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * @param int $id
     * @return Response
     */
    public function actionDelete(int $id)
    {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
    }
    
    /**
     * @param int $id
     * @return Messaging
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id)
    {
        if (($model = Messaging::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
    }
}
