<?php
declare(strict_types=1); 

namespace frontend\models;

use Yii;

Class Easy extends \yii\base\Model
{
    public $housenumber_ids = [];
    
    public $start = 1;
    
    public $finish = 300;
    
    public function rules()
    {
        return [
            [
                [
                    'start',
                    'finish',
                ],
                'required',
            ],
            [
                [
                    'start',
                    'finish',
                ],
               'number',
            ],         
        ];
    }
    
    public function attributeLabels()
    {
        return [
            'housenumber_ids' => Yii::t('app','House Numbers'),
            'start' => Yii::t('app','Start Number'),
            'finish' => Yii::t('app','Finish Number'),
        ];
    }
    
    /**
     * @return int
     */
    public function getStart()
    {
        return $this->start;
    }
    
    /**
     * @return int
     */
    public function getFinish()
    {
        return $this->finish;
    }
       
}
    
    
    
    
    
    
    

