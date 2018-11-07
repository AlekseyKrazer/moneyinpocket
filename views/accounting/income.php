<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use app\components\MenuWidget;

?>
<div class="col-md-4">
    <h1>Доходы</h1>
    <ul>
        <?= MenuWidget::widget(['tpl' => 'menu', 'source' => 'income']) ?>
    </ul>
</div>
<div class="col-md-4">
    Создание дохода
    <?php $form = ActiveForm::begin(['options' => ['style' => 'width: 100%;', 'autocomplete' => 'off']]); ?>
    <?= $form->field($model, 'name') ?>
    <label class="control-label" for="categories-parent_id">Родительская категория</label>
    <select id="categories-parent_id" class="form-control" name="<?= $model->formName() ?>[parent_id]"
            aria-invalid="false">
        <option value="0">-=пусто=-</option>
        <?= MenuWidget::widget(['tpl' => 'select', 'model' => $model, 'source' => 'income']) ?>
    </select>
    <?= $form->field($model, "user_id")->hiddenInput(['value' => 1])->label(false) ?>
    <?= $form->field($model, "type")->hiddenInput(['value' => 2])->label(false) ?>
    <?= $form->field($model, 'position') ?>
    <?= Html::submitButton('Создать доход') ?>
    <?php ActiveForm::end() ?>
</div>