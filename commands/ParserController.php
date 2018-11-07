<?php
/**
 * Created by PhpStorm.
 * User: crazer
 * Date: 02.11.2018
 * Time: 11:23
 */

namespace app\commands;

use yii\console\Controller;
use Yii;
use yii\helpers\ArrayHelper;

class ParserController extends Controller
{
    public function actionParser()
    {


        $dep = Yii::$app->db->createCommand('SELECT * from deposits')->queryAll();
        $dep=ArrayHelper::index($dep,"name");

        $cat = Yii::$app->db->createCommand('SELECT id, name from categories')->queryAll();
        $cat=ArrayHelper::index($cat,"name");
        echo "\n\n";



        $file=file_get_contents("@web/../commands/drebedengi.html");

        $pattern='/<div class="pRec".*?type.*?title="(.*?)".*?<div class="limited wht".*?<span.*?>(.*?)<\/span>.*?<input.*?<input.*?value="(.*?)".*?class="tMini tGray".*?>(.*?)<\/div>.*?<div class="tMini tGray".*?>(.*?)<\/div>/is';

        preg_match_all($pattern,$file,$matches);
        $sum=0;
        $num=0;
        echo count($matches[1])."<BR>";
        for ($i=0; $i<count($matches[1]); $i++)
        //for ($i=0; $i<12; $i++)
        {
            $deposit=$matches[1][$i];
            $category=$matches[2][$i];
            $amount=$matches[3][$i];

            $datetime=str_replace("&nbsp;","",strip_tags($matches[4][$i]));
            if (substr_count($datetime,"2017")>0 or substr_count($datetime,"2016")>0
            or substr_count($datetime,"2015")>0 or substr_count($datetime,"2014")>0
            or substr_count($datetime,"2013")>0 or substr_count($datetime,"2012")>0
                or substr_count($datetime,"2011")>0
            ) {
                $datetime = str_replace(" янв ", "-01-", $datetime);
                $datetime = str_replace(" фев ", "-02-", $datetime);
                $datetime = str_replace(" мар ", "-03-", $datetime);
                $datetime = str_replace(" апр ", "-02-", $datetime);
                $datetime = str_replace(" мая ", "-05-", $datetime);
                $datetime = str_replace(" июн ", "-06-", $datetime);
                $datetime = str_replace(" июл ", "-07-", $datetime);
                $datetime = str_replace(" авг ", "-08-", $datetime);
                $datetime = str_replace(" сен ", "-09-", $datetime);
                $datetime = str_replace(" окт ", "-10-", $datetime);
                $datetime = str_replace(" ноя ", "-11-", $datetime);
                $datetime = str_replace(" дек ", "-12-", $datetime);
            } else {
                $datetime = str_replace(" янв ", "-01-2018", $datetime);
                $datetime = str_replace(" фев ", "-02-2018", $datetime);
                $datetime = str_replace(" мар ", "-03-2018", $datetime);
                $datetime = str_replace(" апр ", "-02-2018", $datetime);
                $datetime = str_replace(" мая ", "-05-2018", $datetime);
                $datetime = str_replace(" июн ", "-06-2018", $datetime);
                $datetime = str_replace(" июл ", "-07-2018", $datetime);
                $datetime = str_replace(" авг ", "-08-2018", $datetime);
                $datetime = str_replace(" сен ", "-09-2018", $datetime);
                $datetime = str_replace(" окт ", "-10-2018", $datetime);
                $datetime = str_replace(" ноя ", "-11-2018", $datetime);
                $datetime = str_replace(" дек ", "-12-2018", $datetime);
            }
            $datetime=date("Y-m-d H:i:s",strtotime($datetime));
            $date=date("Y-m-d",strtotime($datetime));

            $comment=strip_tags($matches[5][$i]);

            $category_id=$cat[$category]['id'];
            $deposit_id=$dep[$deposit]['id'];

            if ($amount>0){
                $type=2;
            }
            if ($amount<0) {
                $type=1;
            }

            if (substr_count($deposit,"Из")>0) {
                $type=3;
                $amount=abs($amount);
                $dep_array=explode("<br>",$category);
                $deposit_from=$dep_array[0];
                $deposit_to=$dep_array[1];
                $deposit_from_id=$dep[$deposit_from]['id'];
                $deposit_to_id=$dep[$deposit_to]['id'];

                echo "EXCHANGE!!!\n";
                echo "Откуда: " . $deposit . "\n";
                echo "Deposit_from: ".$deposit_from."\n";
                echo "Deposit_from_id: ".$deposit_from_id."\n";
                echo "Deposit_to: ".$deposit_to."\n";
                echo "Deposit_to_id: ".$deposit_to_id."\n";
                echo "Категория затрат: " . $category . "\n";
                echo "Сумма: " . $amount . "\n";
                echo "Дата: " . $date . "\n";
                echo "Время: " . $datetime . "\n";
                echo "Комментарий: " . $comment . "\n\n";

                $sql_exchange="INSERT INTO `exchange`(`date`, `datetime`, `amount`, `deposit_from`, `deposit_to`, `user_id`, `comment`) 
                VALUES ('".$date."','".$datetime."',$amount,$deposit_from_id,$deposit_to_id,1,'".$comment."')";
                echo $sql_exchange."\n";
                Yii::$app->db->createCommand($sql_exchange)->execute();

            } else {

/*                    echo "Откуда: " . $deposit . "\n";
                    echo "Type: " . $type . "\n";
                    echo "Категория затрат: " . $category . "\n";
                    echo "Сумма: " . $amount . "\n";
                    echo "Дата: " . $date . "\n";
                    echo "Время: " . $datetime . "\n";
                    echo "Комментарий: " . $comment . "\n";
                    echo "Cat_id: ". $category_id."\n";
                    echo "Dep_id: ".$deposit_id."\n";
                $sql_operation="INSERT INTO `operations`(`date`, `datetime`, `user_id`, `type`, `amount`, `deposit_id`, `category_id`, 
                `comment`) VALUES ('".$date."','".$datetime."',1,$type,$amount,$deposit_id,$category_id,'".$comment."')";
                Yii::$app->db->createCommand($sql_operation)->execute();
                    echo $sql_operation."\n";*/
//                if (isset($cat[trim($category)])) {
////                    echo "YES!";
//                } else {
//                    $sql="INSERT INTO `categories`(`name`, `parent_id`, `user_id`, `position`, `type`)
//                      VALUES ('".$category."',0,1,NULL,$type)";
//                    echo $sql."\n";
//                    Yii::$app->db->createCommand($sql)->execute();
//                    $cat[$category]['id']=1;
//                }
/*                if (isset($dep[$deposit])) {
                    echo "YES!";
                } else {
                    $sql="INSERT INTO `deposits`(`name`, `images`, `group_id`, `user_id`, `position`, `debt`, `start_sum`) 
                    VALUES ('".$deposit."','',0,1,NULL,0,0)";
                    echo $sql."\n";
                    Yii::$app->db->createCommand($sql)->execute();
                    $dep[$deposit]['id']=1;
                }*/
            }
        }
        echo $sum;
    }
}