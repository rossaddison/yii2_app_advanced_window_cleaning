<?php

namespace frontend\controllers;

use Yii;
use frontend\models\Salesorderheader;
use frontend\models\Salesorderdetail;
use frontend\models\SalesinvoiceAmount;
use frontend\models\Product;
use frontend\models\Company;
use frontend\components\Utilities;
use frontend\models\Messagelog;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\db\IntegrityException;
use frontend\models\SalesorderdetailSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\db\Query;
use frontend\models\Gocardlessinvoice;


class SalesorderdetailController extends Controller
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
                            [
                              'allow' => true,
                              'verbs' => ['POST']
                            ],  
                            ],
            ], 
        ];
    }

    
    public function actionIndex($id)
    {
     Yii::$app->session['sales_order_id'] = $id;
     $searchModel = new SalesorderdetailSearch();
     $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
            
     if (Yii::$app->request->post('hasEditable')) {
        $salesorderId = Yii::$app->request->post('editableKey');
        $model = Salesorderdetail::findOne($salesorderId);
        $posted = current($_POST['Salesorderdetail']);
        $post = ['Salesorderdetail' => $posted];
        if ($model->load($post)) {
        $model->save();
        }
        $output = '';
        if (isset($posted['unit_price'])) {
          $output = Yii::$app->formatter->asDecimal($model->unit_price, 2);
        }
        if (isset($posted['paid'])) {
          $output = Yii::$app->formatter->asDecimal($model->paid, 2);
        }
        if (isset($posted['advance_payment'])) {
          $output = Yii::$app->formatter->asDecimal($model->advance_payment, 2);
        }
        if (isset($posted['tip'])) {
          $output = Yii::$app->formatter->asDecimal($model->tip, 2);
        }
        return Json::encode(['output'=>$output, 'message'=>'']);
     }
        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }
    
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['view', 'id' => $model->sales_order_detail_id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    
    public function actionView($id)
    {
        
        return $this->render('view', [
            'model' => $this->findModel($id),
        ]);
    }

   
    public function actionCreate()
    {
        $model = new Salesorderdetail();
        $model->sales_order_id = Yii::$app->session['sales_order_id'];
        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            return $this->redirect(['index', 'id' => $model->sales_order_id]);
        } else {
            return $this->render('create', [
                'model' => $model,'sales_order_id'=> Yii::$app->session['sales_order_id']
            ]);
        }
    }
   
    public function actionDelete($id)
    {
        try {
            $model = $this->findModel($id);
	    $this->findModel($id)->delete();            
            return $this->redirect(['index','id'=>$model->sales_order_id]);
	} catch(IntegrityException $e) {              
              throw new \yii\web\HttpException(404, Yii::t('app','Integrity Constraint exception. This clean is connected to an invoice. An invoice cannot be deleted. Reduce the asking price to zero.'));
        }
    }
    
    public function actionPaidticked()
    {
      $keylist = Yii::$app->request->get('keylist');
      if (!empty($keylist)){
      foreach ($keylist as $key => $value)
      {
                    $model = $this->findModel($value);
                    if ($model !== null) {
                        $model->paid = $model->unit_price;
                        $model->cleaned = "Cleaned";
                         if (!empty($model->invoice_id)){
                            $id = $model->invoice_id;
                            $salesinvoiceamount = SalesinvoiceAmount::findOne($id);
                            $salesinvoiceamount->invoice_balance = $salesinvoiceamount->invoice_total-$model->paid;
                            $salesinvoiceamount->save();
                        }
                        $model->save();
                    }
      }
      return;
      }
      else {throw new NotFoundHttpException(Yii::t('app','No ticks selected.'));}
      return;
    }
    
    public function actionUnpaidticked()
   {
      $keylist = Yii::$app->request->get('keylist');
      if (!empty($keylist)){
      foreach ($keylist as $key => $value)
      {
                    $model = $this->findModel($value);
                    if ($model !== null) {
                        $model->paid = 0;
                        if (!empty($model->invoice_id)){
                            $invoice_id = $model->invoice_id;
                            $salesinvoiceamount = SalesinvoiceAmount::find()->where(['=','invoice_id',$invoice_id])->one();
                            $salesinvoiceamount->invoice_balance = $salesinvoiceamount->invoice_total-$model->paid;
                            $salesinvoiceamount->save();
                        }
                        $model->save();
                    }
      }
     
      return;
      }
      else {throw new NotFoundHttpException('No ticks selected.');}
      return;
    }
    
    public function actionPay($id)
    {
        $model = $this->findModel($id);
        if ($model !== null) {
           //if an invoice has already been created then prepare to create a payment towards it
           if (!empty($model->invoice_id)){
               //enter the amount, date, and method of payment of the invoice
               Yii::$app->session['salesinvoicepayment_invoiceid'] = $model->invoice_id;
               Yii::$app->session['salesinvoicepayment_salesorderdetail_id'] = $model->sales_order_detail_id;
               Yii::$app->session['salesinvoicepayment_unitprice'] = $model->unit_price;
               //the following create process will create a payment using the session variables above
               //and assign the resultant payment_id to the sales order detail table
               //and also mark the sales order detail table as paid.
               return $this->redirect(['salesinvoicepayment/create']);
           }
           //if there is no invoice simply mark the clean as paid. There is no need to go through the invoice system.
           $model->paid = $model->unit_price;
           $model->save();
        }
        return $this->redirect(['view', 'id' => $model->sales_order_detail_id]);
    }
    
    public function actionUnpay($id)
    {
        $model = $this->findModel($id);
        if ($model !== null) {
            if (!empty($model->invoice_id)){
               Yii::$app->session['salesinvoicepayment_invoiceid'] = $model->invoice_id;
               //the payment_id was recorded in the above pay method so use it now to update the relevant payment.
               return $this->redirect(['salesinvoicepayment/update','id'=>$model->payment_id]);
           }
           $model->paid = 0;
           $model->save();
        }
        return $this->redirect(['view', 'id' => $model->sales_order_detail_id]);
    }
       
    public function actionDeleteticked()
   {
      $keylist = Yii::$app->request->get('keylist');
      if (!empty($keylist)){
      foreach ($keylist as $key => $value)
      {
                    $model = $this->findModel($value);
                    if ($model !== null) {
                        try{
                        $model->delete();
                        } catch(IntegrityException $e) {              
                            throw new \yii\web\HttpException(404, Yii::t('app','Integrity Constraint exception. This clean is connected to an invoice. An invoice cannot be deleted. Reduce the asking price to zero.'));
                        }
                    }
                    
      } 
      }
      else {throw new NotFoundHttpException(Yii::t('app','No ticks selected.'));}
      return;
    }
    
    public function actionCleanedticked()
    {
      $keylist = Yii::$app->request->get('keylist');
      if (!empty($keylist)){
      foreach ($keylist as $key => $value)
      {
                    $model = $this->findModel($value);
                    if ($model !== null) {
                        $model->cleaned = "Cleaned";
                        $model->save();
                    }
      }
      }
      else {throw new NotFoundHttpException(Yii::t('app','No ticks selected.'));}
      return;
    }
    
     public function actionNotcleanedticked()
    {
      $keylist = Yii::$app->request->get('keylist');
      if (!empty($keylist)){
      foreach ($keylist as $key => $value)
      {
                    $model = $this->findModel($value);
                    if ($model !== null) {
                        $model->cleaned = "Not Cleaned";
                        $model->save();
                    }
      }
      }
      else {throw new NotFoundHttpException(Yii::t('app','No ticks selected.'));}
      return;
    }
    
     public function actionMissedticked()
     {
      $keylist = Yii::$app->request->get('keylist');
      if (!empty($keylist)){
      foreach ($keylist as $key => $value)
      {
                    $model = $this->findModel($value);
                    if ($model !== null) {
                        $model->cleaned = "Missed";
                        $model->save();
                    }
      }
      }
      else {throw new NotFoundHttpException(Yii::t('app','No ticks selected.'));}
      return;
    }
    
    public function actionTransferticked()
    {
      $keylist = Yii::$app->request->get('keylist');
      //$transadv assigned dropdownbox sales order id value
      $transadv = Yii::$app->request->get('transadv');
      if (!empty($keylist))
      {
        foreach ($keylist as $key => $value)
        {
                    $model = $this->findModel($value); 
                    if ($model !== null) {
                        $prod = $model->product_id;
                        $amount = $model->advance_payment;
                        $model->advance_payment = 0;
                       //transfer current advance payment to future prepayment
                        Utilities::soi2soi($prod,$transadv,$amount);
                       $model->save();
                    }
        }
         
         return;
      }     
      
      else {throw new NotFoundHttpException(Yii::t('app','No ticks selected.'));}
      return;
    }
   
    public function actionAddpretopaidticked()
    {
      $keylist = Yii::$app->request->get('keylist');
      if (!empty($keylist))
      {
        foreach ($keylist as $key => $value)
        {
                    $model = $this->findModel($value); 
                    if ($model !== null) {
                        $amount = $model->pre_payment;
                        $paid = $model->paid;
                        $model->paid = $paid + $amount;
                        $model->pre_payment = 0;
                        $model->save();
                    }
        }
      }     
      
      else {throw new NotFoundHttpException(Yii::t('app','No ticks selected.'));} 
      return;
    }
    
    //use twilio to text the amount owing to a customer
    public function actionOwingticked()
    {
      $keylist = Yii::$app->request->get('keylist');
      $message_text = Yii::$app->request->get('sdmessage');
      if (!empty($keylist)){
      foreach ($keylist as $key => $value)
      {
                    $model = $this->findModel($value);
                    if (($model !== null) & (!empty($model->product->contactmobile) & (strlen($model->product->contactmobile)===11))) 
                        {
                       $twilioService = Yii::$app->Yii2Twilio->initTwilio();
                        try {
                            $q = new Query;
                            $rows = $q->select('*')
                            ->from('works_salesorderdetail')
                            ->where('product_id=:product_id',['and',':product_id='.$model->product_id,'paid=0.00'])
                            ->andWhere('cleaned="Cleaned"')
                            ->andWhere('sales_order_detail_id<='.$model->sales_order_detail_id)
                            ->all();
                            $subtotal = 0.00;
                            $pay = "";
                            foreach ($rows as $key => $value)
                            {
                              $subtotal += $rows[$key]['unit_price'];
                              $val = $rows[$key]['sales_order_id'];
                              $cleandate = Salesorderheader::findOne($val);
                              $date = Yii::t('app','Clean date: ') . $cleandate->clean_date;
                              $owed = Yii::t('app',' Owing:') . $rows[$key]['unit_price']; 
                              $pay = $pay ." ".$date. $owed;                                
                            }
                            $pay = $pay. ' '.$subtotal. Yii::t('app',' payment appreciated. Reference: ').$model->product->name.Yii::t('app',' Please reply -- paid -- to this text once payment has been made.');
                            If ($subtotal > 0) {} else $pay = "";
                            date_default_timezone_set("Europe/London");
                            $date = date('d/m/Y h:i:s a', time());
                            $completemessage = $date. Yii::t('app',' Hi ').$model->product->name .", ". $message_text. " " .$pay;
                            $message = $twilioService->account->messages->create(
                            Yii::$app->params['DialingCodeRestriction'] .substr($model->product->contactmobile,1), // To a number that you want to send sms
                            array(
                                "from" =>  Company::findOne(1)->twilio_telephone,   // eg. "+441315103755" From a number that you are sending
                                "body" => $completemessage, 
                            ));
                           } catch (\Twilio\Exceptions\RestException $e) {
                                echo $e->getMessage();
                           }                            
                    }                  
      }
      }
      if (!empty($keylist)){
      foreach ($keylist as $key => $value)
          {                     
            $model = $this->findModel($value); 
            if (($model !== null) & (!empty($model->product->contactmobile) & (strlen($model->product->contactmobile)===11))) 
             {

                 $date = date('d/m/Y h:i:s a', time());
                 $completemessage = $date. " Hi ".$model->product->name .", ". $message_text. " ";
                 $model2 = new Messagelog();
                 $model2->message = $completemessage;
                 $model2->date = date('Y-m-d');
                 $model2->phoneto = $model->product->contactmobile;
                 $model2->salesorderdetail_id = $model->sales_order_detail_id;
                 $model2->product_id = $model->product_id; 
                 $model2->save();
             }
         }
     } else {throw new NotFoundHttpException(Yii::t('app','No ticks selected.'));}
     return;
     
    }
        
    public function actionTakeoneoffpayment()
    {
        $comp = Company::findOne(1);
        $keylist = Yii::$app->request->get('keylist');
        $message = "";
        $source = Url::to('@web/images/gocardless.png');
        if (!empty($keylist)){
            foreach ($keylist as $key => $value)
            {
                $model = $this->findModel($value);
                if (($model !== null) & (!empty($model->product->email)) & (!empty($model->product->mandate))) 
                {
                    $q = new Query;
                    $rows = $q->select('*')
                    ->from('works_salesorderdetail')
                    ->where('product_id=:product_id',['and',':product_id='.$model->product_id,'paid=0.00'])
                    ->andWhere('cleaned="Cleaned"')
                    ->andWhere('sales_order_detail_id<='.$model->sales_order_detail_id)
                    ->all();
                    $subtotal = 0.00;
                    foreach ($rows as $key => $value)
                    {
                      $subtotal += $rows[$key]['unit_price'];                                                      
                    }
                    //add the current clean to the subtotal of previous cleans to give the complete total
                    $totalcleanamount = $subtotal + $model->unit_price;
                    $client = new \GoCardlessPro\Client([
                    'access_token' => $comp->gc_accesstoken,
                    'environment' => $comp->gc_live_or_sandbox == 'SANDBOX' ? \GoCardlessPro\Environment::SANDBOX : \GoCardlessPro\Environment::LIVE ,
                    ]);
                    $payment = $client->payments()->create([
                    "params" => [
                        "amount" => $totalcleanamount*100, // 10 GBP in pence
                        "currency" => Yii::$formatter->currencyCode,
                        "links" => [
                            "mandate" => $model->product->mandate,
                        ],
                        // Almost all resources in the API let you store custom metadata,
                        // which you can retrieve later
                        //put in an invoice number
                        "metadata" => [
                            "invoice_number" => "INV".$model->sales_order_detail_id,
                        ]
                    ],
                    "headers" => [
                        "Idempotency-Key" => "random_payment_specific_string"
                    ]
                  ]);
                    $model2 = new Gocardlessinvoice();
                    $model2->invoicenumber = "INV".$model->sales_order_detail_id;
                    $model2->product_id = $model->product_id;
                    $model2->date = date('Y-m-d');
                    $model2->amount = $totalcleanamount;
                    $model2->payment_id = $payment->id;
                    $model2->save();
             } //if (($model !== null) & (!empty($model->product->email)) & (!empty($model->product->mandate))) 
          }//foreach ($keylist as $key => $value)                                
        } // if (!empty($keylist))
        else {throw new NotFoundHttpException(Yii::t('app','Exception: Either No ticks selected, no email of householder, or no direct debit mandate from householder.'));} //if (($model !== null) & (!empty($model->product->email))) 
        return;
    }
    
    public function actionCopyticked($id)
   {
       $model2 = Salesorderheader::findOne(Yii::$app->session['sales_order_id']);
       $salesorderdetails = $model2->salesorderdetails;
       $model = new Salesorderheader();
       $model->status = $model2->status;
       $model->statusfile = $model2->statusfile;
       $model->employee_id = $model2->employee_id;
        if ($id == 1) { $model->clean_date = date('Y-m-d');}
        if ($id == 2)
        { 
            $date = $model2->clean_date;
            $addeddate = date('Y-m-d', strtotime($date. ' + 31 days'));
            $model->clean_date = $addeddate;
        }
       $model->sub_total = 0;
       $model->tax_amt=0;
       $model->total_due=0;
       $model->save();
       foreach ($salesorderdetails as $key => $value)
       {
           $model3= new Salesorderdetail();
           $model3->sales_order_id = $model->sales_order_id;
           $model3->cleaned = "Not Cleaned";
           $product_id = $salesorderdetails[$key]['product_id'];
           $found = Product::find()->where(['id'=>$product_id])->one();
           if ($found->frequency == "Weekly")
            {
                    $date = strtotime("+7 day");
                    $addeddate = date("Y-m-d" , $date);
                    $model3->nextclean_date = $addeddate;
            };
            if ($found->frequency == "Monthly")
                {
                   $date = strtotime("+30 day");
                   $addeddate = date("Y-m-d" , $date);
                   $model3->nextclean_date = $addeddate;
                };
            if ($found->frequency == "Fortnightly")
                {
                   $date = strtotime("+15 day");
                   $addeddate = date("Y-m-d" , $date);
                   $model3->nextclean_date = $addeddate;
                };
            if ($found->frequency == "Every two months")
                {
                   $date = strtotime("+60 day");
                   $addeddate = date("Y-m-d" , $date);
                   $model3->nextclean_date = $addeddate;
                }; 
           if ($found->frequency == "Not applicable")
                {
                   $model3->nextclean_date = date("Y-m-d"); 
                };          
           $model3->productcategory_id = $salesorderdetails[$key]['productcategory_id'];
           $model3->productsubcategory_id =$salesorderdetails[$key]['productsubcategory_id'];
           $model3->product_id =$salesorderdetails[$key]['product_id'];
           $model3->unit_price =$salesorderdetails[$key]['unit_price'];
           $model3->paid =0;
           $model3->save();
       } 
     Yii::$app->session->setFlash('success',Yii::t('app','This daily clean has been copied. Modify the date as necessary later.'));
     return;
    }
    
    public function actionSlider()
   {
        Yii::$app->session['sliderfontsalesdetail'] = Yii::$app->request->get('sliderfontsalesdetail');    
   }
    
    protected function findModel($id)
    {
        if (($model = Salesorderdetail::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('SalesorderdetailController: '.Yii::t('app', 'The requested model does not exist.'));
        }
    }
}
