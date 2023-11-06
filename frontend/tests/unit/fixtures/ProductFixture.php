<?php
namespace frontend\tests\unit\fixtures;

use yii\test\ActiveFixture;

class ProductFixture extends ActiveFixture
{
    public $modelClass = 'frontend\models\Product';
    
    // The product table has 3 foreigh keys ie. productcategory_id, productsubcategory_id, and user_id
    public $depends = [
        'frontend\tests\unit\fixtures\ProductcategoryFixture', 
        'frontend\tests\unit\fixtures\ProductsubcategoryFixture', 
        // one or more houses ie. products can be assigned to one user ... who will make payment 
        'frontend\tests\unit\fixtures\UserFixture'
    ];
}