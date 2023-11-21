<?php
declare(strict_types=1); 

namespace frontend\controllers;

use Yii;
use frontend\models\Easy;
use frontend\models\Productsubcategory;
use frontend\models\Product;
use yii\web\Controller;
use yii\filters\VerbFilter;

class EasyController extends Controller
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
                'access' => 
                            [
                            'class' => \yii\filters\AccessControl::class,
                            'only' => ['selectedhousenumbers','index'],
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
     * 
     * @return \yii\web\Response|string
     */
    public function actionInitialize()
    {
        $model = new Easy();
        $street = Productsubcategory::find()
                    ->where(['sort_order'=>500])
                    ->count();
        $housenumbers = [];
        $post = (array)Yii::$app->request->post();
        if ($model->load($post)) {
                //determine if only one street has been flagged 
                if ($street === '1'){
                    /**
                     * @var Productsubcategory|null $street_house
                     */
                     $street_house = Productsubcategory::find()
                    ->where(['sort_order'=>500])
                    ->one();
                     if (null!==$street_house) {
                        $housenumber = 0;
                        $easy = (array)Yii::$app->request->post('Easy');
                        //$easy = $_POST['Easy'];
                        /**
                         * @var array $easy['housenumber_ids']
                         */
                        $housenumbers = $easy['housenumber_ids'];
                        /**
                         * @var int $key
                         * @var int $value
                         */
                        foreach($housenumbers as $key => $value) {
                            $product = new Product();
                            $product->setProductcategory_id($street_house->getProductcategory_id());
                            $product->setProductsubcategory_id($street_house->getId());
                            $product->setName("Firstname");
                            $product->setSurname("Surname");
                            if (($value+1) < 10) {
                                $housenumber = '00'.($value+1);
                            }
                            if ((($value+1) > 10) && (($value+1) < 100)) {
                                $housenumber = '0'.($value+1);
                            }
                            if ((($value+1) > 100) || (($value+1) == 100)) {
                                $housenumber = $value+1;
                            }
                            $product->setProductnumber((string)$housenumber);
                            $product->setPostcodefirsthalf("");
                            $product->setPostcodesecondhalf("");
                            $product->setContactmobile('09999999999');
                            $product->setEmail('email@email.com');
                            $product->setSpecialrequest("");
                            $product->setFrequency("Monthly");
                            $product->setListprice(0.00);
                            $product->setSellstartdate(date("Y-m-d")); 
                            $product->setIs_Active(true);
                            $product->setJobcode('');
                            $product->save();
                        }
                    } // if street_house  
                } //If ($street === 1){
                else 
                {
                    if (!empty(Yii::$app->session)) {
                        Yii::$app->session->setFlash('warning', 'More than one record contains a flag of 99999 for your sort_order field.');
                    } else {
                        $session = new \yii\web\Session();
                        $session->open();
                        $session->setFlash('warning', 'More than one record contains a flag of 99999 for your sort_order field.');
                    }
                }
                return $this->redirect(['/product/index']);
        } // if ($model->load(Yii::$app->request->post()))
        //initialise default items list 1 to 2000
        //this should be sufficient for most neighbourhoods
        $items = range($model->getStart(), $model->getFinish());
        return $this->render('favourite', [
            'model' => $model,
            'items' => $items
        ]);
    }
}
