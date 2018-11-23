<?php

use app\components\MenuWidget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<div class="col-md-4">
    <h1><?= $lang['title'] ?></h1>
    <ul>
        <?= MenuWidget::widget(['tpl' => 'menu_categories', 'source' => $lang['source']]) ?>
    </ul>
</div>
<div class="col-md-4">
    Создание категории
    <?php $form = ActiveForm::begin(); ?>
    <?= $form->field($model, 'name') ?>
    <label class="control-label" for="categories-parent_id">Родительская категория</label>
    <select id="categories-parent_id" class="form-control" name="<?= $model->formName() ?>[parent_id]"
            aria-invalid="false">
        <option value="0">-=пусто=-</option>
        <?= MenuWidget::widget(['tpl' => 'select', 'model' => $model]) ?>
    </select>
    <?= $form->field($model, "user_id")->hiddenInput(['value' => 1])->label(false) ?>
    <?= $form->field($model, "type")->hiddenInput(['value' => $type])->label(false) ?>
    <?= $form->field($model, 'position') ?>
    <?= Html::submitButton($lang['button']) ?>
    <?php ActiveForm::end() ?>
</div>