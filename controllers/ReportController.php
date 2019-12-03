<?php

namespace app\controllers;

use app\models\Report;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class ReportController extends \yii\web\Controller
{
    /**
     * @return array
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                    [
                        'allow' => false,
                        'roles' => ['?'],
                    ],
                ],
            ],
            'verbs'  => [
                'class'   => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }
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
            $data = $model->getData($model);
        }

        $operation = $model->getLastYearOperation();

        return $this->render('index', compact("model", "data", "operation"));
    }
}
