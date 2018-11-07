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
}
