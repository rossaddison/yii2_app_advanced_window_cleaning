<?php
declare(strict_types=1);

namespace frontend\controllers;

use Yii;
use frontend\models\Messagelog;
use frontend\models\MessagelogSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\filters\VerbFilter;

class MessagelogController extends Controller
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
                            'only' => ['index','create', 'update','view','delete'],
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
     * 
     * @return string
     */
    public function actionIndex()
    {
            $searchModel = new MessagelogSearch();
            $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            $dataProvider->setSort([
            'attributes' => [
                    'product_id.name' => [
                    'asc' => ['works_product.name' => SORT_ASC],
                    'desc' => ['works_product.name' => SORT_DESC],
                    'default' => SORT_ASC,
                 ],         
                    'salesorderdetail_id.sid' => [
                    'asc' => ['works_salesorderdetail.sales_order_detail_id' => SORT_ASC],
                    'desc' => ['works_salesorderdetail.sales_order_detail_id' => SORT_DESC],
                    'default' => SORT_ASC,
                 ],  
                
            ],
            'defaultOrder' => [
              'salesorderdetail_id.sid'=> SORT_DESC,  
              'product_id.name' => SORT_ASC,
            ]
          ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * 
     * @param int $id
     * @return mixed
     */
    public function actionView(int $id)
    {
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

    /**
     * 
     * @return Response|string
     */
    public function actionCreate()
    {
        $model = new Messagelog();

        if ($model->load((array)Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->getId()]);
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * 
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
              throw new \yii\web\HttpException(404, Yii::t('app','First delete the daily clean items this message was sent to then you will be able to delete this message. Exception: ').$e);
        }
    }

    /**
     * 
     * @param int $id
     * @return Messagelog
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id)
    {
        if (($model = Messagelog::findOne($id)) !== null) {
            return $model;
        }
        throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
    }
}
