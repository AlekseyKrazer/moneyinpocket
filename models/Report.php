<?php
/**
 * Created by PhpStorm.
 * User: crazer
 * Date: 05.12.2018
 * Time: 11:41
 */

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Report extends ActiveRecord
{
    public $view;
    public $daterange;

    public static function tableName()
    {
        return 'operations';
    }

    public static function getDataChart($dates, $type)
    {
        $dates_arr = explode(" to ", $dates);

        $sql_data = Yii::$app->db->createCommand("
              SELECT SUM(op.amount) as amount, cat.name as category_name 
              FROM operations op 
              JOIN categories cat  ON cat.id=op.category_id and cat.user_id=op.user_id
              WHERE op.user_id=" . Yii::$app->user->id . " and date>='" . $dates_arr[0] . "' and date<='" . $dates_arr[1] . "' and op.type=" . $type . " GROUP by category_name ORDER by amount ASC")->queryAll();

        $total = 0;
        $i     = 0;
        $data  = array();
        foreach ($sql_data as $k => $v) {
            $data[$i]['name'] = $v['category_name'];
            $data[$i]['y']    = intval(abs($v['amount']));
            $total            = $total + abs($v['amount']);
            $i++;
        }
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        return compact('data', 'total');
    }

    public static function getTotalMoney($type)
    {
        $sql_data = Yii::$app->db->createCommand("SELECT SUM(amount) as amount 
              FROM operations 
              WHERE op.user_id=" . Yii::$app->user->id . " and date>='2018-12-01' and op.type=" . $type . " GROUP by category_name ORDER by amount ASC")->queryAll();


        $data[] = ['Category', 'Amount'];
        foreach ($sql_data as $k => $v) {
            $data[] = [$v['category_name'], intval(abs($v['amount']))];
        }
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);

        return $data;
    }

    /**
     * @return array the validation rules.
     */
    public function rules()
    {
        return [
            [['view', 'daterange', 'type', 'datetime'], 'required'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'type'      => 'Тип операций:',
            'datetime'  => 'Выберите свой период:',
            'view'      => 'Группировка:',
            'daterange' => 'Период:',
        ];
    }
}
