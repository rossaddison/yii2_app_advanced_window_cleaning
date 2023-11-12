<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Costdetail;
use yii\helpers\Json;
use yii\db\IntegrityException;
use frontend\models\CostdetailSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\Session;
use yii\filters\VerbFilter;

class CostdetailController extends Controller
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
                [
                  'allow' => true,
                  'verbs' => ['POST']
                ],  
                ],
            ], 
        ];
    }

    /**
     * @param int $id
     * @return string
     */    
    public function actionIndex(int $id)
    {
        // Store the cost header id in session so that a cost detail can be created later in function actionCreate
        /**
         * @psalm-suppress UndefinedMagicPropertyFetch Yii::$app->session
         */
        $session = Yii::$app->session;
        if ($session->isActive) {
            $session->open();
            $session['cost_header_id'] = $id;
        }
        $searchModel = new CostdetailSearch();
        $queryParams = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($queryParams);
        $dataProvider->setSort([
            'attributes' => [
                 'costsubcategory_id.name' => [
                    'asc' => ['works_costsubcategory.name' => SORT_ASC],
                    'desc' => ['works_costsubcategory.name' => SORT_DESC],
                    'default' => SORT_ASC,
                ],  
                'cost_id.costnumber' => [
                    'asc' => ['works_cost.costnumber' => SORT_ASC],
                    'desc' => ['works_cost.costnumber' => SORT_DESC],
                    'default' => SORT_ASC,
                ],
            ],
            'defaultOrder' => [
              'costsubcategory_id.name'=> SORT_ASC,  
              'cost_id.costnumber' => SORT_ASC,
            ]
        ]);
            
        if (Yii::$app->request->post('hasEditable')) {
           /**
            * @var int $costheaderId 
            */ 
           $costheaderId = Yii::$app->request->post('editableKey');
           $model = Costdetail::findOne($costheaderId);
           if (null!==$model) {
            $posted = current((array)$_POST['Costdetail']);
            $post = ['Costdetail' => $posted];
            $model->load($post) ? $model->save() : '';
            //$output must be initialised otherwise you will get an 'internal server error'
            //if unit price and paid are not updated but paymenttype is updated.
            $output = '';
            if (isset($posted['unit_price'])) {
              $output = Yii::$app->formatter->asDecimal($model->getUnitPrice(), 2);
            }
            if (isset($posted['paid'])) {
              $output = Yii::$app->formatter->asDecimal($model->getPaid(), 2);
            }
            return Json::encode(['output'=>$output, 'message'=>'']);
           }
        }

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
        /**
         * @psalm-suppress UndefinedMagicPropertyFetch Yii::$app->session
         */
        $session = Yii::$app->session;
        $cost_header_id = 0;
        if ($session->isActive) {
            $cost_header_id = (int)$session->get('cost_header_id');
        }    
        $model = new Costdetail();
        if ($model->load((array)Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $cost_header_id]);
        } else {
            return $this->render('create', [
                'model' => $model, 'cost_header_id'=> $cost_header_id
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
            $id = $model->getCost_detail_id();
            return $this->redirect(['view', 'id' => $id ]);
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
        try {
            $model = $this->findModel($id);
	    $this->findModel($id)->delete();            
            return $this->redirect(['index','id'=>$model->getCost_header_id()]);
	} catch(IntegrityException $e) {              
              throw new \yii\web\HttpException(404, Yii::t('app','Integrity Constraint exception. Exception: ').$e);
        }
    }
    
    /**
     * @return void 
     * @throws NotFoundHttpException
     */
    public function actionPaidticked()
    {
      $keylist = (array)Yii::$app->request->get('keylist');
      if (!empty($keylist)){
        /**
         * @var int $key
         * @var int $value 
         */        
        foreach ($keylist as $key => $value)
        {
              $model = $this->findModel($value);
              $model->setPaid($model->getUnitPrice());
              $model->save();
        }
      }
      else {throw new NotFoundHttpException(Yii::t('app','No ticks selected.'));}      
    }
    
    /**
     * @return void
     * @throws NotFoundHttpException
     */
    public function actionUnpaidticked()
   {
      $keylist = (array)Yii::$app->request->get('keylist');
      if (!empty($keylist)){
        /**
         * @var int $key
         * @var int $value 
         */      
        foreach ($keylist as $key => $value)
        {
            $model = $this->findModel($value);
            $model->setPaid(0);
            $model->save();
        }
      }
      else {throw new NotFoundHttpException(Yii::t('app','No ticks selected.'));}
    }
    
    /**
     * @param int $id
     * @return Response
     */    
    public function actionPay(int $id)
    {
        $model = $this->findModel($id);
        $cost_detail_id = $model->getCost_detail_id();
        $model->setPaid($model->getUnitPrice());
        $model->save();
        return $this->redirect(['view', 'id' => $cost_detail_id]);
    }
    
    /**
     * @param int $id
     * @return Response
     */
    public function actionUnpay(int $id)
    {
        $model = $this->findModel($id);
        $cost_detail_id = $model->getCost_detail_id();
        $model->setPaid(0.00);
        $model->save();        
        return $this->redirect(['view', 'id' => $cost_detail_id]);
    }
    
    /**
     * @return void 
     * @throws NotFoundHttpException
     */
    public function actionDeleteticked()
   {
      $keylist = (array)Yii::$app->request->get('keylist');
      if (!empty($keylist)){
      /**
       * @var int $key
       * @var int $value 
       */    
      foreach ($keylist as $key => $value)
      {
            $model = $this->findModel($value);
            $model->delete();
      } 
      }
      else {throw new NotFoundHttpException(Yii::t('app','No ticks selected.'));}
    }
    
    /**
     * @return void
     * @throws NotFoundHttpException
     */
    public function actionPaymentmethodcashticked()
    {
      /**
       * @var array Yii::$app->request->get('keylist')
       */
      $keylist = Yii::$app->request->get('keylist');
      if (!empty($keylist)){
      /**
       * @var int $key
       * @var int $value 
       */    
      foreach ($keylist as $key => $value)
      {
        $model = $this->findModel($value);
        $model->paymenttype = "Cash";
        $model->save();
      }
      }
      else {throw new NotFoundHttpException(Yii::t('app','No ticks selected.'));}
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
        $sliderfontcostdetail = (string)Yii::$app->request->get('sliderfontcostdetail');
        $session_slider->set('sliderfontcostdetail', $sliderfontcostdetail);
        $font = (string)$session_slider->get('sliderfontcostdetail');
        return $font;
   }
    
    /**
     * @param int $id
     * @return Costdetail
     * @throws NotFoundHttpException
     */
    protected function findModel(int $id)
    {
        if (($model = Costdetail::findOne($id)) !== null) {
            return $model;
        } else {throw new NotFoundHttpException(Yii::t('app','CostdetailController: The requested model does not exist.'));}
    }
}
