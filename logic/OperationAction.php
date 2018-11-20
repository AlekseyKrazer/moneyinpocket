<?php

namespace app\logic;

use Yii;
use app\models\Exchange;
use app\models\Operations;

class OperationAction
{
    const TYPE_ARRAY = array(1,2,3);
    public $type;

    public function __construct($type)
    {
        if (!isset($type)) {
            $this->type = 1;
        } else {
            $this->type = $type;
        }
    }

    public function checkType()
    {
        if (!in_array($this->type, self::TYPE_ARRAY)) {
            return $this->redirect(Url::to(["accounting/index", "type" => 1]));
        } else {
            return $this->type;

        }
    }

    public function getOneRow($id)
    {
        if ($this->type == 3) {
            $operations = new Exchange();
            if (isset($id) and $id!='') {
                $operations = Exchange::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one();
            }
        } else {
            $operations = new Operations();
            if (isset($id) and $id!='') {
                $operations = Operations::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one();
            }
        }
        return $operations;
    }


    public function getDataForDay($date)
    {
        if ($this->type == 3) {
            $operations_data = Exchange::getHistoryData($date);

        } else {
            $operations_data = Operations::getHistoryData($date, $this->type);
        }
        return $operations_data;
    }


}