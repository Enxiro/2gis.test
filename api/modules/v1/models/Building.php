<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "building".
 *
 * @property integer $id
 * @property string $street
 * @property string $building_number
 * @property string $lat
 * @property string $lng
 *
 * @property Firm[] $firms
 */
class Building extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'building';
    }

    /**
     * @return array
     */
    public function extraFields()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['street', 'building_number', 'lat', 'lng'], 'required'],
            [['lat', 'lng'], 'number'],
            [['street'], 'string', 'max' => 255],
            [['building_number'], 'string', 'max' => 20],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'street' => 'Street',
            'building_number' => 'Building number',
            'lat' => 'Lat',
            'lng' => 'Lng',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFirms()
    {
        return $this->hasMany(Firm::className(), ['building_id' => 'id']);
    }
}
