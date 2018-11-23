<?php
/**
 * Created by PhpStorm.
 * User: crazer
 * Date: 15.10.2018
 * Time: 15:51
 */


namespace app\components;

use app\models\Categories;
use app\models\Groups;
use Yii;
use yii\base\Widget;
use yii\helpers\ArrayHelper;

class MenuWidget extends Widget
{

    public $tpl;
    public $data;
    public $tree;
    public $menuHtml;
    public $model;
    public $source;
    public $debt;

    public function init()
    {
        parent::init(); // TODO: Change the autogenerated stub
        if ($this->tpl === null) {
            $this->tpl = 'select';
        }
        $this->tpl .= ".php";
    }

    public function run()
    {
        $total = '';

        /**
        Выгружаем данные из источника source.
        Если выгрузки не происходит, значит данные должны передаваться сразу в data при инициализации виджета.
        */
        switch ($this->source) {
            case 'income':
                $this->data = Categories::find()->indexBy("id")->asArray()->where(['type' => 2, 'user_id' => Yii::$app->user->id])->all();
                $this->source = 'categories';
                break;
            case 'groups':
                $this->data = Groups::find()->indexBy("id")->where(['debt' => 0])->asArray()->all();
                break;
            case 'deposit':
                $this->debt = 0;
                break;
            case 'debt':
                $this->debt = 1;
                break;
            case 'deposit_exchange':
                break;
            case 'total-deposit':
                $this->data = Yii::$app->db->createCommand(
                    "Select * from 
                                (SELECT  CONCAT('dep_',id) as id, `name`, `group_id` as parent_id, images, 'dep' as type from deposits WHERE debt=0
                                UNION
                                SELECT id, name, parent_id, '' as images, 'cat' as type from groups WHERE debt=0) as one
                                
                                LEFT JOIN
                                
                                (Select SUM(total) as total,CONCAT('dep_',deposit_id) as deposit_id from 
                                      (Select SUM(total) as total, deposit as deposit_id from 
                                            (Select -1*SUM(amount) as total, deposit_from as deposit from exchange WHERE user_id=" . Yii::$app->user->id . " GROUP by deposit
                                            UNION
                                            Select SUM(amount) as total, deposit_to as deposit from exchange WHERE user_id=" . Yii::$app->user->id . " GROUP by deposit
                                            ) as exchange 
                                            JOIN deposits d ON exchange.deposit=d.id GROUP by deposit_id
                                      UNION
                                      Select total, dep.id as deposit_id from deposits dep 
                                      JOIN (Select SUM(amount) as total, deposit_id from operations WHERE user_id=" . Yii::$app->user->id . " GROUP by deposit_id
                                            ) as operations ON operations.deposit_id=dep.id
                                      UNION
                                      SELECT start_sum AS total, id AS deposit_id FROM deposits
                                      ) AS all_operations 
                                GROUP by deposit_id
                                ) AS two 
                                ON one.id=two.deposit_id"
                )->queryAll();
                $this->data = ArrayHelper::index($this->data, "id");

                //Записывает общее значение для родительской категории
                foreach ($this->data as $k) {
                    if ($k['parent_id'] == 0) {
                        $total = $k['total'] + $total;
                    }
                }
                break;
            case 'total-owe':
                $this->data = Yii::$app->db->createCommand(
                    "Select * from 
                                      (SELECT  CONCAT('dep_',id) as id, `name`, `group_id` as parent_id, images, 'dep' as type from deposits WHERE debt=1
                                      UNION
                                      SELECT id, name, parent_id, '' as images, 'cat' as type from groups WHERE debt=1) as one
                                      LEFT JOIN
                                      (Select SUM(total) as total,CONCAT('dep_',deposit_id) as deposit_id from 
                                          (Select SUM(total) as total, deposit as deposit_id 
                                          FROM (
                                                Select -1*SUM(amount) as total, deposit_from as deposit 
                                                FROM exchange WHERE user_id=" . Yii::$app->user->id . " GROUP by deposit
                                                UNION
                                                SELECT SUM(amount) as total, deposit_to as deposit 
                                                FROM exchange WHERE user_id=" . Yii::$app->user->id . " GROUP by deposit
                                               ) AS a 
                                               JOIN deposits d ON a.deposit=d.id WHERE d.debt=1 GROUP by deposit_id
                                              UNION
                                              SELECT total, dep.id as deposit_id from deposits dep 
                                              JOIN (
                                              Select SUM(amount) as total, deposit_id FROM operations 
                                              WHERE user_id=" . Yii::$app->user->id . " GROUP by deposit_id
                                              ) as dd ON dd.deposit_id=dep.id WHERE dep.debt=1
                                              UNION
                                              SELECT start_sum as total, id as deposit_id from deposits
                                          ) as b 
                                            GROUP by deposit_id) as two ON one.id=two.deposit_id WHERE total>0 or total IS NULL"
                )->queryAll();
                foreach ($this->data as $k) {
                    $total = $k['total'] + $total;
                }
                $this->data = ArrayHelper::index($this->data, "id");
                break;
            case 'total-debt':
                $this->data = Yii::$app->db->createCommand(
                    "Select * from 
                                  (SELECT  CONCAT('dep_',id) as id, `name`, `group_id` as parent_id, images, 'dep' as type from deposits WHERE debt=1
                                  UNION
                                  SELECT id, name, parent_id, '' as images, 'cat' as type from groups WHERE debt=1) as one
                                  LEFT JOIN
                                  (Select SUM(total) as total,CONCAT('dep_',deposit_id) as deposit_id from 
                                      (Select SUM(total) as total, deposit as deposit_id 
                                      FROM (
                                            Select -1*SUM(amount) as total, deposit_from as deposit 
                                            FROM exchange WHERE user_id=" . Yii::$app->user->id . " GROUP by deposit
                                            UNION
                                            SELECT SUM(amount) as total, deposit_to as deposit 
                                            FROM exchange WHERE user_id=" . Yii::$app->user->id . " GROUP by deposit
                                           ) AS a 
                                           JOIN deposits d ON a.deposit=d.id WHERE d.debt=1 GROUP by deposit_id
                                          UNION
                                          SELECT total, dep.id as deposit_id from deposits dep 
                                          JOIN (
                                          Select SUM(amount) as total, deposit_id FROM operations 
                                          WHERE user_id=" . Yii::$app->user->id . " GROUP by deposit_id
                                          ) as dd ON dd.deposit_id=dep.id WHERE dep.debt=1
                                          UNION
                                          SELECT start_sum as total, id as deposit_id from deposits
                                      ) as b 
                                        GROUP by deposit_id) as two ON one.id=two.deposit_id WHERE total<0 OR total is NULL"
                )->queryAll();

                foreach ($this->data as $k) {
                    $total = $k['total'] + $total;
                }
                $this->data = ArrayHelper::index($this->data, "id");
                break;
            case 'groups-debt':
                $this->data = Groups::find()->indexBy("id")->where(['debt' => 1])->asArray()->all();
                break;
            default:
                $this->data = Categories::find()->indexBy("id")->asArray()->where(['type' => 1, 'user_id' => Yii::$app->user->id])->all();
                $this->source = 'categories';
        }
        $this->tree = $this->getTree();

        $this->menuHtml = $this->getMenuHtml($this->tree);

        if ($total < 0) {
            $total = "Итого: <span style=\"color='red'\">" . Yii::$app->formatter->asCurrency($total) . "</span>";
        } elseif ($total > 0) {
            $total = "Итого: <span style=\"color='red'\">" . Yii::$app->formatter->asCurrency($total) . "</span>";
        }

        return $this->menuHtml . $total;
    }

    /**
     * Функция для построения дерева по полю parent_id
     * */
    protected function getTree()
    {
        $tree = [];
        foreach ($this->data as $id => &$node) {
            if (!$node['parent_id']) {
                $tree[$id] = &$node;
            } else {
                $this->data[$node['parent_id']]['childs'][$node['id']] = &$node;
            }
        }
        return $tree;
    }

    protected function getMenuHtml($tree, $tab = false)
    {
        $str = '';
        foreach ($tree as $category) {
            $str .= $this->catToTemplate($category, $tab);
        }
        return $str;
    }

    protected function catToTemplate($category, $tab)
    {
        ob_start();
        include __DIR__ . '/menu_tpl/' . $this->tpl;
        return ob_get_clean();
    }


}