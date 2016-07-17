<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "firm".
 *
 * @property integer $id
 * @property string $name
 * @property integer $building_id
 *
 * @property Building $building
 * @property FirmRubric[] $firmRubrics
 * @property Rubric[] $rubrics
 */
class Firm extends \yii\db\ActiveRecord
{
    public $distance;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'firm';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'building_id'], 'required'],
            [['building_id'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['building_id'], 'exist', 'skipOnError' => true, 'targetClass' => Building::className(), 'targetAttribute' => ['building_id' => 'id']],
        ];
    }

    /**
     * @return array
     */
    public function fields()
    {
        return [
            'id',
            'name',
            'building_id',
            'address' => function() {
                return $this->building->street . ', ' . $this->building->building_number;
            },
            'address_point' => function() {
                return [
                    'lat' => $this->building->lat,
                    'lng' => $this->building->lng
                ];
            },
            'phones',
            'rubrics',
            'distance'
        ];
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
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'building_id' => 'Building ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBuilding()
    {
        return $this->hasOne(Building::className(), ['id' => 'building_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFirmRubrics()
    {
        return $this->hasMany(FirmRubric::className(), ['firm_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRubrics()
    {
        return $this->hasMany(Rubric::className(), ['id' => 'rubric_id'])->viaTable('firm_rubric', ['firm_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPhones()
    {
        return $this->hasMany(Phone::className(), ['firm_id' => 'id']);
    }
}
