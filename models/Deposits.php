<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "deposits".
 *
 * @property int $id
 * @property string $name
 * @property string $images
 * @property int $group_id
 * @property int $user_id
 * @property int $position
 * @property int $debt
 */
class Deposits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'deposits';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'group_id', 'user_id'], 'required'],
            [['start_sum'], 'number'],
            [['group_id', 'user_id', 'position', 'debt'], 'integer'],
            [['name'], 'string', 'max' => 150],
            [['images'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название счета',
            'images' => 'Выберите иконку:',
            'group_id' => 'Group ID',
            'user_id' => 'User ID',
            'position' => 'Position',
            'start_sum' => 'Начальный капитал',
            'debt' => 'Debt',
        ];
    }

    public static function getAllDepositWithoutDebt()
    {
        $dep = Yii::$app->db->createCommand(
            'SELECT  CONCAT(\'dep_\',id) as id, `name`, `group_id` as parent_id, images, \'dep\' as type from deposits WHERE debt=0
                                                  UNION
                                                  SELECT id, name, parent_id, \'\' as images, \'cat\' as type from groups WHERE debt=0'
        )->queryAll();
        return $dep;
    }
    public static function getAllDepositWithDebt()
    {
        $debt = Yii::$app->db->createCommand(
            'SELECT  CONCAT(\'dep_\',id) as id, `name`, `group_id` as parent_id, images, \'dep\' as type from deposits WHERE debt=1
                                                  UNION
                                                  SELECT id, name, parent_id, \'\' as images, \'cat\' as type from groups WHERE debt=1'
        )->queryAll();
        return $debt;
    }
}
