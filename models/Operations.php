<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "operations".
 *
 * @property int $id
 * @property string $datetime
 * @property int $user_id
 * @property int $type
 * @property string $amount
 * @property int $deposit_id
 * @property int $category_id
 * @property string $comment
 */
class Operations extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'operations';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['datetime', 'user_id', 'type', 'amount', 'deposit_id', 'category_id'], 'required'],
            [['datetime'], 'safe'],
            [['date'], 'safe'],
            [['user_id', 'type', 'deposit_id', 'category_id'], 'integer'],
            [['comment'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'datetime' => 'Дата',
            'type' => 'Type',
            'amount' => 'Сумма',
            'deposit_id' => 'Откуда берем деньги',
            'category_id' => 'На что тратим',
            'comment' => 'Комментарий',
        ];
    }

    public static function getHistoryData($date, $type)
    {
        $data = Yii::$app->db->createCommand(
            "
              SELECT op.id, op.date, op.datetime, op.amount, op.deposit_id, dep.name as deposit_name, dep.images, op.category_id, 
              cat.name as category_name, op.comment 
              FROM operations op 
              JOIN categories cat  ON cat.id=op.category_id and cat.user_id=op.user_id
              JOIN deposits dep ON op.deposit_id=dep.id and dep.user_id=op.user_id
              WHERE op.user_id=" . Yii::$app->user->id . " and date='" . $date . "' and op.type=" . $type . " ORDER by datetime ASC, op.id ASC"
        )->queryAll();
        return $data;
    }
}