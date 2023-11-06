<?php
namespace frontend\tests\unit\fixtures;

use yii\test\ActiveFixture;

class ProductsubcategoryFixture extends ActiveFixture
{
    public $modelClass = 'frontend\models\Productsubcategory';
    public $depends = ['frontend\tests\unit\fixtures\ProductcategoryFixture'];
}