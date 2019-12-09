<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "deposits".
 *
 * @property int $id
 * @property string $name
 * @property string $images
 * @property int $group_id
 * @property int $user_id
 * @property int $position
 * @property int $debt
 */
class Deposits extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'deposits';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'group_id', 'user_id'], 'required'],
            [['start_sum'], 'number'],
            [['hide'], 'number'],
            [['group_id', 'user_id', 'position', 'debt'], 'integer'],
            [['name'], 'string', 'max' => 150],
            [['images'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название счета',
            'images' => 'Выберите иконку:',
            'group_id' => 'Group ID',
            'user_id' => 'User ID',
            'position' => 'Position',
            'start_sum' => 'Начальный капитал',
            'debt' => 'Debt',
            'hide' => 'Не показывать в общем списке',
        ];
    }

    public static function getAllDepositWithoutDebt()
    {
        $dep = Yii::$app->db->createCommand(
            'SELECT  CONCAT(\'dep_\',id) as id, `name`, `group_id` as parent_id, images, \'dep\' as type, hide from deposits WHERE debt=0
                                                  UNION
                                                  SELECT id, name, parent_id, \'\' as images, \'cat\' as type, hide from groups WHERE debt=0'
        )->queryAll();
        return $dep;
    }
    public static function getAllDepositWithDebt()
    {
        $debt = Yii::$app->db->createCommand(
            'SELECT  CONCAT(\'dep_\',id) as id, `name`, `group_id` as parent_id, images, \'dep\' as type, hide from deposits WHERE debt=1
                                                  UNION
                                                  SELECT id, name, parent_id, \'\' as images, \'cat\' as type, hide from groups WHERE debt=1'
        )->queryAll();
        return $debt;
    }

    public static function depositTransfer($post_data)
    {
        if ($post_data['deposit_from']==$post_data['deposit_to']) {
            Yii::$app->session->setFlash('error', 'Нельзя переносить из одного счета в тот же самый');
        } else {
            $sql=array();
            $deposit_from=str_replace("dep_", "", $post_data['deposit_from']);
            $deposit_to=str_replace("dep_", "", $post_data['deposit_to']);
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
