<?php
namespace frontend\controllers;

use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use frontend\models\Company;
use frontend\models\Sessiondetail;
use yii\helpers\Json;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;

class SiteController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['signup'],
                'rules' => [
                    [
                        //only an administrator can signup future observers a.k.a clients
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['admin'],
                    ],
                ],
            ],
            'verbs' => [
                'class' =>VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }
        
    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

                return $this->goHome();
            }

            Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
        }

        return $this->render('requestPasswordResetToken', [
            'model' => $model,
        ]);
    }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
            Yii::$app->session->setFlash('success', 'New password saved.');

            return $this->goHome();
        }

        return $this->render('resetPassword', [
            'model' => $model,
        ]);
    }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if (($user = $model->verifyEmail()) && Yii::$app->user->login($user)) {
            Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }

    public function actionMaintenance()
    {
      return $this->render('maintenance');
    }

    public function actionAbout()
    {
        return $this->render('about');
    }
    
    public function actionGocardlesscustomercreated($redirect_flow_id)
    {
        //customer passback retrieve details
        $redirectflowid = Sessiondetail::find()->where('redirect_flow_id=:redirect_flow_id',['=',':redirect_flow_id',$redirect_flow_id])->one();
        $redirectflowid->customer_approved = 1;
        $redirectflowid->save();
        return $this->render('gocardlesscustomercreated');
    }
    
    public static function getSubCatList($postalcode_id) {
       //find all the streets in the postal area 
        $data=\frontend\models\Productsubcategory::find()
       ->where(['productcategory_id'=>$postalcode_id])
       ->select(['id','name AS name'])->asArray()->orderBy('name')->all();
       return $data;
       
    }
    
    public static function getSubCatCostList($postalcode_id) {
       //find all the streets in the postal area 
        $data=\frontend\models\Costsubcategory::find()
       ->where(['costcategory_id'=>$postalcode_id])
       ->select(['id','name AS name'])->asArray()->orderBy('name')->all();
       return $data;       
    }
    
    public static function getSubCatListb($postalcode_id) {
       //find all the streets in the postal area 
        $data=\frontend\models\Productsubcategory::find()
       ->where(['productcategory_id'=>$postalcode_id])
       ->select(['id'])->asArray()->all();
       return $data;
       
    }
    
    public static function getProdList($cat_id, $sub_id) {
        
        $data = [];
        $data=\frontend\models\Product::find()
       ->where(['productcategory_id'=>$cat_id])
       ->andWhere(['productsubcategory_id'=>$sub_id])
       ->select(['id','name'])->asArray()->orderBy('name')->all();
       return $data;
       
    }
    
    public static function getProdListb($cat_id, $subcat_id) {
        //find all the houses in the street
        $data = [];
        $data=\frontend\models\Product::find()
       ->where(['productcategory_id'=>$cat_id])
       ->andWhere(['productsubcategory_id'=>$subcat_id])      
       ->select(['id', 'productnumber AS name'])->asArray()->orderBy('name')->all();
       return $data;
       
    }
    
    public static function getCostListb($cat_id, $subcat_id) {
        //find all the houses in the street
        $data = [];
        $data=\frontend\models\Cost::find()
       ->where(['costcategory_id'=>$cat_id])
       ->andWhere(['costsubcategory_id'=>$subcat_id])      
       ->select(['id', 'costnumber AS name'])->asArray()->orderBy('name')->all();
       return $data;
       
    }
    
    public static function getProdListc($cat_id, $subcat_id) {
        //find all the houses in the street
        $data = 0;
        $data=\frontend\models\Product::find()
       ->where(['productcategory_id'=>$cat_id])
       ->andWhere(['productsubcategory_id'=>$subcat_id])      
       ->select(['id'])->count();
       return $data;
       
    }
    
    public function actionSubcat() 
    {
    $out = [];
    if (isset($_POST['depdrop_parents'])) {
        $parents = $_POST['depdrop_parents'];
        if ($parents != null) {
            $cat_id = $parents[0];
            $out = self::getSubCatList($cat_id); 
            return Json::encode(['output'=>$out, 'selected'=>'']);
        }
    }
    return Json::encode(['output'=>$out, 'selected'=>'']);
    }
    
    public function actionSubcatcost() 
    {
    $out = [];
    if (isset($_POST['depdrop_parents'])) {
        $parents = $_POST['depdrop_parents'];
        if ($parents != null) {
            $cat_id = $parents[0];
            $out = self::getSubCatcostList($cat_id); 
            return Json::encode(['output'=>$out, 'selected'=>'']);
        }
    }
    return Json::encode(['output'=>$out, 'selected'=>'']);
    }
    
    public function actionCos() {
    $out = [];
    if (isset($_POST['depdrop_parents'])) {
        $ids = $_POST['depdrop_parents'];
        $cat_id = empty($ids[0]) ? null : $ids[0];
        $subcat_id = empty($ids[1]) ? null : $ids[1];
        if ($cat_id != null) {
            $out = self::getCostListb($cat_id,$subcat_id); 
            return Json::encode(['output'=>$out, 'selected'=>'']);
        }
    }
    return Json::encode(['output'=>'', 'selected'=>'']);
    }
      
    public function actionError()
    {
    $exception = Yii::$app->errorHandler->exception;
    if ($exception !== null) {
        return $this->render('error', ['exception' => $exception]);
    }
    }
   
   public function actionSitemessage()
   {
       $cfirstname = Yii::$app->request->get('custfirstname');
       $cmobile = Yii::$app->request->get('custmobile');
       if (!empty($cfirstname) & !empty($cmobile) & (strlen($cmobile)==15))
       {
            $twilioService = Yii::$app->Yii2Twilio->initTwilio();
            try {
                date_default_timezone_set("Europe/London");
                $date = date('d/m/Y h:i:s a', time());
                $completemessage = $date. Yii::t('app',' Hi '). $cfirstname .Yii::t('app',' , Clean Request: ') .$cmobile;
                $message = $twilioService->account->messages->create(
                substr(Company::findOne(1)->twilio_telephone,0,3) .substr($cmobile,1), // To a number that you want to send sms
                            array(
                                "from" => Company::findOne(1)->twilio_telephone,   // From a number that you are sending
                                "body" => $completemessage, 
                            ));
                           } catch (\Twilio\Exceptions\RestException $e) {
                                echo $e->getMessage();
                                var_dump($e->getMessage());
                           }
       }
   }
   
  public function actionPayments()
  {
        return $this->render('payments');
  }
  
  public function actionReceived()
  {
      return $this->render('received');
  }

  public function actionCancelled()
  {
      return $this->render('cancelled');
  }

  public function actionPrivacypolicy()
  {
      return $this->render('privacypolicy');
  }
  
  
    
}
