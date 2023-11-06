<?php
namespace frontend\tests\unit\fixtures;

use yii\test\ActiveFixture;

class ProductcategoryFixture extends ActiveFixture
{
    public $modelClass = 'frontend\models\Productcategory';
    public $depends = ['frontend\tests\unit\fixtures\TaxFixture'];
}