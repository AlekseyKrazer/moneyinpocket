<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\helpers\Url;

/**
 * This is the model class for table "categories".
 *
 * @property int $id
 * @property string $name
 * @property int $parent_id
 * @property int $user_id
 * @property int $position
 *
 * @property Operations $id0
 */
class Categories extends ActiveRecord
{
    const TYPE_ARRAY = [1, 2];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'categories';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['parent_id', 'user_id', 'position', 'type'], 'integer'],
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
            'name' => 'Название категории',
            'parent_id' => 'Родительская категория',
            'user_id' => 'User ID',
            'position' => 'Позиция в списке (цифрой)',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getId0()
    {
        return $this->hasOne(Operations::className(), ['category_id' => 'id']);
    }

    public function checkType($type)
    {
        if (!in_array($type, self::TYPE_ARRAY)) {
            return Yii::$app->response->redirect(Url::to(["categories/index", "type" => 1]));
        } else {
            return $type;
        }
    }

    public function getParams($type)
    {
        $this->checkType($type);
        $lang_array['type'] = $type;
        if ($type == 1) {
            $lang_array['source'] = 'categories';
            $lang_array['name'] = 'Категория';
            $lang_array['title'] = 'Категории расходов';
            if (Yii::$app->controller->module->requestedRoute == 'categories/update') {
                $lang_array['button'] = 'Редактировать категорию';
            } else {
                $lang_array['button'] = 'Создать категорию';
            }
        } else {
            $lang_array['source'] = 'income';
            $lang_array['name'] = 'Доход';
            $lang_array['title'] = 'Доходы';
            if (Yii::$app->controller->module->requestedRoute == 'categories/update') {
                $lang_array['button'] = 'Редактировать доход';
            } else {
                $lang_array['button'] = 'Создать доход';
            }
        }
        return $lang_array;
    }
}
