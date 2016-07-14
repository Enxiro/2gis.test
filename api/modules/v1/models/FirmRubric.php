<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "firm_rubric".
 *
 * @property integer $firm_id
 * @property integer $rubric_id
 *
 * @property Rubric $rubric
 * @property Firm $firm
 */
class FirmRubric extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'firm_rubric';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['firm_id', 'rubric_id'], 'required'],
            [['firm_id', 'rubric_id'], 'integer'],
            [['rubric_id'], 'exist', 'skipOnError' => true, 'targetClass' => Rubric::className(), 'targetAttribute' => ['rubric_id' => 'id']],
            [['firm_id'], 'exist', 'skipOnError' => true, 'targetClass' => Firm::className(), 'targetAttribute' => ['firm_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'firm_id' => 'Firm ID',
            'rubric_id' => 'Rubric ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRubric()
    {
        return $this->hasOne(Rubric::className(), ['id' => 'rubric_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFirm()
    {
        return $this->hasOne(Firm::className(), ['id' => 'firm_id']);
    }
}
