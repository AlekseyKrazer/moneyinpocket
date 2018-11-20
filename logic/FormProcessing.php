<?php
namespace app\logic;

use Yii;

class FormProcessing
{

    public $post_data;
    public $form_name;

    public function __construct($form_name, $post_data)
    {
        $this->form_name = $form_name;
        $this->post_data = $post_data;
    }

    public function checkOperationForm()
    {
        $add_array=array();
        $add_array['_csrf'] = $this->post_data['_csrf'];
        $add_array[$this->form_name]['datetime'] = $this->post_data[$this->form_name]['datetime'];
        $add_array[$this->form_name]['date'] = date("Y-m-d", strtotime($this->post_data[$this->form_name]['datetime']));
        $date = $add_array[$this->form_name]['date'];
        $add_array[$this->form_name]['type'] = $this->post_data[$this->form_name]['type'];
        $add_array[$this->form_name]['amount'] = $this->post_data[$this->form_name]['amount'];

        //Поле amount может выполнять математические действия. Удаляем все лишнее и выполняем действие.
        $add_array[$this->form_name]['amount'] = str_replace(',', ".", $add_array[$this->form_name]['amount']);
        $equation = preg_replace('[^0-9\+-\*\/\(\) ]', '', $add_array[$this->form_name]['amount']);
        if (!empty($equation)) {
            eval('$total = (' . $equation . ');');
            $add_array[$this->form_name]['amount'] = $total;
        }
        if ($add_array[$this->form_name]['type'] == 1) {
            $add_array[$this->form_name]['amount'] = -1 * $add_array[$this->form_name]['amount'];
        }


        $add_array[$this->form_name]['user_id'] = Yii::$app->user->id;

        //При обмене у нас две переменных deposit_from - откуда взяли деньги и deposit_to - куда положили. Категории нет.
        if ($add_array[$this->form_name]['type'] == 3) {
            $add_array[$this->form_name]['deposit_from'] = str_replace("dep_", "", $this->post_data[$this->form_name]['deposit_id']);
            $add_array[$this->form_name]['deposit_to'] = str_replace("dep_", "", $this->post_data[$this->form_name]['deposit_id2']);
        } else {
            //При расходе и доходе есть категория дохода\расхода и депозит с которым провели операцию.
            $add_array[$this->form_name]['category_id'] = $this->post_data[$this->form_name]['category_id'];
            $add_array[$this->form_name]['deposit_id'] = str_replace("dep_", "", $this->post_data[$this->form_name]['deposit_id']);
        }

        $add_array[$this->form_name]['comment'] = $this->post_data[$this->form_name]['comment'];

        if (!isset($total) or $total <= 0) {
            Yii::$app->session->setFlash('error', 'Сумма не может быть ноль и меньше');
            unset($add_array);
            $add_array = array();
        }
        return $add_array;
    }

    public function depositTransfer()
    {
        if ($this->post_data[$this->form_name]['deposit_from']==$this->post_data[$this->form_name]['deposit_to']) {
            Yii::$app->session->setFlash('error', 'Нельзя переносить из одного счета в тот же самый');
        } else {
            $sql=array();
            $deposit_from=str_replace("dep_", "", $this->post_data[$this->form_name]['deposit_from']);
            $deposit_to=str_replace("dep_", "", $this->post_data[$this->form_name]['deposit_to']);
            $sql[]="UPDATE operations SET deposit_id=".$deposit_to." WHERE deposit_id=".$deposit_from;
            $sql[]="UPDATE exchange SET deposit_from=".$deposit_to." WHERE deposit_from=".$deposit_from;
            $sql[]="UPDATE exchange SET deposit_to=".$deposit_to." WHERE deposit_to=".$deposit_from;

            /* Надо будет добавить обработку, чтобы на этот депозит переносилась стартовая сумма.(???)
             * и чтобы не было ситуации, когда переносится из одного депозита в тот же самый.
             */

            $transaction =  Yii::$app->db->beginTransaction();
            try {
                foreach ($sql as $s) {
                    Yii::$app->db->createCommand($s)->execute();
                }
                $transaction->commit();
            } catch (\Throwable $e) {
                $transaction->rollBack();
                throw $e;
            }
            Yii::$app->session->setFlash('success', 'Успешно переведено');
        }
    }


}