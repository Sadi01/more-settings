<?php

namespace sadi01\moresettings\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use sadi01\moresettings\models\SettingCat;

/**
 * SettingCatSearch represents the model behind the search form about `sadi01\moresettings\models\SettingCat`.
 */
class SettingCatSearch extends SettingCat
{
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_public'], 'boolean'],
            [['title', 'description', 'model_class'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
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
        $query = SettingCat::find();

        // add conditions that should always apply here

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
            'is_public' => $this->is_public,
        ]);

        $query->andFilterWhere(['like', 'title', $this->title])
            ->andFilterWhere(['like', 'description', $this->description])
            ->andFilterWhere(['like', 'model_class', $this->model_class]);

        return $dataProvider;
    }
}
