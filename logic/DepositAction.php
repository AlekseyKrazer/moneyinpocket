<?php

namespace app\logic;

use app\models\Deposits;
use Yii;
use yii\helpers\ArrayHelper;

class DepositAction
{
    public $title;
    public $debt;
    public $view;

    public function __construct($debt)
    {
        $this->debt=$debt;
        if ($this->debt == 0) {
            $this->title = 'Счет';
        } else {
            $this->title = 'Долг';
        }
    }

    public function getId($id)
    {
        $id=str_replace("dep_", "", $id);
        return $id;
    }


    public function getData()
    {
        if ($this->debt == 0) {
            $dep = Deposits::getAllDepositWithoutDebt();
            $dep = ArrayHelper::index($dep, "id");
        } else {
            $dep = Deposits::getAllDepositWithDebt();
            $dep = ArrayHelper::index($dep, "id");
        }
        return $dep;
    }

    public function getParams()
    {
        if ($this->debt == 0) {
            $lang_array['source'] = 'deposit';
            $lang_array['title'] = 'Счета';
            if (Yii::$app->controller->module->requestedRoute == 'deposit/update') {
                $lang_array['button'] = 'Редактировать счет';
                $lang_array['title_right'] = 'Редактирование счета';
            } else {
                $lang_array['button'] = 'Создать счет';
                $lang_array['title_right'] = 'Создание счета';
            }
        } else {
            $lang_array['source'] = 'debt';
            $lang_array['title'] = 'Долги/Займы';
            if (Yii::$app->controller->module->requestedRoute == 'deposit/update') {
                $lang_array['button'] = 'Редактировать долг\займ';
                $lang_array['title_right'] = 'Редактирование долга\займа';
            } else {
                $lang_array['button'] = 'Создать долг\займ';
                $lang_array['title_right'] = 'Создание долга';
            }
        }
        return $lang_array;
    }
}