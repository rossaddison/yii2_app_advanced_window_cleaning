<?php
declare(strict_types=1); 

namespace frontend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Paymentrequest;

/**
 * PaymentrequestSearch represents the model behind the search form of `frontend\models\Paymentrequest`.
 */
class PaymentrequestSearch extends Paymentrequest
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'sales_order_detail_id'], 'integer'],
            [['gc_payment_request_id', 'status', 'modified_date'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Paymentrequest::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'sales_order_detail_id' => $this->sales_order_detail_id,
            'modified_date' => $this->modified_date,
        ]);

        $query->andFilterWhere(['like', 'gc_payment_request_id', $this->gc_payment_request_id])
            ->andFilterWhere(['like', 'status', $this->status]);

        return $dataProvider;
    }
}
