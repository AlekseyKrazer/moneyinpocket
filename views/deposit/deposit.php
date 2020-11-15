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
    <h3><?= $lang['title_right'] ?></h3>
    <?php $form = ActiveForm::begin(['options' => ['style' => 'width: 100%;', 'autocomplete' => 'off']]); ?>
    <?= $form->field($deposit, 'name') ?>
    <label class="control-label" for="categories-parent_id">Нужна ли группа?</label>
    <select id="income-parent_id" class="form-control" name="<?= $deposit->formName() ?>[group_id]"
            aria-invalid="false">
        <option value="0">-=пусто=-</option>
        <?= MenuWidget::widget(['tpl' => 'select', 'model' => $deposit, 'source' => 'groups']) ?>
    </select>
    <?= $form->field($deposit, "user_id")->hiddenInput(['value' => 1])->label(false) ?>
    <?= $form->field($deposit, "debt")->hiddenInput(['value' => $debt])->label(false) ?>
    <?= $form->field($deposit, "images")->radioList([
            null => 'Без иконки',
            'alfabank.png' => Html::img('@web/images/alfabank.png', ['width'=>16]),
            'wallet.png' => Html::img('@web/images/wallet.png', ['width'=>16, 'class' => "rounded float-left"]),
            'folder.png' => Html::img('@web/images/folder.png', ['width'=>16]),
            'money-bag.png' => Html::img('@web/images/money-bag.png', ['width'=>16]),
            'visa.png' => Html::img('@web/images/visa.png', ['width'=>16]),
            'pharmacy.png' => Html::img('@web/images/pharmacy.png', ['width'=>16]),
            'blue-credit-card.png' => Html::img('@web/images/blue-credit-card.png', ['width'=>16]),
            'red-credit-card.png' => Html::img('@web/images/red-credit-card.png', ['width'=>16]),
            'green-credit-card.png' => Html::img('@web/images/green-credit-card.png', ['width'=>16]),
            'yellow-credit-card.png' => Html::img('@web/images/yellow-credit-card.png', ['width'=>16]),
            'sberbank.ico' => Html::img('@web/images/sberbank.ico', ['width'=>16]),
            'piggy-bank.png' => Html::img('@web/images/piggy-bank.png', ['width'=>16]),
            'moex.png' => Html::img('@web/images/moex.png', ['width'=>16]),
    ], ['encode' => false]) ?>
    <?= $form->field($deposit, 'start_sum') ?>
    <?= $form->field($deposit, 'position') ?>
    <?= $form->field($deposit, 'hide')->checkbox() ?>
    <?= Html::submitButton($lang['button']) ?>
    <?php ActiveForm::end() ?>
    <br>

    <?php if (in_array(Yii::$app->controller->module->requestedRoute, ['deposit/update'])): ?>
        <h3>Перенос</h3>
        <?php $form = ActiveForm::begin(); ?>
        <label class="control-label" for="operations-deposit_id">Откуда переносим</label>
        <select id="operations-deposit_id" class="form-control" name="Transfer[deposit_from]" aria-required="true">
            <?= MenuWidget::widget(['tpl' => 'select_comb', 'source' => 'deposit', 'model' => $deposit]); ?>
        </select><BR>
        Операций <?= $arr_count['count_operations'] ?> <BR>
        Операций обмена <?= $arr_count['count_exchange'] ?> <BR>
        <label class="control-label" for="operations-deposit_id">Куда переносим?</label>
        <select id="operations-deposit_id" class="form-control" name="Transfer[deposit_to]" aria-required="true">
            <?= MenuWidget::widget(['tpl' => 'select_comb', 'source' => 'deposit']); ?>
        </select><BR>
        <?= Html::submitButton('Перенести') ?>
        <?php ActiveForm::end() ?>
    <?php endif; ?>
    <BR>

    <h3>Создание групп</h3>
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
    <?= Html::submitButton('Создать группу') ?>
    <?php ActiveForm::end() ?>
    <br>
</div>