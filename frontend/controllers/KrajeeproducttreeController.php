<?php
declare(strict_types=1); 

namespace frontend\controllers;

use frontend\models\KrajeeProductTree;
use frontend\models\Productcategory;
use frontend\models\Productsubcategory;
use frontend\models\Product;
use kartik\tree\controllers\NodeController;
use yii\filters\VerbFilter;

/**
 * @see kartik\tree\models\Tree ... \vendor\kartik-v\yii2-tree-manager\src\models\Tree
 */
class KrajeeproducttreeController extends NodeController
{
    public function behaviors()
    {
        return [
            'verbs' => [
                'class' =>VerbFilter::class,
                'actions' => [
                   // 'delete' => ['POST'],
                ],
            ],
           'access' => 
                [
                'class' => \yii\filters\AccessControl::class,
                'only' => ['index','populate'],
                'rules' => [
                [
                  'allow' => true,
                  'roles' => ['admin'],
                ],
                [
                  'allow' => false,
                  'roles' => ['?'],
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
     * 
     * @return string 
     */    
    public function actionIndex()
    {
        return $this->render('index');      
    }
    
    /**
     * 
     * @return string
     */
    public function actionPopulate()
    {
        //remove all data in the database
        KrajeeProductTree::deleteAll();
        //rebuild the database given data from productcategory ie. postcode, productsubcategory ie. street, product ie. house
        //create the root and call it Run
        $root = new KrajeeProductTree(['name'=>'Run']);
        /**
         * @see 
         * @psalm-suppress TooFewArguments
         */
        $root->makeRoot();
        //create the postcode nodes
        $allpostcodes = Productcategory::find()->orderBy('id')->all() ?: [];
        /**
         * @var int $key
         * @var int $value
         */
        foreach ($allpostcodes as $key => $value)
        {
            /**
             * @var array $allpostcodes[$key]
             */
            $newpostcodenode = new KrajeeProductTree(['name'=>$allpostcodes[$key]['name']]);
            $newpostcodenode->productcategory_id = $allpostcodes[$key]['id'];
            /**
             * @see \vendor\kartik-v\yii2-tree-manager\src\models\Tree
             * @psalm-suppress TooFewArguments
             */
            $newpostcodenode->prependTo($root);
            $allstreets = Productsubcategory::find() 
                        ->where(['productcategory_id'=>$allpostcodes[$key]['id']])
                        ->orderBy('sort_order')
                        ->all() ?: null;
            if (null!==$allstreets) { 
                //create the street nodes associated with this new node
                /**
                 * @var int $key
                 * @var int $value
                 */
                foreach ($allstreets as $key => $value)
                {
                    /**
                     * @var array $allstreets[$key]
                     */
                    $newstreetnode = new KrajeeProductTree(['name'=>$allstreets[$key]['name']]);
                    $newstreetnode->productsubcategory_id = $allstreets[$key]['id'];
                    /**
                     * @psalm-suppress TooFewArguments
                     */
                    $newstreetnode->prependTo($newpostcodenode);
                    $allhouses = Product::find()
                            ->where(['productsubcategory_id'=>$allstreets[$key]['id']])
                            ->andWhere(['productcategory_id'=>$allstreets[$key]['productcategory_id']])
                            ->andWhere(['isactive'=>1])
                            ->all() ?: [];
                    //create the house nodes associated with this new steet node
                    /**
                     * @var int $key
                     * @var int $value
                     */
                    foreach ($allhouses as $key => $value)
                    {
                        /**
                         * @var array $allhouses[$key]
                         */
                        $newhousenode = new KrajeeProductTree(['name'=>$allhouses[$key]['productnumber']]);
                        $newhousenode->product_id = $allhouses[$key]['id'];
                        /**
                         * @psalm-suppress TooFewArguments
                         */
                        $newhousenode->prependTo($newstreetnode);
                    } //foreach  
                } // foreach
            } // null!==$allstreets    
        }      
        return $this->render('index'); 
    }     
}
