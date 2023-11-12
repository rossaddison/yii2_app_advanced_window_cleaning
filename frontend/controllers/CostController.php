<?php
namespace frontend\controllers;

use Yii;
use frontend\models\Costsubcategory;
use frontend\models\Cost;
use frontend\models\Costdetail;
use frontend\models\Costsearch;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Response;
use yii\web\Session;
use yii\filters\VerbFilter;
use yii\db\ActiveRecord;
use yii\db\Query;
use yii\behaviors\TimestampBehavior;

class CostController extends Controller
{
    
    public function behaviors()
    {
        return [
                'verbs' => 
                            [
                            'class' =>VerbFilter::class,
                            'actions' =>    [
                                                'delete' => ['POST'],
                                            ],
                            ],
                'timestamp' => 
                            [
                            'class' => TimestampBehavior::class,
                            'attributes' => [
                                                ActiveRecord::EVENT_BEFORE_INSERT => ['modifieddate'],
                                                ActiveRecord::EVENT_BEFORE_UPDATE => ['modifieddate'],
                                            ],
                            ],
                'access' => 
                            [
                            'class' => \yii\filters\AccessControl::class,
                            'only' => ['create', 'update','view','delete','doit','subcatcost'],
                            'rules' => [
                            [
                              'allow' => true,
                              'verbs' => ['POST']
                            ],
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
        $searchModel = new Costsearch();
        $queryParams = Yii::$app->request->queryParams;
        $dataProvider = $searchModel->search($queryParams);
        $dataProvider->setSort([
            'attributes' => [
                'costnumber' => [
                    'asc' => ['works_cost.costnumber' => SORT_ASC],
                    'desc' => ['works_cost.costnumber' => SORT_DESC],
                    'default' => SORT_ASC,
                ],
            ],
            'defaultOrder' => [
              'costnumber' => SORT_ASC,
            ]
          ]); 
        
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    /**
     * @return Response|string
     */
    public function actionCreate()
    {
        $model = new Cost();
        $request = Yii::$app->request;
        $post = (array)$request->post();
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
     * @return Response|string
     */
    public function actionView(int $id) {
        $model=$this->findModel($id);
        if ($model->load((array)Yii::$app->request->post()) && $model->save()) {
            if (!empty(Yii::$app->session)) {
                Yii::$app->session->setFlash('kv-detail-success', Yii::t('app','Saved record successfully'));
            } else {
                $session = new Session;
                $session->open();
                $session->setFlash('kv-detail-success', Yii::t('app','Saved record successfully'));
            }   
            $id = $model->getId();
            return $this->redirect(['view', 'id'=> $id]);
        } else {
            return $this->render('view', ['model'=>$model]);
        }
    }
    
    /**
     * @return void
     */
    public function actionDelete() {
        $post = (array)Yii::$app->request->post();
        /**
         * @var int $post['id']
         */
        $id = $post['id'];
        try {
        if (Yii::$app->request->isAjax && isset($post['costdelete'])) {
            if ($this->findModel($id)->delete()) {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                Yii::$app->response->content = Json::encode([
                    'success' => true,
                    'messages' => [
                        'kv-detail-info' => Yii::t('app','The cost # ') . $id . Yii::t('app',' was successfully deleted. <a href="') . 
                            Url::to(['/cost/index']) . '" class="btn btn-sm btn-info">' .
                            '<i class="glyphicon glyphicon-hand-right"></i>  '. Yii::t('app','Click here'). '</a> '. Yii::t('app','to proceed.')
                    ]
                ]);
            } 
          }
       }
       catch (\Exception $e)
       {
                Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
                Yii::$app->response->content = Json::encode([
                    'success' => false,
                    'messages' => [
                        'kv-detail-error' => Yii::t('app','Cannot delete the cost # ') . $id . Yii::t('app',' It exists on Daily Cost')
                      . 'schedule(s) already. Delete this cost on these Daily Cost Schedules first please. Exception: ' .$e
                    ]
                ]);
       }
   } 
   
   /**
    * Purpose: Used to copy repeating costs from one daily clean to another dated daily clean
    * @return void
    */
   public function actionCopyit()
   {
      //corder is the dropdownbox specific date's (w59:cost/index) cost header id
      $cost_header_id = (array)Yii::$app->request->get('ccost');
      $keylist = (array)Yii::$app->request->get('keylist');
      //work through the costs that have been selected to be copied
      /**
       * @var int $key
       * @var string $value 
       */
      foreach ($keylist as $key => $value)
      {
        $model = Cost::findOne($value);
        if (null!==$model) {
            $session = new Session;
            $session->open();
            //prevent cost duplicates
            $q = new Query();
            if  ($q->select('*')->from('works_costdetail')->where(['cost_header_id' => $cost_header_id])->andWhere(['cost_id'=>$value])->exists()) 
                  {
                    $session->setFlash('kv-detail-success', $cost_header_id ); 
                    exit();
                  }
                  else {
            $model2 = new Costdetail();
            //the sales order id for the specific daily clean that we are copying to
            $model2->cost_header_id = $cost_header_id;
            $model2->paymenttype = "Cash";
            if ($model->frequency === "Daily")
            {
                    $date = strtotime("+1 day");
                    $addeddate = date("Y-m-d" , $date);
                    $model2->nextcost_date = $addeddate;
            };
            if ($model->frequency === "Weekly")
            {
                    $date = strtotime("+7 day");
                    $addeddate = date("Y-m-d" , $date);
                    $model2->nextcost_date = $addeddate;
            };
            if ($model->frequency === "Monthly")
                {
                   $date = strtotime("+30 day");
                   $addeddate = date("Y-m-d" , $date);
                   $model2->nextcost_date = $addeddate;
                };
            if ($model->frequency === "Fortnightly")
                {
                   $date = strtotime("+15 day");
                   $addeddate = date("Y-m-d" , $date);
                   $model2->nextcost_date = $addeddate;
                };
            if ($model->frequency === "Every two months")
                {
                   $date = strtotime("+60 day");
                   $addeddate = date("Y-m-d" , $date);
                   $model2->nextcost_date = $addeddate;
                }; 
            if ($model->frequency === "Other")
                {
                   $model2->nextcost_date = date("Y-m-d"); 
                };
            $model2->costcategory_id = $model->costcategory_id;
            $model2->costsubcategory_id = $model->costsubcategory_id;
            $model2->cost_id = $value;
            $model2->carousal_id = null;
            $model2->order_qty=1;
            $model2->unit_price = $model->listprice;
            $model2->line_total = $model2->unit_price;
            $model2->paid = 0;
            $model2->save();
            }
          } // null!==$model
        } // foreeach  
    }
    
    /**
     * @return string
     */
    public function actionSubcatcost() 
    {
    $out = [];
    if (isset($_POST['depdrop_parents'])) {
        /**
         * @var array|null $_POST['depdrop_parents']
         */
        $parents = $_POST['depdrop_parents'];
        if ($parents !== null) {
            /**
             * @var int $parents[0]
             */
            $cat_id = $parents[0];
            $out = self::getSubCatcostList($cat_id); 
            return Json::encode(['output'=>$out, 'selected'=>'']);
        }
    }
    return Json::encode(['output'=>$out, 'selected'=>'']);
    }

    /**
     * 
     * @param int $costcode_id
     * @return array
     */
    public static function getSubCatcostList(int $costcode_id) {
       //find all the costs in the subcost 
        $data=Costsubcategory::find()
       ->where(['costcategory_id'=>$costcode_id])
       ->select(['id','name AS name'])->asArray()->orderBy('name')->all();
       return $data;
    } 
    
    /**
     * @return string
     */
    public function actionCos() {
    $out = [];
    if (isset($_POST['depdrop_parents'])) {
        /**
         * @var array $_POST['depdrop_parents']
         */
        $ids = $_POST['depdrop_parents'];
        /**
         * @var int $ids[0]
         */
        $cat_id = empty($ids[0]) ? null : $ids[0];
        /**
         * @var int $ids[1]
         */
        $subcat_id = empty($ids[1]) ? null : $ids[1];
        if ($cat_id !== null && $subcat_id !==null) {
            $out = self::getCostListb($cat_id,$subcat_id); 
            return Json::encode(['output'=>$out, 'selected'=>'']);
        }
    }
    return Json::encode(['output'=>'', 'selected'=>'']);
    }
    
    /**
     * @param int $cat_id
     * @param int $subcat_id
     * @return array
     */
    public static function getCostListb(int $cat_id, int $subcat_id) {
        $data = [];
        $data=Cost::find()
       ->where(['costcategory_id'=>$cat_id])
       ->andWhere(['costsubcategory_id'=>$subcat_id])      
       ->select(['id', 'costnumber AS name'])->asArray()->all();
        return $data;
    }
   
   /**
    * @return string 
    */
   public function actionSlider()
   {
        $session = new Session;
        $session->open();
        /**
         * @psalm-suppress PossiblyInvalidCast 
         */
        $sliderfontcost = (string)Yii::$app->request->get('sliderfontcost');
        $session->set('sliderfontcost', $sliderfontcost);
        $font = (string)$session->get('sliderfontcost');
        return $font;
   }
    
   /**
    * @param int $id
    * @return Cost
    * @cls
    * throws NotFoundHttpException
    */
   protected function findModel(int $id)
   {
        if (($model = Cost::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
        }
   }
}
