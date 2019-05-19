<?php

namespace app\controllers;

use app\models\Report;
use Yii;

class ReportController extends \yii\web\Controller
{
    /**
     * @return string
     */
    public function actionIndex()
    {
        $model         = new Report();
        $data['data']  = "";
        $data['total'] = "";

        if (Yii::$app->request->post()) {
            $model->load(Yii::$app->request->post());
            $data = Report::getDataChart(Yii::$app->request->post()['Report']['datetime'], Yii::$app->request->post()['Report']['type']);
        }

        return $this->render('index', compact("model", "data"));
    }
}
