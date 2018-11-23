<?php

namespace app\controllers;

use app\logic\FormProcessing;
use app\logic\OperationAction;
use app\models\Deposits;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Cookie;

class AccountingController extends Controller
{

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
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    private function yiiSetCookie($name, $value, $expire = 120)
    {
        //Записываем куку
        Yii::$app->response->cookies->add(
            new Cookie(
                [
                    'name' => $name,
                    'value' => $value,
                    'expire' => time() + $expire,
                ]
            )
        );
    }

    public function actionHistory($type, $date = false)
    {
        $date=date("Y-m-d", strtotime($date));

        $operation_action = new OperationAction($type);
        $type = $operation_action->checkType();
        $data_for = $operation_action->getDataForDay($date);

        /*
        Запись куки, чтобы при загрузке в календаре показывалась выбранная для history дата
        */
        if (date("Y-m-d", strtotime($date))!=date("Y-m-d", time())) {
            $this->yiiSetCookie("datetime", date("Y-m-d ", strtotime($date)).date("H:i", time()), 120);
        } else {
            Yii::$app->response->cookies->remove("datetime");
        }

        return $this->renderPartial("history", compact('date', 'data_for', 'type'));
    }

    public function actionNow($type)
    {
        Yii::$app->response->cookies->remove("datetime");
        $operation_action = new OperationAction($type);
        $type = $operation_action->checkType();

        return $this->redirect(Url::to(["accounting/index", "type" => $type]));
    }

    public function actionIndex($id = false, $date = false)
    {

        $operation_action = new OperationAction(Yii::$app->getRequest()->getQueryParam('type'));

        $type = $operation_action->checkType();
        $operations = $operation_action->getOneRow($id);

        if (Yii::$app->request->post()) {
            //Валидируем данные и перегоняем в нужный нам формат.
            $operation_validator = new FormProcessing($operations->formName(), Yii::$app->request->post());
            $add_array = $operation_validator->checkOperationForm();
        }

        if (isset($add_array) && $operations->load($add_array)) {
            if ($operations->save()) {
                Yii::$app->session->setFlash('success', 'Операция успешно записана');
                $this->yiiSetCookie("datetime", date("Y-m-d H:i", strtotime($add_array[$operations->formName()]['datetime'])), 120);

                if (isset($add_array[$operations->formName()]['deposit_id'])) {
                    $this->yiiSetCookie("deposit", $add_array[$operations->formName()]['deposit_id'], 3600 * 24);
                }
                return $this->redirect(Url::to(["accounting/index", "type" => $type]));
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }

        //Подставляем депозит из куки, чтобы пользователю не надо было каждый раз выбирать его.
        if ($id === false and isset(Yii::$app->request->cookies['deposit']) and $type!=3) {
            $operations->deposit_id=Yii::$app->request->cookies['deposit']->value;
        }

        //Решаем, на какую дату нам возвращаться в календаре
        if ($operations->datetime!='') {
            $this->yiiSetCookie("datetime", date("Y-m-d H:i", strtotime($operations->datetime)), 120);
            $datetime=$operations->datetime;
            $date = date("Y-m-d", strtotime($operations->datetime));
        } else {
            if (!isset(Yii::$app->request->cookies['datetime'])) {
                $date = date("Y-m-d", time());
                $datetime = date("Y-m-d H:i", time());
            } else {
                $datetime = Yii::$app->request->cookies['datetime']->value;
                $date = date("Y-m-d", strtotime(Yii::$app->request->cookies['datetime']->value));
            }
        }

        //Выгружаем все необходимые данные

        $operations_data = $operation_action->getDataForDay($date);

        $dep=Deposits::getAllDepositWithoutDebt();
        $dep=ArrayHelper::index($dep, "id");

        $debts = Deposits::getAllDepositWithDebt();
        $debt =ArrayHelper::index($debts, "id");

        return $this->render('index', compact('operations', 'dep', 'debt', 'operations_data', 'datetime', 'type'));
    }


    public function actionDelete($id, $type)
    {
        $operation_action = new OperationAction($type);
        $model = $operation_action->getOneRow($id);

        if (empty($model)) {
            Yii::$app->session->setFlash('error', 'Ошибка при удалении #301');
        } else {
            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'Операция успешно удалена');
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }
        return $this->redirect(['accounting/index', 'type' => $type]);
    }

    public function actionPlanning()
    {
        return $this->render('planning');
    }

}
