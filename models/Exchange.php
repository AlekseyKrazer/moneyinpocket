<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "exchange".
 *
 * @property int $id
 * @property string $date
 * @property string $datetime
 * @property string $amount
 * @property int $deposit_from
 * @property int $deposit_to
 * @property int $user_id
 * @property string $comment
 */
class Exchange extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'exchange';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['datetime', 'amount', 'user_id'], 'required'],
            [['date', 'datetime','amount'], 'safe'],
            [['deposit_from', 'deposit_to', 'user_id'], 'integer'],
            [['comment'], 'string'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Date',
            'datetime' => 'Datetime',
            'amount' => 'Amount',
            'deposit_from' => 'Deposit From',
            'deposit_to' => 'Deposit To',
            'user_id' => 'User ID',
            'comment' => 'Comment',
        ];
    }
}
