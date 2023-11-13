<?php
declare(strict_types=1); 

namespace frontend\controllers;

use Yii;
use yii\db\IntegrityException;
use yii\web\NotFoundHttpException;
use frontend\models\Costheader;
use frontend\models\CostheaderSearch;
use frontend\models\Costdetail;
use frontend\models\Cost;
use yii\helpers\Json;
use yii\web\Controller;
use yii\web\Response;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
use yii\filters\VerbFilter;

class CostheaderController extends Controller
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
            'timestamp' => 
                [
                'class' =>TimestampBehavior::class,
                'attributes' => [
                                    ActiveRecord::EVENT_BEFORE_INSERT => ['modified_date'],
                                    ActiveRecord::EVENT_BEFORE_UPDATE => ['modified_date'],
                                ],
                ],
            'access' => 
                [
                'class' => \yii\filters\AccessControl::class,
                'only' => ['index','create', 'update','delete','copyticked'],
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
     * @return string 
     */
    public function actionIndex()
    {
        $searchModel = new CostheaderSearch();
        $queryParam = Yii::$app->request->queryParams ?: [];
        $dataProvider = $searchModel->search($queryParam);
        $dataProvider->setSort([
            'attributes' => [
                'cost_header_id' => [
                    'asc' => ['cost_header_id' => SORT_ASC],
                    'desc' => ['cost_header_id' => SORT_DESC],
                    'default' => SORT_DESC,
                ],
                'cost_date' => [
                    'asc' => ['cost_date' => SORT_ASC],
                    'desc' => ['cost_date' => SORT_DESC],
                    'default' => SORT_DESC,
                ],
            ],
            'defaultOrder' => [
                'cost_date' => SORT_DESC
            ]
        ]);
        if (Yii::$app->request->post('hasEditable')) {
            /**
             * @psalm-suppress RiskyCast
             */
            $costId = (int)Yii::$app->request->post('editableKey');
            $model = Costheader::findOne($costId);
            if ($model) {
                    $posted = (array)Yii::$app->request->post('Costheader');
                    $post = ['Costheader' => $posted];
                    if ($posted) {
                        // load model like any single model validation
                        if ($model->load($post)) {
                            // can save model or do something before saving model
                            $model->save();
                        }
                        // custom output to return to be displayed as the editable grid cell
                        // data. Normally this is empty - whereby whatever value is edited by
                        // in the input by user is updated automatically.
                        $output = '';
                        if (isset($posted['cost_date'])) {
                            $output = Yii::$app->formatter->asDate($model->getCost_date(),'php:Y-m-d');
                        }
                        return Json::encode(['output'=> $output, 'message'=>'']);
                    } // null !==$post
            }            
        }
        return $this->render('index', [
                    'searchModel' => $searchModel,
                    'dataProvider' => $dataProvider
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
     * 
     * @return Response|string
     */
    public function actionCreate()
    {
        $model = new Costheader();
        if ($model->load((array)Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->getCost_header_id()]);
        } else {
            return $this->render('create', [
                'model' => $model,
            ]);
        }
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
            return $this->redirect(['view', 'id' => $model->getCost_header_id()]);
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
	} catch(IntegrityException $e) {
              //Yii::warning('Delete cleans first under this clean.'); 
              throw new \yii\web\HttpException(404, Yii::t('app','You have individual costs on the detail which you must delete first. Click on costs and delete all of the individual costs. Exception: ').$e);
        }
    }
    
    /**
     * @return void
     * @param int $id
     */
    public function actionCopyticked(int $id)
    {
      $keylist = (array)Yii::$app->request->get('keylist');
      $addeddate = date('Y-m-d');
      /**
       * @var int $key
       * @var int $value
       */
      foreach ($keylist as $key => $value)
      {
       //find the record of the $value checked item
       $costheader = Costheader::findOne($value);
       if (null!==$costheader) {
                $costdetails = $costheader->getCostdetails();
                $model = new Costheader();
                $model->setStatus($costheader->getStatus());
                $model->setStatusfile($costheader->getStatusfile());
                $model->setEmployee_id($costheader->getEmployee_id());
                 if ($id == 1) 
                 { 
                     $model->setCost_date(date('Y-m-d'));
                 }
                 if ($id == 2)
                 { 
                     $date = $costheader->getCost_date();
                     $addeddate = date('Y-m-d', strtotime($date. ' + 31 days'));
                     $model->setCost_date($addeddate);
                 }
                $model->setSub_total(0.00);
                $model->setTax_amt(0.00);
                $model->setTotal_due(0.00);
                //save the record to generate a new sales order id
                $model->save();
                //find the last record's sales_order_id just saved
                //Yii::$app->session['salesoid'] = $model->sales_order_id;
                /**
                 * @psalm-suppress RawObjectIteration
                 * @var Costdetail $costdetail
                 */
                foreach ($costdetails as $costdetail)
                {
                    $model3= new Costdetail();
                    $model3->setCost_header_id($model->getCost_header_id());
                    $cost_id = $costdetail->getCost_id();
                    $found = Cost::findOne($cost_id) ?: null;
                    if ($found) {
                        if ($found->getFrequency() === "Daily")
                         {
                             $date = strtotime("+1 day");
                             $addeddate = date("Y-m-d" , $date);
                             $model3->setNextcost_date($addeddate);
                         }
                        if ($found->getFrequency() === "Weekly")
                         {
                             $date = strtotime("+7 day");
                             $addeddate = date("Y-m-d" , $date);
                             $model3->setNextcost_date($addeddate);
                         }
                         if ($found->getFrequency() === "Monthly")
                         {
                            $date = strtotime("+30 day");
                            $addeddate = date("Y-m-d" , $date);
                            $model3->setNextcost_date($addeddate);
                         };
                         if ($found->getFrequency() === "Fortnightly")
                         {
                            $date = strtotime("+15 day");
                            $addeddate = date("Y-m-d" , $date);
                            $model3->setNextcost_date($addeddate);
                         }
                         if ($found->getFrequency() === "Every two months")
                         {
                            $date = strtotime("+60 day");
                            $addeddate = date("Y-m-d" , $date);
                            $model3->setNextcost_date($addeddate);
                         }
                        if ($found->getFrequency() === "Other")
                         {
                            $model3->getNextcost_date(); 
                         }
                        $model3->setCostcategory_id($costdetail->getCostcategory_id());
                        $model3->setCostsubcategory_id($costdetail->getCostsubcategory_id());
                        $model3->setCost_id($costdetail->getCost_id());
                        $model3->setOrder_qty($costdetail->getOrder_qty());
                        $model3->setUnit_price($costdetail->getUnit_price());
                        $model3->setLine_total($costdetail->getLine_total());
                        $model3->setPaid(0.00);
                        $model3->save();
                    } // null!==$found    
                } //foreach costdetails
            } //costheader
        } // keylist
        /**
         * @psalm-suppress UndefinedMagicPropertyFetch Yii::$app->session
         */
        Yii::$app->session->setFlash('success', Yii::t('app','Those costs that were checked or ticked have been copied to Costs: {0}') .$addeddate);
    }
    
    /**
     * @param int $id
     * @return string
     */
    public function actionTotalannualcost(int $id)
    {
        $costyear = $id;
        $months = [];
        $grandduetotal = 0.00;
        $grandpaidtotal = 0.00;
        $grandtotal = 0.00;
        //             Month               Due              Paid            Tips        Advpyt       Prepyt        Total
        $months[0][0]= "Jan";$months[0][1]= 0;$months[0][2]= 0;$months[0][3]= 0;$months[0][4]= 0;$months[0][5]= 0;$months[0][6]= 0;
        $months[1][0]= "Feb";$months[1][1]= 0;$months[1][2]=0;$months[1][3]= 0;$months[1][4]= 0;$months[1][5]= 0;$months[1][6]= 0;
        $months[2][0]= "March";$months[2][1]= 0;$months[2][2]= 0;$months[2][3]= 0;$months[2][4]= 0;$months[2][5]= 0;$months[2][6]= 0;
        $months[3][0]= "April";$months[3][1]= 0;$months[3][2]= 0;$months[3][3]= 0;$months[3][4]= 0;$months[3][5]= 0;$months[3][6]= 0;
        $months[4][0]= "May";$months[4][1]= 0;$months[4][2]= 0;$months[4][3]= 0;$months[4][4]= 0;$months[4][5]= 0;$months[4][6]= 0;
        $months[5][0]= "June";$months[5][1]= 0;$months[5][2]= 0;$months[5][3]= 0;$months[5][4]= 0;$months[5][5]= 0;$months[5][6]= 0;
        $months[6][0]= "July";$months[6][1]= 0;$months[6][2]= 0;$months[6][3]= 0;$months[6][4]= 0;$months[6][5]= 0;$months[6][6]= 0;
        $months[7][0]= "August";$months[7][1]= 0;$months[7][2]= 0;$months[7][3]= 0;$months[7][4]= 0;$months[7][5]= 0;$months[7][6]= 0;
        $months[8][0]= "September";$months[8][1]= 0;$months[8][2]= 0;$months[8][3]= 0;$months[8][4]= 0;$months[8][5]= 0;$months[8][6]= 0;
        $months[9][0]= "October";$months[9][1]= 0;$months[9][2]= 0;$months[9][3]= 0;$months[9][4]= 0;$months[9][5]= 0;$months[9][6]= 0;
        $months[10][0]= "November";$months[10][1]= 0;$months[10][2]= 0;$months[10][3]= 0;$months[10][4]= 0;$months[10][5]= 0;$months[10][6]= 0;
        $months[11][0]= "December";$months[11][1]= 0;$months[11][2]= 0;$months[11][3]= 0;$months[11][4]= 0;$months[11][5]= 0;$months[11][6]= 0;
        $months[12][0]= "Total";$months[12][1]= 0;$months[12][2]= 0;$months[12][3]= 0;$months[12][4]= 0;$months[12][5]= 0;$months[12][6]= 0;
        $i=0;
        $j=$i+1;
        while ($i <= 11){
            $monthlycosts = Costheader::find()
                   ->where(['<=','cost_date',"$costyear"."-".$j."-"."31"])
                   ->andFilterWhere(['>=','cost_date',"$costyear"."-".$j."-"."01"])
                   ->orderBy('cost_date')
                   ->all();                  
            $totalamount = 0.00;
            $totalpaid = 0.00;
            $totalall = 0.00;
            /**
             * @var int $key
             */
            foreach ($monthlycosts as $key => $value)
            {
                /**
                 * @var int $key['cost_header_id']
                 */
                $cost_header_id = $key['cost_header_id']; 
                $result = Costdetail::find()->where(['cost_header_id'=>$cost_header_id])->all();
                 /**
                  * @var int $key
                  * @var int $value
                  */
                 foreach ($result as $key => $value)
                 {
                    /**
                     * @var float $result[$key]['paid']
                     */
                    $totalpaid = $totalpaid + $result[$key]['paid'];
                    /**
                     * @var float $result[$key]['unit_price']
                     */
                    $totalamount = $totalamount + $result[$key]['unit_price'];
                    //////$totaltips = $totaltips + $result[$key]['tip'];
                    //////$totaladvpyts = $totaladvpyts + $result[$key]['advance_payment'];
                    //////$totalprepyts = $totalprepyts + $result[$key]['pre_payment'];
                 }
                 $totalall = $totalpaid;
             }
             //total due row
             $months[$i][1] = number_format($totalamount,2);
             //total paid row
             $months[$i][2] = number_format($totalpaid,2);
            //total tips row
            //////$months[$i][3] = number_format($totaltips,2);
            //total advance payments row
            //////$months[$i][4] = number_format($totaladvpyts,2);
            //total pre payment row
            //////$months[$i][5] = number_format($totalprepyts,2);

             //fill last row 6 at bottom of table with monthly totals
             $months[$i][6] = number_format($totalall,2);
             $i++;
             $j=$i+1;
             $grandduetotal = $grandduetotal + $totalamount;
             $grandpaidtotal = $grandpaidtotal + $totalpaid;
                    //////$grandtipstotal = $grandtipstotal + $totaltips;
                    //////$grandadvtotal = $grandadvtotal + $totaladvpyts;
                    //////$grandpretotal = $grandpretotal + $totalprepyts;
                    //////$grandtotal = $grandpaidtotal+$grandtipstotal+$grandadvtotal+$grandpretotal;
             $grandtotal = $grandpaidtotal;
        }
        //total column at far right of table
        $months[12][1] = number_format($grandduetotal,2);
        $months[12][2] = number_format($grandpaidtotal,2);
        //////$months[12][3] = number_format($grandtipstotal,2);
        //////$months[12][4] = number_format($grandadvtotal,2);
        //////$months[12][5] = number_format($grandpretotal,2);
        $months[12][6] = number_format($grandtotal,2);
        return $this->render('totalannualcost',['months'=>$months,'year'=>$costyear]);
    }
    
    /**
     * @return void
     */
    public function actionTotalmonthlyexpenditure()
    {
       /**
        * @psalm-suppress RiskyCast 
        */
       $costmonth = (int)Yii::$app->request->get('costmonth');
       /**
        * @psalm-suppress RiskyCast 
        */
       $costyear = (int)Yii::$app->request->get('costyear');
       $monthadd = $costmonth + 1;
       $costyearcompare = $costyear;
       If ($monthadd === 13) {
           $monthadd = 1;
           $costyearcompare = $costyearcompare + 1;
       } 
       //filter the dates out for the month selected
       $monthlycosts = Costheader::find()
       ->where(['<=','cost_date', $costyearcompare."-".$monthadd."-"."01"])
       ->andFilterWhere(['>=','cost_date', $costyear."-".$costmonth."-"."01"])
       ->orderBy('cost_date')
       ->all();
       
       $totalamount = 0.00;
       $totalpaid = 0.00;
       foreach ($monthlycosts as $key)
      {
            /**
             * @var int $key['cost_header_id']
             */
            $result = Costdetail::find()->where(['cost_header_id'=>$key['cost_header_id']])->all();
            foreach ($result as $key => $value)
            {
                /**
                 * @var float $result[$key]['paid']
                 */
                $totalpaid = $totalpaid + $result[$key]['paid'];
                /**
                 * @var float $result[$key]['unit_price']
                 */
                $totalamount = $totalamount + $result[$key]['unit_price'];
            }
      }
      /**
       * @psalm-suppress UndefinedMagicPropertyFetch Yii::$app->session
       */
      Yii::$app->session->setFlash('success', Yii::t('app',"Expenditure: {0}/{1}: Amount:", [$costmonth,$costyear]).number_format($totalamount,2). Yii::t('app',' Paid: ').number_format($totalpaid,2));
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
        $sliderfontcostdetail = (string)Yii::$app->request->get('sliderfontcostheader');
        $session_slider->set('sliderfontcostheader', $sliderfontcostdetail);
        $font = (string)$session_slider->get('sliderfontcostheader');
        return $font;
   }
   
   /**
    * @param int $id
    * @return Costheader
    * @throws NotFoundHttpException
    */
   protected function findModel(int $id)
   {
        if (($model = Costheader::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException(Yii::t('app','The requested page does not exist.'));
        }
   }
}
