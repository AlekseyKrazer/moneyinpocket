<?php

use app\components\MenuWidget;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Moneyinpocket';
?>
<div class="col-md-4">

    <h1><?= $lang['title'] ?></h1>
    <?php


    echo MenuWidget::widget(['tpl' => 'menu_comb', 'source' => $lang['source']]);


    ?>
</div>
<div class="col-md-4">
    <h3>Редактирование группы</h3>
    <?php $form = ActiveForm::begin(['options' => ['style' => 'width: 100%;', 'autocomplete' => 'off']]); ?>
    <?= $form->field($groups, 'name') ?>
    <label class="control-label" for="categories-parent_id">Родительская категория</label>
    <select id="income-parent_id" class="form-control" name="<?= $groups->formName() ?>[parent_id]"
            aria-invalid="false">
        <option value="0">-=пусто=-</option>
        <?= MenuWidget::widget(['tpl' => 'select', 'model' => $groups, 'source' => 'groups']) ?>
    </select>
    <?= $form->field($groups, "user_id")->hiddenInput(['value' => 1])->label(false) ?>
    <?= $form->field($groups, "debt")->hiddenInput(['value' => $debt])->label(false) ?>
    <?= $form->field($groups, 'position') ?>
    <?= $form->field($groups, 'hide')->checkbox() ?>
    <?= $form->field($groups, 'collapse')->checkbox() ?>
    <?= Html::submitButton('Редактировать группу') ?>
    <?php ActiveForm::end() ?>
    <br>
</div>