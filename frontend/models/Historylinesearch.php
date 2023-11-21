<?php
declare(strict_types=1); 

namespace frontend\models;

use frontend\models\Historyline;
use yii\base\Model;
use yii\data\ActiveDataProvider;

class Historylinesearch extends Historyline
{
    
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params)
    {
        $query = Historyline::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 10,
            ],
            'db'=> \frontend\components\Utilities::userdb(),
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }
        
        $dataProvider->sort->attributes['historyline'] = [
        'asc' => ['start' => SORT_DESC],
        'desc' => ['start' => SORT_ASC],
        ];
        
        $dataProvider->sort->attributes['historyline'] = [
        'asc' => ['stop' => SORT_DESC],
        'desc' => ['stop' => SORT_ASC],
        ];
        
        $query->andFilterWhere([
            //'start' => $this->start,
            //'stop' => $this->stop,
            'text' => $this->text])
            ->andFilterWhere(['>=','start',$this->start])
            ->andFilterWhere(['>=','stop',$this->stop])    
            ->orderBy('start')
            ->all();
        return $dataProvider;
    }
}
