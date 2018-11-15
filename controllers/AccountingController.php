<?php

namespace app\controllers;

use yii\helpers\ArrayHelper;
use yii\web\Controller;
use app\models\Operations;
use yii\helpers\Url;
use app\models\Categories;
use Yii;
use app\models\Groups;
use app\models\Deposits;
use app\models\Exchange;
use yii\web\Cookie;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

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

    private function YiiSetCookie($name, $value, $expire = 120)
    {
        //Записываем куку
        Yii::$app->response->cookies->add(
            new Cookie(
                [
                'name' => $name,
                'value' => $value,
                'expire' => time()+$expire,
                ]
            )
        );
    }

    public function actionTest($type, $date = false)
    {
        //Функция выборки данных при изменении даты в календарике. Инициируется через ajax.
        if (!in_array($type, [1, 2, 3])) {
            return $this->redirect(Url::to(["accounting/index", "type" => 1]));
        }
        $date=date("Y-m-d", strtotime($date));

        if ($type==3) {
            //Если обмен, то используется этот SQL запрос.
            $data_for = Exchange::find()
                ->select(
                    ['exchange.id', 'exchange.date', 'exchange.datetime', 'exchange.amount', 'exchange.deposit_from', 'd.name as deposit_from_name',
                    'd.images as deposit_from_images', 'exchange.deposit_to', 'dd.name as deposit_to_name', 'dd.images as deposit_to_images',
                    'exchange.comment']
                )
                ->join('INNER JOIN', 'deposits as d', '`exchange`.`deposit_from` = `d`.`id` AND `exchange`.`user_id` = `d`.`user_id`')
                ->join('INNER JOIN', 'deposits as dd', '`exchange`.`deposit_to` = `dd`.`id` AND `exchange`.`user_id` = `dd`.`user_id`')
                ->where(['date' => $date, 'exchange.user_id' => Yii::$app->user->id])
                ->orderBy("datetime")
                ->asArray()->all();
        } else {
            //Если расходы или доходы, то дергаем все данные по ним.
            $data_for = Yii::$app->db->createCommand(
                "
              SELECT op.id, op.date, op.datetime, op.amount, op.deposit_id, dep.name as deposit_name, dep.images, op.category_id, 
              cat.name as category_name, op.comment 
              FROM operations op 
              JOIN categories cat  ON cat.id=op.category_id and cat.user_id=op.user_id
              JOIN deposits dep ON op.deposit_id=dep.id and dep.user_id=op.user_id
              WHERE op.user_id=" . Yii::$app->user->id . " and date='" . $date . "' and op.type=" . $type . " ORDER by datetime ASC"
            )->queryAll();
        }

        /*
        Записываем куку. Если выбранный день - это сегодня, то кука удаляется, чтобы у нас был реалтайм.
        Если нет - записываем куку
        */
        if (date("Y-m-d", strtotime($date))!=date("Y-m-d", time())) {
            $this->YiiSetCookie("datetime_1", date("Y-m-d H:i", strtotime($date)), 120);
        } else {
            Yii::$app->response->cookies->remove("datetime_1");
        }

        return $this->renderPartial("test", compact('date', 'data_for', 'type'));
    }

    public function actionIndex($id = false, $date = false)
    {
        //Проверка на тип, должен быть только тот, что в массиве.
        if (!isset($_GET['type'])) {
            $_GET['type']=1;
            $type=1;
        } elseif (!in_array($_GET['type'], [1, 2, 3])) {
            Yii::$app->response->cookies->remove("datetime_1");
            $type=1;
            return $this->redirect(Url::to(["accounting/index", "type" => 1]));
        } else {
            $type=$_GET['type'];
        }


        //Если обмен, то используем другую модель
        if ($type == 3) {
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

        $add_array=array();

        if (Yii::$app->request->post()) {
            //Проверка и обработка пришедших из формы данных. Имя формы может быть разным.
            $add_array['_csrf'] = Yii::$app->request->post()['_csrf'];
            $add_array[$operations->formName()]['datetime'] = Yii::$app->request->post()[$operations->formName()]['datetime'];
            $add_array[$operations->formName()]['date'] = date("Y-m-d", strtotime(Yii::$app->request->post()[$operations->formName()]['datetime']));
            $date = $add_array[$operations->formName()]['date'];
            $add_array[$operations->formName()]['type'] = Yii::$app->request->post()[$operations->formName()]['type'];
            $add_array[$operations->formName()]['amount'] = Yii::$app->request->post()[$operations->formName()]['amount'];

            //Поле amount может выполнять математические действия. Удаляем все лишнее и выполняем действие.
            $add_array[$operations->formName()]['amount'] = str_replace(',', ".", $add_array[$operations->formName()]['amount']);
            $equation = preg_replace('[^0-9\+-\*\/\(\) ]', '', $add_array[$operations->formName()]['amount']);
            if (!empty($equation)) {
                eval('$total = (' . $equation . ');');
                $add_array[$operations->formName()]['amount'] = $total;
            }
            if ($add_array[$operations->formName()]['type'] == 1) {
                $add_array[$operations->formName()]['amount'] = -1 * $add_array[$operations->formName()]['amount'];
            }
            //

            $add_array[$operations->formName()]['user_id'] = Yii::$app->user->id;

            //При обмене у нас две переменных deposit_from - откуда взяли деньги и deposit_to - куда положили. Категории нет.
            if ($add_array[$operations->formName()]['type'] == 3) {
                $add_array[$operations->formName()]['deposit_from'] = str_replace("dep_", "", Yii::$app->request->post()[$operations->formName()]['deposit_id']);
                $add_array[$operations->formName()]['deposit_to'] = str_replace("dep_", "", Yii::$app->request->post()[$operations->formName()]['deposit_id2']);
            } else {
                //При расходе и доходе есть категория дохода\расхода и депозит с которым провели операцию.
                $add_array[$operations->formName()]['category_id'] = Yii::$app->request->post()[$operations->formName()]['category_id'];
                $add_array[$operations->formName()]['deposit_id'] = str_replace("dep_", "", Yii::$app->request->post()[$operations->formName()]['deposit_id']);
            }

            $add_array[$operations->formName()]['comment'] = Yii::$app->request->post()[$operations->formName()]['comment'];

            if (!isset($total) or $total <= 0) {
                Yii::$app->session->setFlash('error', 'Сумма не может быть ноль и меньше');
                unset($add_array);
                $add_array = array();
            }
        }

        if ($operations->load($add_array)) {
            if ($operations->save()) {
                    Yii::$app->session->setFlash('success', 'Операция успешно записана');
                    $this->YiiSetCookie("datetime_1", date("Y-m-d H:i", strtotime($add_array[$operations->formName()]['datetime'])), 120);

                if (isset($add_array[$operations->formName()]['deposit_id'])) {
                          $this->YiiSetCookie("deposit", $add_array[$operations->formName()]['deposit_id'], 3600*24);
                }
                    return $this->redirect(Url::to(["accounting/index", "type" => $type]));
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }



        $dep = Yii::$app->db->createCommand(
            'SELECT  CONCAT(\'dep_\',id) as id, `name`, `group_id` as parent_id, images, \'dep\' as type from deposits WHERE debt=0
                                                  UNION
                                                  SELECT id, name, parent_id, \'\' as images, \'cat\' as type from groups WHERE debt=0'
        )->queryAll();
        $dep=ArrayHelper::index($dep, "id");

        $debts = Yii::$app->db->createCommand(
            'SELECT  CONCAT(\'dep_\',id) as id, `name`, `group_id` as parent_id, images, \'dep\' as type from deposits WHERE debt=1
                                                  UNION
                                                  SELECT id, name, parent_id, \'\' as images, \'cat\' as type from groups WHERE debt=1'
        )->queryAll();

        $debt =ArrayHelper::index($debts, "id");

        //Тестим куку
        if (($type==1 or $type==2) and $operations->deposit_id=='' and isset(Yii::$app->request->cookies['deposit'])) {
            $operations->deposit_id=Yii::$app->request->cookies['deposit']->value;
        }

        if ($operations->datetime!='') {
            $this->YiiSetCookie("datetime_1", date("Y-m-d H:i", strtotime($operations->datetime)), 120);
            $datetime=$operations->datetime;
            $date = date("Y-m-d", strtotime($operations->datetime));
        } else {
            if (!isset(Yii::$app->request->cookies['datetime_1'])) {
                $date = date("Y-m-d", time());
                $datetime = date("Y-m-d H:i", time());
            } else {
                $datetime = Yii::$app->request->cookies['datetime_1']->value;
                $date = date("Y-m-d", strtotime(Yii::$app->request->cookies['datetime_1']->value));
            }
        }

        if ($type == 3) {
            $operations_data = Exchange::find()
                ->select(
                    ['exchange.id', 'exchange.date', 'exchange.datetime', 'exchange.amount', 'exchange.deposit_from', 'd.name as deposit_from_name',
                    'd.images as deposit_from_images', 'exchange.deposit_to', 'dd.name as deposit_to_name', 'dd.images as deposit_to_images',
                    'exchange.comment']
                )
                ->join('INNER JOIN', 'deposits as d', '`exchange`.`deposit_from` = `d`.`id` AND `exchange`.`user_id` = `d`.`user_id`')
                ->join('INNER JOIN', 'deposits as dd', '`exchange`.`deposit_to` = `dd`.`id` AND `exchange`.`user_id` = `dd`.`user_id`')
                ->where(['date' => $date, 'exchange.user_id' => Yii::$app->user->id])
                ->asArray()->all();

        } else {
            $operations_data = Yii::$app->db->createCommand(
                "
              SELECT op.id, op.date, op.datetime, op.amount, op.deposit_id, dep.name as deposit_name, dep.images, op.category_id, 
              cat.name as category_name, op.comment 
              FROM operations op 
              JOIN categories cat  ON cat.id=op.category_id and cat.user_id=op.user_id
              JOIN deposits dep ON op.deposit_id=dep.id and dep.user_id=op.user_id
              WHERE op.user_id=" . Yii::$app->user->id . " and date='" . $date . "' and op.type=" . $type . " ORDER by datetime"
            )->queryAll();
        }

        return $this->render('index', compact('operations', 'dep', 'debt', 'operations_data', 'datetime'));
    }


    public function actionDelete($id, $type)
    {

        if ($type==3) {
            $model = Exchange::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one();
        } else {
            $model = Operations::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one();
        }
        if (empty($model)) {
            Yii::$app->session->setFlash('error', 'Ошибка при удалении #301');
            return $this->redirect(['accounting/index', 'type' => $type]);
        } else {
            if ($model->delete()) {
                Yii::$app->session->setFlash('success', 'Операция успешно удалена');
                return $this->redirect(['accounting/index', 'type' => $type]);
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }
    }

    public function actionCategory()
    {

        $model = new Categories();


        if ($model->load(Yii::$app->request->post())) {
            //Здесь надо написать сравнение, чтобы user_id у залогиненного совпадал с пришедшим из формы
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Категория успешно создана');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }

        return $this->render('category', compact('model'));
    }

    public function actionCategoryUpdate($id)
    {

        $model = Categories::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one();
        if (empty($model)) {
            Yii::$app->session->setFlash('error', 'Ошибка при обновлении #301');
            return $this->redirect(['accounting/category']);
        } else {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Категория успешно обновлена');
                    return $this->redirect(['accounting/category']);
                } else {
                    Yii::$app->session->setFlash('error', 'Error!');
                }
            }
            return $this->render('category', compact('model'));
        }
    }

    public function actionCategoryDelete($id)
    {
        $count = Operations::find()->where(['category_id' => $id, 'user_id' => Yii::$app->user->id])->count();

        if ($count==0) {
            $model = Categories::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one();
            if (empty($model)) {
                Yii::$app->session->setFlash('error', 'Ошибка при удалении #301');
                return $this->redirect(['accounting/category']);
            } else {
                if ($model->delete()) {
                    Yii::$app->session->setFlash('success', 'Категория успешно удалена');
                    return $this->redirect(['accounting/category']);
                } else {
                    Yii::$app->session->setFlash('error', 'Error!');
                }
            }
        } else {
            Yii::$app->session->setFlash('error', 'В этой категории хранится '.$count.' операций, которые надо либо удалить, либо перенести в другую категорию. Так же категорию можно скрыть.');
            return $this->redirect(['accounting/category']);
        }
    }

    public function actionIncome()
    {
        $model = new Categories();

        if ($model->load(Yii::$app->request->post())) {
            //Здесь надо написать сравнение, чтобы user_id у залогиненного совпадал с пришедшим из формы
            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Источник дохода успешно создан');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }

        return $this->render('income', compact('model'));
    }


    public function actionIncomeUpdate($id)
    {

        $model = Categories::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one();
        if (empty($model)) {
            Yii::$app->session->setFlash('error', 'Ошибка при обновлении доходов #301');
            return $this->redirect(['accounting/income']);
        } else {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Источник дохода успешно обновлен');
                    return $this->redirect(['accounting/income']);
                } else {
                    Yii::$app->session->setFlash('error', 'Error!');
                }
            }
            return $this->render('income', compact('model'));
        }
    }

    public function actionIncomeDelete($id)
    {
        $count = Operations::find()->where(['category_id' => $id, 'user_id' => Yii::$app->user->id])->count();

        if ($count==0) {
            $model = Categories::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one();
            if (empty($model)) {
                Yii::$app->session->setFlash('error', 'Ошибка при удалении источника дохода #301');
                return $this->redirect(['accounting/income']);
            } else {
                if ($model->delete()) {
                    Yii::$app->session->setFlash('success', 'Источник дохода успешно удален');
                    return $this->redirect(['accounting/income']);
                } else {
                    Yii::$app->session->setFlash('error', 'Error!');
                }
            }
        } else {
            Yii::$app->session->setFlash('error', 'К этому доходу относится '.$count.' операций, которые надо либо удалить, либо перенести в другую категорию доходов. Также доход можно скрыть.');
            return $this->redirect(['accounting/income']);
        }
    }


    public function actionDeposit($id = false)
    {
        $groups = new Groups();

        if ($groups->load(Yii::$app->request->post())) {
            //Здесь надо написать сравнение, чтобы user_id у залогиненного совпадал с пришедшим из формы
            if ($groups->save()) {
                Yii::$app->session->setFlash('success', 'Группа успешна создана');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }

        $deposit = new Deposits();
        if ($deposit->load(Yii::$app->request->post())) {
            //Здесь надо написать сравнение, чтобы user_id у залогиненного совпадал с пришедшим из формы
            if ($deposit->save()) {
                Yii::$app->session->setFlash('success', 'Источник успешно создан');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }


        $dep = Yii::$app->db->createCommand(
            'SELECT  CONCAT(\'dep_\',id) as id, `name`, `group_id` as parent_id, images, \'dep\' as type from deposits WHERE debt=0
                                                  UNION
                                                  SELECT id, name, parent_id, \'\' as images, \'cat\' as type from groups WHERE debt=0'
        )->queryAll();

        $dep=ArrayHelper::index($dep, "id");

        $debt=0;

        return $this->render('deposit', compact('groups', 'deposit', 'dep', 'debt'));
    }
    public function actionDepositUpdate($id)
    {
        $groups = new Groups();

        if ($groups->load(Yii::$app->request->post())) {
            //Здесь надо написать сравнение, чтобы user_id у залогиненного совпадал с пришедшим из формы
            if ($groups->save()) {
                Yii::$app->session->setFlash('success', 'Группа успешна создана');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }

        $id=str_replace("dep_", "", $id);

        $deposit = Deposits::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one();

        $count = Operations::find()->where(['deposit_id' => $id, 'user_id' => Yii::$app->user->id])->count();
        $count_exchange = Exchange::find()->where(['or','deposit_to='.$id,'deposit_from='.$id])->count();
        $arr_count=['count_operations' => $count, 'count_exchange' => $count_exchange];

        if (empty($deposit)) {
            Yii::$app->session->setFlash('error', 'Ошибка при обновлении счетов #301');
            return $this->redirect(['accounting/deposit']);
        } else {
            if ($deposit->load(Yii::$app->request->post())) {
                //Здесь надо написать сравнение, чтобы user_id у залогиненного совпадал с пришедшим из формы
                if ($deposit->save()) {
                    Yii::$app->session->setFlash('success', 'Источник успешно обновлен');
                    return $this->redirect(['accounting/deposit']);
                } else {
                    Yii::$app->session->setFlash('error', 'Error!');
                }
            }
        }

        if (Yii::$app->request->post()!=null) {
            if (Yii::$app->request->post()['Transfer']['deposit_from']==Yii::$app->request->post()['Transfer']['deposit_to']) {
                Yii::$app->session->setFlash('error', 'Нельзя переносить из одного счета в тот же самый');
            } else {
                $sql=array();
                $deposit_from=str_replace("dep_", "", Yii::$app->request->post()['Transfer']['deposit_from']);
                $deposit_to=str_replace("dep_", "", Yii::$app->request->post()['Transfer']['deposit_to']);
                $sql[]="UPDATE operations SET deposit_id=".$deposit_to." WHERE deposit_id=".$deposit_from;
                $sql[]="UPDATE exchange SET deposit_from=".$deposit_to." WHERE deposit_from=".$deposit_from;
                $sql[]="UPDATE exchange SET deposit_to=".$deposit_to." WHERE deposit_to=".$deposit_from;

                $transaction =  Yii::$app->db->beginTransaction();
                try {
                    foreach ($sql as $s) {
                        Yii::$app->db->createCommand($s)->execute();
                    }
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                Yii::$app->session->setFlash('success', 'Успешно переведено');
            }

        }

        $dep = Yii::$app->db->createCommand(
            'SELECT  CONCAT(\'dep_\',id) as id, `name`, `group_id` as parent_id, images, \'dep\' as type from deposits WHERE debt=0
                                                  UNION
                                                  SELECT id, name, parent_id, \'\' as images, \'cat\' as type from groups WHERE debt=0'
        )->queryAll();

        $dep=ArrayHelper::index($dep, "id");

        $debt=0;

        return $this->render('deposit', compact('groups', 'deposit', 'dep', 'debt', 'arr_count'));
    }


    public function actionDepositDelete($id)
    {
        $id=str_replace("dep_", "", $id);

        $count = Operations::find()->where(['deposit_id' => $id, 'user_id' => Yii::$app->user->id])->count();
        $count_exchange = Exchange::find()->where(['or','deposit_to='.$id,'deposit_from='.$id])->count();

        if ($count==0 and $count_exchange==0) {
            $model = Deposits::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id, 'debt' => 0])->one();
            if (empty($model)) {
                Yii::$app->session->setFlash('error', 'Ошибка при удалении счета #301');
                return $this->redirect(['accounting/deposit']);
            } else {
                if ($model->delete()) {
                    Yii::$app->session->setFlash('success', 'Счет успешно удален');
                    return $this->redirect(['accounting/deposit']);
                } else {
                    Yii::$app->session->setFlash('error', 'Error!');
                }
            }
        } else {
            Yii::$app->session->setFlash('error', 'К этому счету относится '.$count.' операций и '.$count_exchange.' операций обмена, которые надо либо удалить, либо перенести в другой счет. Так же счет можно просто скрыть.');
            return $this->redirect(['accounting/deposit']);
        }
    }

    public function actionDebt($id = false)
    {
        $groups = new Groups();

        if ($groups->load(Yii::$app->request->post())) {
            //Здесь надо написать сравнение, чтобы user_id у залогиненного совпадал с пришедшим из формы
            if ($groups->save()) {
                Yii::$app->session->setFlash('success', 'Группа успешна создана');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }

        $deposit = new Deposits();
        if ($deposit->load(Yii::$app->request->post())) {
            //Здесь надо написать сравнение, чтобы user_id у залогиненного совпадал с пришедшим из формы
            if ($deposit->save()) {
                Yii::$app->session->setFlash('success', 'Источник успешно создан');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }


        $dep = Yii::$app->db->createCommand(
            'SELECT  CONCAT(\'dep_\',id) as id, `name`, `group_id` as parent_id, images, \'dep\' as type from deposits WHERE debt=1
                                                  UNION
                                                  SELECT id, name, parent_id, \'\' as images, \'cat\' as type from groups WHERE debt=1'
        )->queryAll();

        $dep=ArrayHelper::index($dep, "id");

        $debt=1;

        return $this->render('debt', compact('groups', 'deposit', 'dep', 'debt'));
    }

    public function actionDebtUpdate($id)
    {
        $groups = new Groups();

        if ($groups->load(Yii::$app->request->post())) {
            //Здесь надо написать сравнение, чтобы user_id у залогиненного совпадал с пришедшим из формы
            if ($groups->save()) {
                Yii::$app->session->setFlash('success', 'Группа успешна создана');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }

        $id=str_replace("dep_", "", $id);

        $deposit = Deposits::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one();

        $count = Operations::find()->where(['deposit_id' => $id, 'user_id' => Yii::$app->user->id])->count();
        $count_exchange = Exchange::find()->where(['or','deposit_to='.$id,'deposit_from='.$id])->count();
        $arr_count=['count_operations' => $count, 'count_exchange' => $count_exchange];

        if (empty($deposit)) {
            Yii::$app->session->setFlash('error', 'Ошибка при обновлении долгов #301');
            return $this->redirect(['accounting/debt']);
        } else {
            if ($deposit->load(Yii::$app->request->post())) {
                //Здесь надо написать сравнение, чтобы user_id у залогиненного совпадал с пришедшим из формы
                if ($deposit->save()) {
                    Yii::$app->session->setFlash('success', 'Долг успешно обновлен');
                    return $this->refresh();
                } else {
                    Yii::$app->session->setFlash('error', 'Error!');
                }
            }
        }


        if (Yii::$app->request->post()!=null) {
            if (Yii::$app->request->post()['Transfer']['deposit_from']==Yii::$app->request->post()['Transfer']['deposit_to']) {
                Yii::$app->session->setFlash('error', 'Нельзя переносить из одного счета в тот же самый');
            } else {
                $sql=array();
                $deposit_from=str_replace("dep_", "", Yii::$app->request->post()['Transfer']['deposit_from']);
                $deposit_to=str_replace("dep_", "", Yii::$app->request->post()['Transfer']['deposit_to']);
                $sql[]="UPDATE operations SET deposit_id=".$deposit_to." WHERE deposit_id=".$deposit_from;
                $sql[]="UPDATE exchange SET deposit_from=".$deposit_to." WHERE deposit_from=".$deposit_from;
                $sql[]="UPDATE exchange SET deposit_to=".$deposit_to." WHERE deposit_to=".$deposit_from;

                $transaction =  Yii::$app->db->beginTransaction();
                try {
                    foreach ($sql as $s) {
                        Yii::$app->db->createCommand($s)->execute();
                    }
                    $transaction->commit();
                } catch (\Exception $e) {
                    $transaction->rollBack();
                    throw $e;
                } catch (\Throwable $e) {
                    $transaction->rollBack();
                    throw $e;
                }
                Yii::$app->session->setFlash('success', 'Успешно переведено');
            }

        }

        $dep = Yii::$app->db->createCommand(
            'SELECT  CONCAT(\'dep_\',id) as id, `name`, `group_id` as parent_id, images, \'dep\' as type from deposits WHERE debt=1
                                                  UNION
                                                  SELECT id, name, parent_id, \'\' as images, \'cat\' as type from groups WHERE debt=1'
        )->queryAll();

        $dep=ArrayHelper::index($dep, "id");

        $debt=1;

        return $this->render('debt', compact('groups', 'deposit', 'dep', 'debt', 'arr_count'));
    }

    public function actionDebtDelete($id)
    {
        $id=str_replace("dep_", "", $id);

        $count = Operations::find()->where(['deposit_id' => $id, 'user_id' => Yii::$app->user->id])->count();
        $count_exchange = Exchange::find()->where(['or','deposit_to='.$id,'deposit_from='.$id])->count();

        if ($count==0 and $count_exchange==0) {
            $model = Deposits::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id, 'debt' => 1])->one();
            if (empty($model)) {
                Yii::$app->session->setFlash('error', 'Ошибка при удалении долга\займа #301');
                return $this->redirect(['accounting/debt']);
            } else {
                if ($model->delete()) {
                    Yii::$app->session->setFlash('success', 'Долг\займ успешно удален');
                    return $this->redirect(['accounting/debt']);
                } else {
                    Yii::$app->session->setFlash('error', 'Error!');
                }
            }
        } else {
            Yii::$app->session->setFlash('error', 'К этому долгу относится '.$count.' операций и '.$count_exchange.' операций обмена, которые надо либо удалить, либо перенести в другое место. Также долг можно просто скрыть.');
            return $this->redirect(['accounting/debt']);
        }
    }

    public function actionPlanning()
    {
        return $this->render('planning');
    }

}
