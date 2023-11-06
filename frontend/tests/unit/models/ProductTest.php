<?php
namespace frontend\tests\unit\models;

use common\fixtures\UserFixture;

// foreign keys in table Product
use frontend\tests\unit\fixtures\TaxFixture;
use frontend\tests\unit\fixtures\ProductcategoryFixture;
use frontend\tests\unit\fixtures\ProductsubcategoryFixture;

use frontend\models\Product;

class ProductTest extends \Codeception\Test\Unit
{
    protected $tester;
    protected $testcase;
    
    /**
     * @see @frontend\codeception.yml 
     */    
    public function _before()
    {
        $this->tester->haveFixtures([
            'user' => [
                'class' => UserFixture::class,
                'dataFile' => codecept_data_dir() . 'user.php'
            ],
            'tax' => [
                'class' => TaxFixture::class,
                'dataFile' => codecept_data_dir() . 'works_tax.php'
            ],
            'productcategory' => [
                'class' => ProductcategoryFixture::class,
                'dataFile' => codecept_data_dir() . 'works_productcategory.php'
            ],
            'productsubcategory' => [
                'class' => ProductsubcategoryFixture::class,
                'dataFile' => codecept_data_dir() . 'works_productsubcategory.php'
            ],
        ]);
    }
        
    private function new_product() : Product {
        $product = new Product([
            'name' =>'Twenty',
            'surname' => 'Windows',
            'contactmobile' => '07712121212',
            'specialrequest' => 'Do not clean front door',
            'listprice' => 10.00,
            'frequency' => 'Monthly',
            'productnumber' => '199',
            'postcodefirsthalf' => 'G31',
            'postcodesecondhalf' => '5FN',
            'email' => 'householder@house.com',
            // street within the postcode
            'productsubcategory_id' => 1,
            // postcode
            'productcategory_id' => 1,
            'sellstartdate' => date('Y-m-d'),
            'sellenddate' => date('2099-12-31'),
            'discontinueddate' => null,
            'isactive' => 1,
            'mandate' => '',
            'gc_number' => ''
        ]);
        return $product;
    }
    
    public function testValidation()
    {
        $product = new Product();

        $product->name = null;
        $this->assertFalse($product->validate($product->name));
        // exceed the maximum length of 60
        $product->name = 'toolooooongnaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaeeee';
        $this->assertFalse($product->validate($product->name));

        $product->name= 'myname';
        $this->assertTrue($product->validate($product->name));
    }
}
