<?php
declare(strict_types=1); 

namespace frontend\models;

use frontend\models\Costheader;
use frontend\models\Costcategory;
use frontend\models\Costsubcategory;
use frontend\models\Carousal;
use frontend\models\Cost;
use Yii;

class Costdetail extends \yii\db\ActiveRecord
{
        
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
            [['paymentreference'],'default','value'=> null],
            [['paymentreference'],'string'],            
            [['line_total'],'default','value'=>1],            
            [['order_qty'], 'number'],
            [['unit_price','paid'], 'number','min'=>0.00,'max'=>10000.00],
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
     * @return int
     */
    public function getCostsubcategory_id() 
    {
        return $this->costsubcategory_id;
    }
    
    /**
     * @param int $costsubcategory_id
     * @return void
     */
    public function setCostsubcategory_id(int $costsubcategory_id)
    {
        $this->costsubcategory_id = $costsubcategory_id;
    }        
    
    /**
     * @return int
     */
    public function getCostcategory_id() 
    {
        return $this->costcategory_id;
    }
    
    /**
     * @param int $costcategory_id
     */
    public function setCostcategory_id(int $costcategory_id) {
        $this->costcategory_id = $costcategory_id;
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
    public function setUnit_price( $price)
    {
        $this->unit_price = $price;
    }
    
    /**
     * 
     * @return float
     */
    public function getUnit_price()
    {
        return $this->unit_price;
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
    public function setPaid( $paid)
    {
        $this->paid = $paid;
    }
    
    /**
     * 
     * @return int 
     */
    public function getCarousal_id()
    {
        return $this->carousal_id;
    } 
    
    /**
     * 
     * @param ?int $carousal_id
     */
    public function setCarousal_id(?int $carousal_id)
    {
        $this->carousal_id = $carousal_id;
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
     * @param int $cost_detail_id
     */
    public function setCost_detail_id(int $cost_detail_id)
    {
        $this->cost_detail_id = $cost_detail_id;
    }        
    
    /**
     * 
     * @return int
     */
    public function getCost_header_id()
    {
        return $this->cost_header_id;
    }
    
    /**
     * 
     * @param int $cost_header_id
     */
    public function setCost_header_id(int $cost_header_id)
    {
        $this->cost_header_id = $cost_header_id;
    }
    
    /**
     * @return int
     */
    public function getCost_id()
    {
        return $this->cost_id;
    }
    
    /**
     * 
     * @param int $cost_id
     */
    public function setCost_id(int $cost_id)
    {
        $this->cost_id = $cost_id;
    }
    
    /**
     * @return int
     */
    public function getOrder_qty()
    {
        return $this->order_qty;
    }
    
    /**
     * 
     * @param  int $order_qty
     */
    public function setOrder_qty(int $order_qty)
    {
        $this->order_qty = $order_qty;
    }
    
    /**
     * @return string
     */
    public function getNextcost_date()
    {
        return $this->nextcost_date ?: date('Y-m-d');
    }
    
    /**
     * 
     * @param  string $nextcost_date
     */
    public function setNextcost_date(string $nextcost_date)
    {
        $this->nextcost_date = $nextcost_date;
    }
    
    /**
     * @return string
     */
    public function getCost_date()
    {
        return $this->cost_date;
    }
    
    /**
     * 
     * @param  string $cost_date
     */
    public function setCost_date(string $cost_date)
    {
        $this->cost_date = $cost_date;
    }
    
    /**
     * @return float
     */
    public function getLine_total()
    {
        return $this->line_total;
    }
    
    /**
     * @param  float $line_total
     */
    public function setLine_total(float $line_total)
    {
        $this->line_total = $line_total;
    }
    
    /**
     * @return string
     */
    public function getPaymenttype()
    {
        return $this->paymenttype;
    }
    
    /**
     * @param  string $paymenttype
     */
    public function setPaymenttype(string $paymenttype)
    {
        $this->paymenttype = $paymenttype;
    }
    
    /**
     * @return string
     */
    public function getPaymentreference()
    {
        return $this->paymentreference;
    }
    
    /**
     * @param  ?string $paymentreference
     */
    public function setPaymentreference(?string $paymentreference)
    {
        $this->paymentreference = $paymentreference;
    }
}
