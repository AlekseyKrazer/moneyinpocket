<?php

namespace app\models;

/**
 * This is the model class for table "groups".
 *
 * @property int $id
 * @property string $name
 * @property int $parent_id
 * @property int $user_id
 * @property int $position
 * @property int $debt
 */
class Groups extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'groups';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent_id', 'user_id', 'position', 'debt', 'hide','collapse'], 'integer'],
            [['name'], 'string', 'max' => 100],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Название группы',
            'parent_id' => 'Parent ID',
            'user_id' => 'User ID',
            'position' => 'Position',
            'debt' => 'Debt',
            'hide' => 'Не показывать в общем списке',
            'collapse' => 'Показывать в свернутом виде',
        ];
    }
}
