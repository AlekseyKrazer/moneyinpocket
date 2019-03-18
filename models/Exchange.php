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

    public static function getHistoryData($date)
    {
        $data = Exchange::find()
	        ->select(
                ['exchange.id', 'exchange.date', 'exchange.datetime', 'exchange.amount', 'exchange.deposit_from', 'd.name as deposit_from_name',
                    'd.images as deposit_from_images', 'exchange.deposit_to', 'dd.name as deposit_to_name', 'dd.images as deposit_to_images',
                    'exchange.comment']
            )
	        ->join('INNER JOIN', 'deposits as d', '`exchange`.`deposit_from` = `d`.`id` AND `exchange`.`user_id` = `d`.`user_id`')
	        ->join('INNER JOIN', 'deposits as dd', '`exchange`.`deposit_to` = `dd`.`id` AND `exchange`.`user_id` = `dd`.`user_id`')
	        ->where(['date' => $date, 'exchange.user_id' => Yii::$app->user->id])
	        ->orderBy( "datetime,id" )
	        ->asArray()->all();
        return $data;
    }
}
