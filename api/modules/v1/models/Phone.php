<?php

namespace api\modules\v1\models;

use Yii;

/**
 * This is the model class for table "phone".
 *
 * @property integer $id
 * @property integer $firm_id
 * @property integer $type
 * @property string $phone
 *
 * @property Firm $firm
 */
class Phone extends \yii\db\ActiveRecord
{
    const TYPE_MOBILE = 10;
    const TYPE_STATIONARY = 20;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'phone';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['firm_id', 'type', 'phone'], 'required'],
            [['firm_id', 'type'], 'integer'],
            [['type'], 'in', 'range' => [self::TYPE_MOBILE, self::TYPE_STATIONARY]],
            [['phone'], 'string', 'max' => 11],
            [['firm_id'], 'exist', 'skipOnError' => true, 'targetClass' => Firm::className(), 'targetAttribute' => ['firm_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'firm_id' => 'Firm ID',
            'type' => 'Type',
            'phone' => 'Phone',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFirm()
    {
        return $this->hasOne(Firm::className(), ['id' => 'firm_id']);
    }
}
