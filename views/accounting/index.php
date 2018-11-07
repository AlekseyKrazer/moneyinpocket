<?php

use yii\widgets\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;
use bs\Flatpickr\FlatpickrWidget as Flatpickr;
use kartik\sortable\Sortable;
use kartik\sortinput\SortableInput;
use app\components\MenuWidget;
use app\components\TotalWidget;
use yii\web\Cookie;
?>
<script> var exports = {}; </script>

<div class="col-md-4">
    <?php
    $class1='';
    $class2='';
    $class3='';
    switch ($_GET['type']) {
        case 1:
            $class1 = 'class="btn btn-primary"';
            break;
        case 2:
            $class2 = 'class="btn btn-primary"';
            break;
        case 3:
            $class3 = 'class="btn btn-primary"';
            break;
    }
    ?>

    <?= Html::a('<span '.$class1.'>Расходы</span>', ['accounting/index', 'type' => 1]); ?>
    <?= Html::a('<span '.$class2.'>Доходы</span>', ['accounting/index', 'type' => 2]); ?>
    <?= Html::a('<span '.$class3.'>Переводы</span>', ['accounting/index', 'type' => 3]); ?>
    <?php $form = ActiveForm::begin(['options' => ['style' => 'width: 100%;', 'autocomplete' => 'off']]); ?>
    <?= $form->field($operations, 'datetime')->widget(Flatpickr::className(), [
        'locale' => 'ru',
        'options' => [
            'class' => 'form-control',
//            'onchange' => '$.post( "' . Yii::$app->urlManager->createUrl(["accounting/test"]) . '&date="+$(this).val(), function(data){
//             $("#test_div").html( data );
//             })'
            'onchange' => '$.post( "' . Yii::$app->urlManager->createUrl(["accounting/test"]) . '&type='.$_GET['type'].'&date="+$(this).val(), function(data){
             $("#test_div").html( data );
             })'
        ],

        'clientOptions' => [
            // config options https://chmln.github.io/flatpickr/options/
            'allowInput' => true,
            'defaultDate' => $datetime,
            'enableTime' => true,
            'time_24hr' => true,
            'altInput' => true,
            'altFormat' => "j F Y H:i",
            'dateFormat' => "Y-m-d H:i",
            'weekNumbers' => true,
            'allowInput' => false,

        ],
    ]) ?>
    <?= Html::a("Сейчас", ['accounting/index', 'type' => 4]); ?>
    <?= $form->field($operations, "type")->hiddenInput(['value' =>  $_GET['type']])->label(false) ?>
    <?php if (isset($_GET['id']) and $_GET['id']!='') {
    echo $form->field($operations, "amount")->input("text", ['placeholder' => 'Введите сумму', 'value' => abs($operations->amount)])->label(false);
    } else {
       echo $form->field($operations, "amount")->input("text", ['placeholder' => 'Введите сумму'])->label(false);
    }
    ?>
    <?= $form->field($operations, "user_id")->hiddenInput(['value' => 1])->label(false) ?>
    <label class="control-label" for="operations-deposit_id">Места хранения</label>
    <select id="operations-deposit_id" class="form-control" name="<?= $operations->formName() ?>[deposit_id]" aria-required="true">
        <?= MenuWidget::widget(['tpl' => 'select_comb','source' => 'deposit', 'data'=> $dep, 'model' => $operations]); ?>
        <optgroup label="Долговые места">
            <?= MenuWidget::widget(['tpl' => 'select_comb','source' => 'deposit', 'data'=> $debt, 'model' => $operations]); ?>
        </optgroup>
    </select><BR>
    <?php if ($_GET['type']==1): ?>
    <label class="control-label" for="operations-category_id"><span class="text-danger">На что тратим</span></label>
    <select id="operations-category_id" class="form-control" name="<?= $operations->formName() ?>[category_id]" aria-required="true">
        <?= MenuWidget::widget(['tpl' => 'select', 'source' => 'category', 'model' => $operations]) ?>
    </select><BR>
    <?endif; ?>
    <?php if ($_GET['type']==2): ?>
        <label class="control-label" for="operations-category_id"><span class="text-success">Как заработали?</span></label>
        <select id="operations-category_id" class="form-control" name="<?= $operations->formName() ?>[category_id]" aria-required="true">
            <?= MenuWidget::widget(['tpl' => 'select', 'source' => 'income', 'model' => $operations]) ?>
        </select><BR>
    <?endif; ?>
    <?php if ($_GET['type']==3): ?>
        <label class="control-label" for="operations-deposit_id">Куда переносим?</label>
        <select id="operations-deposit_id" class="form-control" name="<?= $operations->formName() ?>[deposit_id2]" aria-required="true">
            <?= MenuWidget::widget(['tpl' => 'select_comb_exchange','source' => 'deposit_exchange', 'data'=> $dep, 'model' => $operations]); ?>
            <optgroup label="Долговые места">
            <?= MenuWidget::widget(['tpl' => 'select_comb_exchange','source' => 'deposit_exchange', 'data'=> $debt, 'model' => $operations]); ?>
            </optgroup>
        </select><BR>
    <?endif; ?>
    <?= $form->field($operations, "comment") ?>
    <?php if ($_GET['type']==1): ?>
    <?= Html::submitButton('Зафиксировать трату') ?>
    <?php endif; ?>
    <?php if ($_GET['type']==2): ?>
        <?= Html::submitButton('Зафиксировать доход') ?>
    <?php endif; ?>
    <?php if ($_GET['type']==3): ?>
        <?= Html::submitButton('Перенести') ?>
    <?php endif; ?>
    <?php if (isset($_GET['id']) and $_GET['id']!=''): ?>
    <?= Html::a('Удалить<span class="glyphicon glyphicon-remove"></span>', ['delete', 'id' => $_GET['id'], 'type' => $_GET['type']], [
        'data' => [
            'confirm' => 'Вы действительно хотите удалить?',
            'method' => 'post',
        ],
    ]) ?>
    <?php endif; ?>
    <?php ActiveForm::end(); ?>


<div id="test_div">
    <?php
    if ($_GET['type']==3) {
        foreach ($operations_data as $key => $value) {
            echo " Из ";
            echo Html::img('@web/images/' . $value['deposit_from_images'], ['width' => 16]) . "    ";
            echo $value['deposit_from_name'] . "   ";
            echo " в ";
            echo Html::img('@web/images/' . $value['deposit_to_images'], ['width' => 16]) . "    ";
            echo $value['deposit_to_name'] . "<BR>";
            echo "<font size=4>" . Html::a(Yii::$app->formatter->asCurrency($value['amount']), ['accounting/index', 'type' => 3, 'id' => $value['id']]) . "</font>   ";
            echo Yii::$app->formatter->asDate(strtotime($value['datetime']), 'php:d M H:i') . "<BR>";
            echo $value['comment'] . "<BR><BR>";
        }

    } else {
        $sum_amount=0;
        if ($_GET['type']==1) {
            $class="text-danger";
        } else {
            $class="text-success";
        }
        foreach ($operations_data as $key => $value) {
            $sum_amount=$sum_amount+$value['amount'];
            echo Html::img('@web/images/' . $value['images'], ['width' => 16]) . "    ";
            echo $value['category_name'] . "   ";
            echo "<font size=4>" . Html::a(Yii::$app->formatter->asCurrency($value['amount']), ['accounting/index', 'type' => $_GET['type'], 'id' => $value['id']],['class' => $class ]) . "</font>   ";
            echo Yii::$app->formatter->asDate(strtotime($value['datetime']), 'php:d M H:i') . "<BR>";
            echo $value['comment'] . "<BR><BR>";
        }
        if ($sum_amount<>0) {
            echo "Итого: <font size=4 class=" . $class . ">" . Yii::$app->formatter->asCurrency($sum_amount) . "</font>";
        }
    }
    ?>

</div>

</div>

<div class="col-md-4">
    <?=MenuWidget::widget(['tpl' => 'menu_total', 'source' => 'total-deposit']) ?>
    <h4>Мне должны:</h4>
    <?= MenuWidget::widget(['tpl' => 'menu_total', 'source' => 'total-owe']) ?>
    <h4>Я должен</h4>
    <?= MenuWidget::widget(['tpl' => 'menu_total', 'source' => 'total-debt']) ?>

</div>