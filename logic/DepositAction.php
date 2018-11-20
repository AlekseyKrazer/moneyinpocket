<?php

namespace app\logic;

use app\models\Deposits;
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
            $this->view = 'deposit';
        } else {
            $this->title = 'Долг';
            $this->view = 'debt';
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
}