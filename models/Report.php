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

    public function getDataChart($dates, $type)
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

//    public static function getTotalMoney($type)
//    {
//        $sql_data = Yii::$app->db->createCommand("SELECT SUM(amount) as amount
//              FROM operations
//              WHERE op.user_id=".Yii::$app->user->id." and op.type=" . $type . " GROUP by category_name ORDER by amount ASC")->queryAll();
//
//
//        $data[] = ['Category', 'Amount'];
//        foreach ($sql_data as $k => $v) {
//            $data[] = [$v['category_name'], intval(abs($v['amount']))];
//        }
//        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
//
//        return $data;
//    }

    public function getDetailData($dates, $type)
    {
        $dates_arr = explode(" to ", $dates);

        $sql_data = Yii::$app->db->createCommand("SELECT op.datetime, dep.name as deposit, dep.images as deposit_image, op.amount as amount, cat.name as category_name, op.comment 
              FROM operations op 
              JOIN categories cat  ON cat.id=op.category_id and cat.user_id=op.user_id
				JOIN deposits dep ON dep.id=op.deposit_id
              WHERE op.user_id=".Yii::$app->user->id." and date>='".$dates_arr[0]."' and date<='".$dates_arr[1]."' and op.type=".$type." ORDER by datetime DESC")->queryAll();

        $total = 0;
        foreach ($sql_data as $k => $v) {
            $total = $total + abs($v['amount']);
        }

        $data = $sql_data;
        return compact('data', 'total');
    }

    public function getData($model)
    {
        if ($model->view==1) {
            $data = Report::getDataChart($model->datetime, $model->type);
        }
        if ($model->view==0) {
            $data = Report::getDetailData($model->datetime, $model->type);
        }

        return $data;
    }

    public function getLastYearOperation()
    {
            $sql_data = Yii::$app->db->createCommand("
Select name as date_month_year, income, spend from 
(
        Select CONCAT(YEAR(curdate()),DATE_FORMAT(curdate(), '%m')) as check_month, 
        DATE_FORMAT(curdate(), '%m-%Y') as name
        UNION
        Select CONCAT(YEAR(curdate() - INTERVAL 1 MONTH),DATE_FORMAT(curdate() - INTERVAL 1 MONTH, '%m')) as check_month, 
        DATE_FORMAT(curdate()-INTERVAL 1 MONTH, '%m-%Y') as name
        UNION
        Select CONCAT(YEAR(curdate() - INTERVAL 2 MONTH),DATE_FORMAT(curdate() - INTERVAL 2 MONTH, '%m')) as check_month, 
        DATE_FORMAT(curdate()-INTERVAL 2 MONTH, '%m-%Y') as name
        UNION
        Select CONCAT(YEAR(curdate() - INTERVAL 3 MONTH),DATE_FORMAT(curdate() - INTERVAL 3 MONTH, '%m')) as check_month, 
        DATE_FORMAT(curdate()-INTERVAL 3 MONTH, '%m-%Y') as name
        UNION
        Select CONCAT(YEAR(curdate() - INTERVAL 4 MONTH),DATE_FORMAT(curdate() - INTERVAL 4 MONTH, '%m')) as check_month, 
        DATE_FORMAT(curdate()-INTERVAL 4 MONTH, '%m-%Y') as name
        UNION
        Select CONCAT(YEAR(curdate() - INTERVAL 5 MONTH),DATE_FORMAT(curdate() - INTERVAL 5 MONTH, '%m')) as check_month, 
        DATE_FORMAT(curdate()-INTERVAL 5 MONTH, '%m-%Y') as name
        UNION
        Select CONCAT(YEAR(curdate() - INTERVAL 6 MONTH),DATE_FORMAT(curdate() - INTERVAL 6 MONTH, '%m')) as check_month, 
        DATE_FORMAT(curdate()-INTERVAL 6 MONTH, '%m-%Y') as name
        UNION
        Select CONCAT(YEAR(curdate() - INTERVAL 7 MONTH),DATE_FORMAT(curdate() - INTERVAL 7 MONTH, '%m')) as check_month, 
        DATE_FORMAT(curdate()-INTERVAL 7 MONTH, '%m-%Y') as name
        UNION
        Select CONCAT(YEAR(curdate() - INTERVAL 8 MONTH),DATE_FORMAT(curdate() - INTERVAL 8 MONTH, '%m')) as check_month, 
        DATE_FORMAT(curdate()-INTERVAL 8 MONTH, '%m-%Y') as name
        UNION
        Select CONCAT(YEAR(curdate() - INTERVAL 9 MONTH),DATE_FORMAT(curdate() - INTERVAL 9 MONTH, '%m')) as check_month, 
        DATE_FORMAT(curdate()-INTERVAL 9 MONTH, '%m-%Y') as name
        UNION
        Select CONCAT(YEAR(curdate() - INTERVAL 10 MONTH),DATE_FORMAT(curdate() - INTERVAL 10 MONTH, '%m')) as check_month,
        DATE_FORMAT(curdate()-INTERVAL 10 MONTH, '%m-%Y') as name
        UNION
        Select CONCAT(YEAR(curdate() - INTERVAL 11 MONTH),DATE_FORMAT(curdate() - INTERVAL 11 MONTH, '%m')) as check_month, 
        DATE_FORMAT(curdate()-INTERVAL 11 MONTH, '%m-%Y') as name

) as Months LEFT JOIN
(
SELECT spend.date_month_year, income, spend FROM 
    
        (SELECT DATE_FORMAT(date, '%m-%Y') as date_month_year, SUM(amount) as income 
        FROM `operations` 
        WHERE user_id=".Yii::$app->user->id." AND amount>0 and date>=DATE_SUB(DATE_FORMAT(curdate(), '%Y-%m-01'), INTERVAL 11 MONTH) 
        GROUP by MONTH(date) ORDER by date ASC) as income
        
        RIGHT JOIN
        
        (SELECT DATE_FORMAT(date, '%m-%Y') as date_month_year, SUM(amount) as spend 
        FROM `operations` 
        WHERE user_id=".Yii::$app->user->id." AND amount<0 and date>=DATE_SUB(DATE_FORMAT(curdate(), '%Y-%m-01'), INTERVAL 11 MONTH) 
        GROUP by MONTH(date) ORDER by date ASC) as spend 
        
        ON income.date_month_year=spend.date_month_year
) as data ON Months.name = data.date_month_year ORDER by check_month DESC")->queryAll();

        return $sql_data;
    }

    //Выясняем первую и последнюю дату с записями по доходам-расходам
    protected function getFirstAndLastDate()
    {
        $sql_data = Yii::$app->db->createCommand("
        (SELECT DATE_FORMAT(date, '%Y-%m-01') as date FROM `operations` WHERE user_id=".Yii::$app->user->id." ORDER by date ASC LIMIT 1)
        UNION
        (SELECT DATE_FORMAT(date, '%Y-%m-01') as date FROM `operations` WHERE user_id=".Yii::$app->user->id." ORDER by date DESC LIMIT 1)")->queryAll();

        return $sql_data;
    }

    protected function formatToHighcharts($array)
    {
        $dates = $this->getFirstAndLastDate();

        $from_date = $dates[0]['date'];
        $to_date = $dates[1]['date'];
        while ($from_date!=$to_date) {
                $is_exist = 0;
            foreach ($array as $key => $value) {
                if ($value['month'] == $from_date) {
                    $is_exist = 1;
                }
            }
            if ($is_exist == 0) {
                array_push($array, ['money'=>0, 'month' => $from_date]);
            }
            $time = strtotime($from_date);
            $from_date = date("Y-m-d", strtotime("+1 month", $time));
        }


        $array_sort = array_column($array, 'month');

        array_multisort($array_sort, SORT_ASC, $array);

        $array_to_highchart = [];
        foreach ($array as $k => $v) {
            date_default_timezone_set('UTC');
            $array_to_highchart[] = [strtotime($array[$k]['month'])*1000, intval(abs($array[$k]['money']))];
        }

        return $array_to_highchart;
    }

    public function getAllOperations()
    {
        $outcome_data = Yii::$app->db->createCommand("SELECT SUM(amount) as money, DATE_FORMAT(date, '%Y-%m-01') as month 
                                                          FROM operations 
                                                          WHERE user_id=".Yii::$app->user->id." and type=1 
                                                          GROUP by DATE_FORMAT(date, '%Y-%m-01') 
                                                          ORDER by DATE_FORMAT(date, '%Y-%m-01') ASC")->queryAll();

        $income_data = Yii::$app->db->createCommand("SELECT SUM(amount) as money, DATE_FORMAT(date, '%Y-%m-01') as month 
                                                          FROM operations 
                                                          WHERE user_id=".Yii::$app->user->id." and type=2 
                                                          GROUP by DATE_FORMAT(date, '%Y-%m-01') 
                                                          ORDER by DATE_FORMAT(date, '%Y-%m-01') ASC")->queryAll();

        $income_data = $this->formatToHighcharts($income_data);
        $outcome_data = $this->formatToHighcharts($outcome_data);

        $operations_all_data = ["income" => json_encode($income_data), "outcome" => json_encode($outcome_data)];

        return $operations_all_data;
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
