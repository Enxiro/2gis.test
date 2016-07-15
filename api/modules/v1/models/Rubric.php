<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "rubric".
 *
 * @property integer $id
 * @property integer $parent_id
 * @property string $name
 *
 * @property FirmRubric[] $firmRubrics
 * @property Firm[] $firms
 * @property Rubric $parent
 * @property Rubric[] $rubrics
 */
class Rubric extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'rubric';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['parent_id'], 'integer'],
            [['name'], 'required'],
            [['name'], 'string', 'max' => 255],
            [['parent_id'], 'exist', 'skipOnError' => true, 'targetClass' => Rubric::className(), 'targetAttribute' => ['parent_id' => 'id']],
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
            'parent_id' => 'Parent ID',
            'name' => 'Name',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFirmRubrics()
    {
        return $this->hasMany(FirmRubric::className(), ['rubric_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFirms()
    {
        return $this->hasMany(Firm::className(), ['id' => 'firm_id'])->viaTable('firm_rubric', ['rubric_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParent()
    {
        return $this->hasOne(Rubric::className(), ['id' => 'parent_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(Rubric::className(), ['parent_id' => 'id']);
    }
}
