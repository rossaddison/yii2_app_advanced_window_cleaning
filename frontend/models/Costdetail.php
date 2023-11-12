<?php
namespace frontend\models;

use frontend\models\Costheader;
use frontend\models\Costcategory;
use frontend\models\Costsubcategory;
use frontend\models\Carousal;
use frontend\models\Cost;
use Yii;

class Costdetail extends \yii\db\ActiveRecord
{
    public $line_total;
    public $order_qty;
    public $carousal_id;
    public $cost_id;
    public $costcategory_id;
    public $costsubcategory_id;
    public $nextcost_date;
    public $cost_header_id;
    public $paymenttype;
    
    /**
     * @inheritdoc
     */
    
    public static function getDb()
   {
       return \frontend\components\Utilities::userdb();
   }
        
    public static function tableName()
    {
        return 'works_costdetail';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['nextcost_date','cost_header_id', 'costcategory_id','costsubcategory_id', 'cost_id', 'unit_price'], 'required'],
            [['cost_header_id','costcategory_id','costsubcategory_id', 'cost_id','carousal_id'], 'integer'],
            [['nextcost_date'], 'safe'],
            [['order_qty'],'default','value'=>1],
            [['paymenttype'],'string'],
            [['paymenttype'],'default','value'=>'Cash'],
            [['paymentreference'],'string'],            
            [['line_total'],'default','value'=>1],
            [['order_qty'], 'number'],
            [['unit_price','paid'], 'float','min'=>0.00,'max'=>10000.00],
            [['unit_price','paid','order_qty'], 'default','value' => 0.00],
            [['carousal_id'], 'default','value' => null],
            [['carousal_id'], 'exist', 'skipOnError' => true, 'targetClass' => Carousal::className(), 'targetAttribute' => ['carousal_id' => 'id']],
            [['cost_id'], 'exist', 'skipOnError' => true, 'targetClass' => Cost::className(), 'targetAttribute' => ['cost_id' => 'id']],
            [['cost_header_id'], 'exist', 'skipOnError' => true, 'targetClass' => Costheader::className(), 'targetAttribute' => ['cost_header_id' => 'cost_header_id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'cost_header_id' => Yii::t('app','Daily Cost ID'),
            'cost_detail_id' => Yii::t('app','Cost(s) in Clean ID'),
            'paymenttype'=> Yii::t('app','Payment Type eg. Cash, Cheque, Paypal, Debitcard, Creditcard, Other *Default: Cash'),
            'paymentreference'=>Yii::t('app','Payment Reference:'),
            'nextcost_date' => Yii::t('app','Next Cost Date'),
            'costcategory_id' => Yii::t('app','Costcode'),
            'costsubcategory_id'=>Yii::t('app','Costsubcode'),
            'cost_id'=>Yii::t('app','Cost'),
            'cost_id.costdescription' => Yii::t('app','Cost Description'),
            'cost_id.costnumber'=>Yii::t('app','Cost Number'),
            'carousal_id' => Yii::t('app','Carousal File eg. jpg, png, pdf, xls, xlsx'),
            'order_qty'=>Yii::t('app','Order Qty'),
            'unit_price' => Yii::t('app','Unit Price'),
            'line_total'=> Yii::t('app','Line Total'),
            'paid' => Yii::t('app','Paid'),
            'modified_date' => Yii::t('app','Modified Date'),
        ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCost()
    {
        return $this->hasOne(Cost::className(), ['id' => 'cost_id']);
    }
    
    public function getCostcategory()
    {
        return $this->hasOne(Costcategory::className(), ['id' => 'costcategory_id']);
    }

    public function getCostsubcategory()
    {
        return $this->hasOne(Costsubcategory::className(), ['id' => 'costsubcategory_id']);
    }
    
    public function getCostHeader()
    {
        return $this->hasOne(Costheader::className(), ['cost_header_id' => 'cost_header_id']);
    }
    
    public function getCarousal()
    {
        return $this->hasOne(Carousal::className(), ['id' => 'carousal_id']);
    }
    
    /**
     * 
     * @param float $price
     */
    public function setUnitPrice(float $price)
    {
        $this->unit_price = $price;
    }
    
    /**
     * 
     * @return float
     */
    public function getUnitPrice()
    {
        return $this->unit_price;
    } 
    
    /**
     * @param float $line_total
     */
    public function setLineTotal(float $line_total)
    {
        $this->line_total = $line_total;
    }
    
    /**
     * 
     * @return float
     */
    public function getLineTotal()
    {
        return $this->line_total;
    }      
    
    /**
     * @return float
     */
    public function getPaid()
    {
        return $this->paid;
    }
    
    /**
     * 
     * @param float $paid
     */
    public function setPaid(float $paid)
    {
        $this->paid = $paid;
    }
    
    /**
     * 
     * @return int 
     */
    public function getCost_detail_id()
    {
        return $this->cost_detail_id;
    } 
    
    /**
     * 
     * @return int
     */
    public function getCost_header_id()
    {
        return $this->cost_header_id;
    }        
}
