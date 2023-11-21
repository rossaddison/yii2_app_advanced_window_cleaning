<?php
declare(strict_types=1); 

namespace frontend\controllers;

use Yii;
use yii\data\ActiveDataProvider;
use frontend\models\Historyline;
use frontend\models\Historylinesearch;
use yii\helpers\Json;
use yii\filters\VerbFilter;
use yii\web\Response;

class HistorylineController extends \yii\web\Controller
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
                'only' => ['index','create', 'update','delete','view','grid'],
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
        $model = new Historyline();

        if ($model->load((array)Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
    }
    
    /**
     * 
     * @return string
     */
    public function actionGrid()
    {
        $searchModel = new Historylinesearch();
        $queryParams = Yii::$app->request->queryParams ?: [];
        $dataProvider = $searchModel->search($queryParams);
        $dataProvider->setSort([
            'attributes' => [
                'start' => [
                    'asc' => ['start' => SORT_DESC],
                    'desc' => ['start' => SORT_ASC],
                    'default' => SORT_DESC,
                ],
            ],
            'defaultOrder' => [
              'start' => SORT_DESC,
            ]
          ]);
         
        if (Yii::$app->request->post('hasEditable')) {
            /**
             * @psalm-suppress RiskyCast
             */
            $editablekey = (int)Yii::$app->request->post('editableKey');
            $model = Historyline::findOne($editablekey);
            if (null!==$model) {
                $post = ['Historyline' => current((array)Yii::$app->request->post('Historyline'))];
                if ($model->load($post)) {
                    $model->save();
                }
                return Json::encode(['output'=> $post, 'message'=>'']);
            }    
        }
        return $this->render('grid', [
            'dataProvider' => $dataProvider,
            'searchModel' => $searchModel,
        ]);
    }
    
    /**
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionSalesorderheader(int $id)
    {
        return $this->redirect(['salesorderheader'],$id);
    }
    
    /**
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionSalesorderdetail(int $id)
    {
        return $this->redirect(['salesorderdetail'],$id);
    }
    
    /**
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionProductcategory($id)
    {
        return $this->redirect(['productcategory'],$id);
    }
    
    /**
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionProductsubcategory($id)
    {
        return $this->redirect(['productsubcategory'],$id);
    }
    
    /**
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionProduct($id)
    {
        return $this->redirect(['product'],$id);
    }
    
    /**
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionCost($id)
    {
        return $this->redirect(['cost'],$id);
    }
    
    /**
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionCostcategory($id)
    {
        return $this->redirect(['costcategory'],$id);
    }
    
    /**
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionCostsubcategory($id)
    {
        return $this->redirect(['costsubcategory'],$id);
    }
    
    /**
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionCarousal($id)
    {
        return $this->redirect(['carousal'],$id);
    }
    
    /**
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionQuicknote($id)
    {
        return $this->redirect(['quicknote'],$id);
    }
    
    /**
     * 
     * @param int $id
     * @return \yii\web\Response
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->redirect(['index']);
    }

    /**
     * 
     * @param int $id
     * @return string
     */
    public function actionIndex()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Historyline::find(),
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'pagination' => false,
        ]);
    }
    
    /**
     * 
     * @param int $id
     * @return \yii\web\Response|string
     */
    public function actionUpdate(int $id)
    {
        $model = $this->findModel($id);
        if ($model->load((array)Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            return $this->render('update', [
                'model' => $model,
            ]);
        }
    }
    
    /**
    * @return string 
    */
   public function actionSlider()
   {
        /**
         * @psalm-suppress UndefinedMagicPropertyFetch Yii::$app->session
         */
        $session_slider = Yii::$app->session; 
        if ($session_slider->isActive) {
            $session_slider->open();
        }    
        /**
         * @psalm-suppress PossiblyInvalidCast 
         */
        $sliderfontcostdetail = (string)Yii::$app->request->get('sliderfonthistoryline');
        $session_slider->set('sliderfonthistoryline', $sliderfontcostdetail);
        $font = (string)$session_slider->get('sliderfonthistoryline');
        return $font;
   }
    
    /**
     * 
     * @param int $id
     * @return Historyline
     * @throws \yii\web\NotFoundHttpException
     */
    protected function findModel(int $id)
    {
        if (($model = Historyline::findOne($id)) !== null) {
            return $model;
        }
        throw new \yii\web\NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
    }
}
