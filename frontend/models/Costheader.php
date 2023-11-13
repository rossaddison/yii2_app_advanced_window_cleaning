<?php
declare(strict_types=1); 

namespace frontend\models;

use frontend\models\Costdetail;
use frontend\models\Employee;
use Yii;

class Costheader extends \yii\db\ActiveRecord
{
       
   public static function getDb()
   {
       return \frontend\components\Utilities::userdb();
   }
    
    public static function tableName()
    {
        return 'works_costheader';
    }
    
    /**
     * @inheritdoc
     */   
    public function rules()
    {
        return [
            [['status', 'employee_id', 'cost_date'], 'required'],
            [['status'], 'string'],
            [['employee_id', 'cost_header_id'], 'integer'],
            [['cost_date', 'modified_date'], 'safe'],
            [['sub_total', 'tax_amt', 'total_due'], 'number'],
            [['sub_total', 'tax_amt', 'total_due'],'default', 'value' => 0.00],
            [['statusfile'], 'string', 'max' => 20],
            [['employee_id'], 'exist', 'skipOnError' => true, 'targetClass' => Employee::className(), 'targetAttribute' => ['employee_id' => 'id']],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'cost_header_id' => Yii::t('app','No.'),
            'status' => Yii::t('app','Cost Code'),
            'statusfile' => Yii::t('app','Cost Code Suffix'),
            'employee_id' => Yii::t('app','Employee ID'),
            'cost_date' => Yii::t('app','Cost Date'),
            'sub_total' => Yii::t('app','Sub Total'),
            'tax_amt' => Yii::t('app','Tax Amt'),
            'total_due' => Yii::t('app','Total Due'),
            'modified_date' => Yii::t('app','Modified Date'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCostdetails()
    {
        return $this->hasMany(Costdetail::class, ['cost_header_id' => 'cost_header_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getEmployee()
    {
        return $this->hasOne(Employee::class, ['id' => 'employee_id']);
    }
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
             $this->modified_date = date('Y-m-d');    
            return true;
        }
        return false;
    }
    
    /**
     * @param int $cost_header_id
     */
    public function setCost_header_id(int $cost_header_id)
    {
        $this->cost_header_id = $cost_header_id;
    }
    
    /**
     * @return int $cost_header_id
     */
    public function getCost_header_id()
    {
        return $this->cost_header_id;
    }
    
    /**
     * @return int $employee_id
     */
    public function getEmployee_id()
    {
        return $this->employee_id;
    }        
    
    /**
     * @param int $employee_id
     */
    public function setEmployee_id(int $employee_id)
    {
        $this->employee_id = $employee_id;
    }
    
    /**
     * @return string $cost_date
     */
    public function getCost_date()
    {
        return $this->cost_date;
    }        
    
    /**
     * @param string $cost_date
     */
    public function setCost_date(string $cost_date)
    {
        $this->cost_date = $cost_date;
    }
    
    /**
     * @return string $status
     */
    public function getStatus()
    {
        return $this->status;
    }        
    
    /**
     * @param string $status
     */
    public function setStatus(float $status)
    {
        $this->status = $status;
    }
    
    /**
     * @return string $statusfile
     */
    public function getStatusfile()
    {
        return $this->statusfile;
    }        
    
    /**
     * @param string $statusfile
     */
    public function setStatusfile(float $statusfile)
    {
        $this->statusfile = $statusfile;
    }
    
    /**
     * @return float
     */
    public function getSub_total()
    {
        return $this->sub_total;
    }        
    
    /**
     * @param float $sub_total
     */
    public function setSub_total(float $sub_total)
    {
        $this->sub_total = $sub_total;
    }
    
    /**
     * @return float
     */
    public function getTax_amt()
    {
        return $this->tax_amt;
    }        
    
    /**
     * @param float $tax_amt
     */
    public function setTax_amt(float $tax_amt)
    {
        $this->tax_amt = $tax_amt;
    }        
    
    /**
     * @param float $total_due
     */
    public function setTotal_due(float $total_due)
    {
        $this->total_due = $total_due;
    }        
    
    /**
     * @return float
     */
    public function getTotal_due()
    {
        return $this->total_due;
    }        
}
