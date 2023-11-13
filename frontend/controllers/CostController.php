<?php
declare(strict_types=1); 

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
                            'only' => ['create', 'update','view','delete','copyit','subcatcost'],
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
        $cost = new Cost();
        $request = Yii::$app->request;
        $post = (array)$request->post();
        if ($cost->load($post) && $cost->save()) {
            $id = $cost->getId();
            return $this->redirect(['view', 'id' => $id]);
        } else {
            return $this->render('create', [
                'model' => $cost,
            ]);
        }
    }
    
    /**
     * @return Response|string
     */
    public function actionView(int $id) {
        $cost = $this->findModel($id);
        if ($cost->load((array)Yii::$app->request->post()) && $cost->save()) {
            if (!empty(Yii::$app->session)) {
                Yii::$app->session->setFlash('kv-detail-success', Yii::t('app','Saved record successfully'));
            } else {
                $session = new Session;
                $session->open();
                $session->setFlash('kv-detail-success', Yii::t('app','Saved record successfully'));
            }   
            $id = $cost->getId();
            return $this->redirect(['view', 'id'=> $id]);
        } else {
            return $this->render('view', ['model'=>$cost]);
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
   public function actionDoit()
   {
     /**
      * @psalm-suppress UndefinedMagicPropertyFetch Yii::$app->session
      */
      $session = Yii::$app->session;
      if ($session->isActive) {
          $session->open(); 
      } else {
          $session = new Session();
      }
      //prevent cost duplicates
      //corder is the dropdownbox specific date's (w59:cost/index) cost header id
      /**
       * @psalm-suppress RiskyCast
       */ 
      $cost_header_id = (int)Yii::$app->request->get('ccost');
      $keylist = (array)Yii::$app->request->get('keylist');
      //work through the costs that have been selected to be copied
      /**
       * @var int $key
       * @var int $value 
       */
      foreach ($keylist as $key => $value)
      {
        $cost = Cost::findOne($value);
        if ($cost) {            
            $q = new Query();
            if  ($q->select('*')->from('works_costdetail')
                                ->where(['cost_header_id' => $cost_header_id])
                                ->andWhere(['cost_id'=>$value])->exists()) 
            {
                $session->setFlash('kv-detail-success', $cost_header_id ); 
                exit();
            }
            else {
            $costdetail = new Costdetail();
            //the sales order id for the specific daily clean that we are copying to
            $costdetail->setCost_header_id($cost_header_id);
            $costdetail->setPaymenttype("Cash");
            $costdetail->setPaymentreference(null);
            $costdetail->setNextcost_date($this->frequency($cost->getFrequency()));
            $costdetail->setCostcategory_id($cost->getCostcategory_id());
            $costdetail->setCostsubcategory_id($cost->getCostsubcategory_id());
            /**
             * @psalm-suppress RedundantCastGivenDocblockType (int)$value
             */
            $costdetail->setCost_id((int)$value);
            $costdetail->setCarousal_id(null);
            $costdetail->setOrder_qty(1);
            $costdetail->setUnit_price((float)$cost->getListprice());
            $costdetail->setLine_total($costdetail->getUnit_price());
            $costdetail->setPaid(0.00);
            $costdetail->save() ? $session->setFlash('info','The costs have been copied across.') 
                            : $session->setFlash('info','The costs have NOT been copied across.');
            }
          } // null!==$cost
        } // foreeach  
    }
    
    /**
     * @param string $frequency
     * @return string
     */
    private function frequency(string $frequency) 
    {
        $addeddate= date("Y-m-d");
        if ($frequency === "Daily")
        {
                $date = strtotime("+1 day");
                $addeddate = date("Y-m-d" , $date);
        }
        if ($frequency === "Weekly")
        {
                $date = strtotime("+7 day");
                $addeddate = date("Y-m-d" , $date);
        }
        if ($frequency === "Monthly")
        {
               $date = strtotime("+30 day");
               $addeddate = date("Y-m-d" , $date);
        }
        if ($frequency === "Fortnightly")
        {
               $date = strtotime("+15 day");
               $addeddate = date("Y-m-d" , $date);
        }
        if ($frequency === "Every two months")
        {
               $date = strtotime("+60 day");
               $addeddate = date("Y-m-d" , $date);
        } 
        if ($frequency === "Other")
        {
               $addeddate = date("Y-m-d"); 
        }
        return $addeddate;
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
            $out = self::getSubCatcostList((string)$cat_id); 
            return Json::encode(['output'=>$out, 'selected'=>'']);
        }
    }
    return Json::encode(['output'=>$out, 'selected'=>'']);
    }

    /**
     * 
     * @param string $costcode_id
     * @return array
     */
    public static function getSubCatcostList(string $costcode_id) {
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
       $data = Cost::find()
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
        $sliderfontcostdetail = (string)Yii::$app->request->get('sliderfontcost');
        $session_slider->set('sliderfontcost', $sliderfontcostdetail);
        $font = (string)$session_slider->get('sliderfontcost');
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
        if (($cost = Cost::findOne($id)) !== null) {
            return $cost;
        } else {
            throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
        }
   }
}
