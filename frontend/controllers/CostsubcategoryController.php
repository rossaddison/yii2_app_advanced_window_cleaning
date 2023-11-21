<?php
declare(strict_types=1); 

namespace frontend\controllers;

use Yii;
use frontend\models\Costsubcategory;
use frontend\models\CostsubcategorySearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;

class CostsubcategoryController extends Controller
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
            'access' => [
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
        $searchModel = new CostsubcategorySearch();
        $queryParams = Yii::$app->request->queryParams ?: [];
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
     * @return \yii\web\Response|string
     */    
    public function actionCreate()
    {
        $model = new Costsubcategory();
        $post = (array)Yii::$app->request->post();
        if ($model->load($post) && $model->save()) {
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
     * @return \yii\web\Response|string
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        $post = (array)Yii::$app->request->post();
        if ($model->load($post) && $model->save()) {
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
     * @return \yii\web\Response
     * @throws \yii\web\HttpException
     */
    public function actionDelete(int $id)
    {
        try
        {
        $this->findModel($id)->delete();
        return $this->redirect(['index']);
        } catch(\yii\db\IntegrityException $e) {
            throw new \yii\web\HttpException(404, Yii::t('app','First delete the costs associated with this subcategory then you will be able to delete this subcategory. Exception: ').$e);
        }
    }
    
    /**
     * @param int $id
     * @return Costsubcategory
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id)
    {
        if (($model = Costsubcategory::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
        }
    }
}
