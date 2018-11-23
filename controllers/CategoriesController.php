<?php

namespace app\controllers;

use app\models\Categories;
use app\models\Operations;
use Yii;

class CategoriesController extends \yii\web\Controller
{

    public function actionIndex($type = 1)
    {
        $model = new Categories();

        $model->checkType($type);

        $lang = $model->getParams($type);

        if ($model->load(Yii::$app->request->post())) {
            //Здесь надо написать сравнение, чтобы user_id у залогиненного совпадал с пришедшим из формы
            if ($model->save()) {
                Yii::$app->session->setFlash('success', $lang['name'] . ' успешно создана');
                return $this->refresh();
            } else {
                Yii::$app->session->setFlash('error', 'Error!');
            }
        }
        return $this->render('category', compact('model', 'lang', 'type'));
    }

    public function actionUpdate($id, $type)
    {
        $model = new Categories();

        $model->checkType($type);

        $lang = $model->getParams($type);

        $model = Categories::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one();
        if (empty($model)) {
            Yii::$app->session->setFlash('error', 'Ошибка при обновлении #301');
            return $this->redirect(['categories/index', 'type' => $type]);
        } else {
            if ($model->load(Yii::$app->request->post())) {
                if ($model->save()) {
                    Yii::$app->session->setFlash('success', 'Категория успешно обновлена');
                    return $this->redirect(['categories/index', 'type' => $type]);
                } else {
                    Yii::$app->session->setFlash('error', 'Error!');
                }
            }
            return $this->render('category', compact('model', 'lang', 'type'));
        }
    }

    public function actionDelete($id, $type)
    {
        $count = Operations::find()->where(['category_id' => $id, 'user_id' => Yii::$app->user->id])->count();

        if ($count == 0) {
            $model = Categories::find()->where(['id' => $id, 'user_id' => Yii::$app->user->id])->one();
            if (empty($model)) {
                Yii::$app->session->setFlash('error', 'Ошибка при удалении #301');
            } else {
                if ($model->delete()) {
                    Yii::$app->session->setFlash('success', 'Категория успешно удалена');
                } else {
                    Yii::$app->session->setFlash('error', 'Error!');
                }
            }
        } else {
            Yii::$app->session->setFlash('error', 'В этой категории хранится ' . $count . ' операций, которые надо либо удалить, либо перенести в другую категорию. Так же категорию можно скрыть.');
        }
        return $this->redirect(['categories/index', 'type' => $type]);
    }


    public function actionIndex2()
    {
        return $this->render('index');
    }

}
