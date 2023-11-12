<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Costcategory;
use frontend\models\CostcategorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;

class CostcategoryController extends Controller
{
    public $id;
    
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
                            'only' => ['view','create', 'update','delete'],
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
        $searchModel = new CostcategorySearch();
        $queryParams = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
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
        $model = new Costcategory();
        if ($model->load((array)Yii::$app->request->post()) && $model->save()) {
            $id = $model->getId();
            return $this->redirect(['view', 'id' => $id]);
        } else {
            return $this->render('create', [
                'model' => $model,
                
            ]);
        }
    }
    
    /**
     * @param int $id
     * @return Response|string
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        if ($model->load((array)Yii::$app->request->post()) && $model->save()) {
            $id = $model->getId();
            return $this->redirect(['view', 'id' => $id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }

    /**
     * 
     * @param int $id
     * @return Response
     * @throws \yii\web\HttpException
     */
    public function actionDelete(int $id)
    {
        try{
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
        } catch(\yii\db\IntegrityException $e) {
              throw new \yii\web\HttpException(404, Yii::t('app','First delete the cost subcategory or cost then you will be able to delete this file. Exception: {$e}'));
        }
    }

    /**
     * @param int $id
     * @return Costcategory
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id)
    {
        if (($model = Costcategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
        }
    }
}
