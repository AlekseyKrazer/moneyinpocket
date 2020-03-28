<?php

namespace app\controllers;

use app\logic\DepositAction;
use app\models\Deposits;
use app\models\Exchange;
use app\models\Groups;
use app\models\Operations;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class DepositController extends \yii\web\Controller
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

    public function actionIndex($id = false, $debt = 0)
    {
        $groups = new Groups();

        if ($groups->load(Yii::$app->request->post())) {
            if ($groups->save()) {
                Yii::$app->session->setFlash('success', 'Группа успешна создана');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }

        $deposit_action = new DepositAction($debt);

        $deposit = new Deposits();
        if ($deposit->load(Yii::$app->request->post())) {
            if ($deposit->save()) {
                Yii::$app->session->setFlash('success', $deposit_action->title.' успешно создан');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }

        $lang = $deposit_action->getParams();

        return $this->render('deposit', compact('groups', 'deposit', 'debt', 'lang'));
    }

    public function actionGroupUpdate($id, $debt =0)
    {
        $groups = Groups::find()->where(['id' => $id])->one();

        if ($groups->load(Yii::$app->request->post())) {
            if ($groups->save()) {
                Yii::$app->session->setFlash('success', 'Группа успешна обновлена');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }


        $deposit_action = new DepositAction($debt);
        $lang = $deposit_action->getParams();

        return $this->render('update_group', compact('groups', 'debt', 'lang'));
    }

    public function actionUpdate($id, $debt = 0)
    {
        $groups = new Groups();

        if ($groups->load(Yii::$app->request->post())) {
            if ($groups->save()) {
                Yii::$app->session->setFlash('success', 'Группа успешна создана');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }
        $deposit_action = new DepositAction($debt);
        $id = $deposit_action->getId($id);

        $deposit = Deposits::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one();

        if (empty($deposit)) {
            Yii::$app->session->setFlash('error', 'Ошибка при обновлении #301');
            return $this->redirect([['deposit/index', 'debt' => $debt]]);
        } else {
            if ($deposit->load(Yii::$app->request->post())) {
                if ($deposit->save()) {
                    Yii::$app->session->setFlash('success', $deposit_action->title.' успешно обновлен');
                    return $this->redirect(['deposit/index', 'debt' => $debt]);
                } else {
                    Yii::$app->session->setFlash('error', 'Error!');
                }
            }
        }

        //Если был инициирован перенос операций - выполняем их
        if (isset(Yii::$app->request->post()['Transfer'])) {
            Deposits::depositTransfer(Yii::$app->request->post()['Transfer']);
        }

        $lang = $deposit_action->getParams();

        //Показываем количество операций по этому депозиту
        $count = Operations::find()->where(['deposit_id' => $id, 'user_id' => Yii::$app->user->id])->count();
        $count_exchange = Exchange::find()->where(['or','deposit_to='.$id,'deposit_from='.$id])->count();
        $arr_count=['count_operations' => $count, 'count_exchange' => $count_exchange];

        return $this->render('deposit', compact('groups', 'deposit', 'debt', 'arr_count', 'lang'));
    }


    public function actionDelete($id, $debt = 0)
    {
        $deposit_action = new DepositAction($debt);
        $id = $deposit_action->getId($id);

        $count = Operations::find()->where(['deposit_id' => $id, 'user_id' => Yii::$app->user->id])->count();
        $count_exchange = Exchange::find()->where(['or','deposit_to='.$id,'deposit_from='.$id])->count();

        if ($count==0 and $count_exchange==0) {
            $model = Deposits::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id, 'debt' => $debt])->one();
            if (empty($model)) {
                Yii::$app->session->setFlash('error', 'Ошибка при удалении #301');
            } else {
                if ($model->delete()) {
                    Yii::$app->session->setFlash('success', $deposit_action->title.' успешно удален');
                } else {
                    Yii::$app->session->setFlash('error', 'Error!');
                }
            }
        } else {
            Yii::$app->session->setFlash('error', 'К этому счету относится '.$count.' операций и '.$count_exchange.' операций обмена, которые надо либо удалить, либо перенести в другой счет. Так же счет можно просто скрыть.');
        }
        return $this->redirect(['deposit/index', 'debt' => $debt]);
    }

    public function actionIndex2()
    {
        return $this->render('index');
    }

}
