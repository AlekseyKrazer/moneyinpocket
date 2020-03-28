<?php

use app\components\MenuWidget;
use bs\Flatpickr\FlatpickrWidget as Flatpickr;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

$this->title = 'Ввод операций - Moneyinpocket';
?>
<script> var exports = {};

    function show_main_category(id) {
        var element = document.getElementById(id);


        if (element!= null) {
            if (element.style.display != 'none') {
                element.style.display = "none";
            } else {
                element.style.display = "block";
            }
            recalc();
        }
    }

    function recalc() {
        var final_total = document.getElementById("final-total");


        var total_debt = document.getElementById("total-debt-show");
        var total_debt_sum = 0;


        var total_debt_data = total_debt.querySelectorAll('[id^="total-debt dep_"]');
        for (var i = 0; i < total_debt_data.length; i++) {
            total_debt_sum = total_debt_sum + parseFloat(total_debt_data[i].textContent);
        }
        var todel_debt = document.getElementById('total-debt-temp-total');

        if (total_debt.style.display == "none") {
            if (todel_debt == null) {
                total_debt.insertAdjacentHTML("beforebegin", "<span id='total-debt-temp-total' style='display: inline'>(" + formatCurrency(total_debt_sum) + ")</span>");
            }
            total_debt_sum = 0;
        } else {
            if (todel_debt != null) {
                todel_debt.remove();
            }
        }


        var total_owe = document.getElementById("total-owe-show");
        var total_owe_sum = 0;
        var total_owe_data = total_owe.querySelectorAll('[id^="total-owe dep_"]');

        for (var i = 0; i < total_owe_data.length; i++) {
            total_owe_sum = total_owe_sum + parseFloat(total_owe_data[i].textContent);
        }

        var todel_owe = document.getElementById('total-owe-temp-total');
        if (total_owe.style.display == "none") {
            if (todel_owe == null) {
                total_owe.insertAdjacentHTML("beforebegin", "<span id='total-owe-temp-total'>(" + formatCurrency(total_owe_sum) + ")</span>");
            }
            total_owe_sum = 0;
        } else {
            if (todel_owe != null) {
                todel_owe.remove();
            }
        }

        var total_deposit_sum = 0;
        var total_deposit_all_sum = 0;
        var total_deposit_hide_cat_sum = 0;
        var total_deposit = document.getElementById("total-deposit-show");

        var all_deposit = total_deposit.querySelectorAll('[id^="total-deposit dep_"]');

        for (var i = 0; i < all_deposit.length; i++) {
            total_deposit_all_sum = total_deposit_all_sum + parseFloat(all_deposit[i].textContent);
        }

        var total_deposit_categories = total_deposit.querySelectorAll('[id^="category_"]');
        for (var num = 0; num < total_deposit_categories.length; num++) {
            var category_deposit = total_deposit_categories[num].querySelectorAll('[id^="total-deposit dep_"]');
            if (category_deposit.length>0) {
                var category_deposit_sum = 0;
                for (var i = 0; i < category_deposit.length; i++) {
                    if (total_deposit_categories[num].style.display == "none") {
                        total_deposit_hide_cat_sum = total_deposit_hide_cat_sum + parseFloat(category_deposit[i].textContent);
                    }
                    category_deposit_sum = category_deposit_sum + parseFloat(category_deposit[i].textContent);
                }
                var todel_deposit = document.getElementById("total_sum_" + total_deposit_categories[num].id);
                if (total_deposit_categories[num].style.display == "none") {
                    if (todel_deposit == null) {
                        total_deposit_categories[num].insertAdjacentHTML("beforebegin", "<span id='total_sum_" + total_deposit_categories[num].id + "'>(" + formatCurrency(category_deposit_sum) + ")</span>");
                    }
                } else {
                    if (todel_deposit != null) {
                        todel_deposit.remove();
                    }
                }
            }
        }
        total_deposit_sum = parseFloat(Number(total_deposit_all_sum-total_deposit_hide_cat_sum).toFixed(2));

        var final_sum = total_debt_sum + total_owe_sum + total_deposit_sum;

        if (final_sum>0) {
            final_sum = "<span style=\"color:green\">" + formatCurrency(final_sum) + "</span>";
        } else {
            final_sum = "<span style=\"color:red\">" + formatCurrency(final_sum) + "</span>";
        }

        final_total.innerHTML=final_sum;
    }
</script>

<div class="col-md-4">
    <?php

    //Выбираем класс активной кнопки
    $class1='';
    $class2='';
    $class3='';
    switch ($type) {
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
            'onchange' => '$.post( "' . Yii::$app->urlManager->createUrl(["accounting/history"]) . '&type='.$type.'&date="+$(this).val(), function(data){
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
    <?= Html::a("Сейчас", ['accounting/now', 'type' => $type]); ?>
    <?= $form->field($operations, "type")->hiddenInput(['value' =>  $type])->label(false) ?>
    <?php if (isset($_GET['id']) and $_GET['id']!='') {
        echo $form->field($operations, "amount")->input("text", ['placeholder' => 'Введите сумму', 'value' => abs($operations->amount)])->label(false);
    } else {
        echo $form->field($operations, "amount")->input("text", ['placeholder' => 'Введите сумму'])->label(false);
    }
    ?>
    <?= $form->field($operations, "user_id")->hiddenInput(['value' => 1])->label(false) ?>
    <label class="control-label" for="operations-deposit_id">Места хранения</label>
    <select id="operations-deposit_id" class="form-control" name="<?= $operations->formName() ?>[deposit_id]" aria-required="true">
        <?= MenuWidget::widget(['tpl' => 'select_comb', 'source' => 'deposit', 'model' => $operations]); ?>
        <optgroup label="Долговые места">
            <?= MenuWidget::widget(['tpl' => 'select_comb', 'source' => 'debt', 'model' => $operations]); ?>
        </optgroup>
    </select><BR>
    <?php if ($type == 1): ?>
    <label class="control-label" for="operations-category_id"><span class="text-danger">На что тратим</span></label>
    <select id="operations-category_id" class="form-control" name="<?= $operations->formName() ?>[category_id]" aria-required="true">
        <?= MenuWidget::widget(['tpl' => 'select', 'source' => 'category', 'model' => $operations]) ?>
    </select><BR>
    <?endif; ?>
    <?php if ($type == 2): ?>
        <label class="control-label" for="operations-category_id"><span class="text-success">Как заработали?</span></label>
        <select id="operations-category_id" class="form-control" name="<?= $operations->formName() ?>[category_id]" aria-required="true">
            <?= MenuWidget::widget(['tpl' => 'select', 'source' => 'income', 'model' => $operations]) ?>
        </select><BR>
    <?endif; ?>
    <?php if ($type == 3): ?>
        <label class="control-label" for="operations-deposit_id">Куда переносим?</label>
        <select id="operations-deposit_id2" class="form-control" name="<?= $operations->formName() ?>[deposit_id2]" aria-required="true">
            <?= MenuWidget::widget(['tpl' => 'select_comb_exchange', 'source' => 'deposit', 'model' => $operations]); ?>
            <optgroup label="Долговые места">
                <?= MenuWidget::widget(['tpl' => 'select_comb_exchange', 'source' => 'debt', 'model' => $operations]); ?>
            </optgroup>
        </select><BR>
    <?endif; ?>
    <?= $form->field($operations, "comment") ?>
    <?php if ($type==1): ?>
    <?= Html::submitButton('Зафиксировать трату') ?>
    <?php endif; ?>
    <?php if ($type==2): ?>
        <?= Html::submitButton('Зафиксировать доход') ?>
    <?php endif; ?>
    <?php if ($type==3): ?>
        <?= Html::submitButton('Перенести') ?>
    <?php endif; ?>
    <?php if (isset($_GET['id']) and $_GET['id']!=''): ?>
    <?= Html::a('Удалить<span class="glyphicon glyphicon-remove"></span>', ['delete', 'id' => $_GET['id'], 'type' => $type], [
        'data' => [
            'confirm' => 'Вы действительно хотите удалить?',
            'method' => 'post',
        ],
    ]) ?>
    <?php endif; ?>
    <?php ActiveForm::end(); ?>


<div id="test_div">
    <?php
    if ($type == 3) {
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
        if ($type==1) {
            $class="text-danger";
        } else {
            $class="text-success";
        }
        foreach ($operations_data as $key => $value) {
            $sum_amount=$sum_amount+$value['amount'];
            echo Html::img('@web/images/' . $value['images'], ['width' => 16]) . "    ";
            echo $value['category_name'] . "   ";
            echo "<font size=4>" . Html::a(Yii::$app->formatter->asCurrency($value['amount']), ['accounting/index', 'type' => $type, 'id' => $value['id']], ['class' => $class ]) . "</font>   ";
            echo Yii::$app->formatter->asDate(strtotime($value['datetime']), 'php:d M H:i') . "<BR>";
            echo $value['comment'] . "<BR><BR>";
        }
        if ($sum_amount<>0) {
            echo "Итого: <font size=4 class=" . $class . ">" . Yii::$app->formatter->asCurrency($sum_amount) . "</font>";
        }
    }
    ?>

</div>
    <BR><BR>
</div>

<div class="col-md-4">
    <div id="total-deposit-show">
        <?=MenuWidget::widget(['tpl' => 'menu_total', 'source' => 'total-deposit']) ?>
    </div>
    <BR>
    <a onclick="show_main_category('total-owe-show')" style="cursor: pointer; color: black; font-size: 18px; font-weight: 500;">Мне должны:</a>
    <div id="total-owe-show" style="display:none;">
        <?= MenuWidget::widget(['tpl' => 'menu_total', 'source' => 'total-owe']) ?>
    </div>
    <BR>
    <a onclick="show_main_category('total-debt-show')" style="cursor: pointer; color: black; font-size: 18px; font-weight: 500;">Я должен:</a>
    <div id="total-debt-show" style="display:none;">
        <?= MenuWidget::widget(['tpl' => 'menu_total', 'source' => 'total-debt']) ?>
    </div>
<BR><BR>
    ИТОГО: <span id="final-total"></span>
</div>

<script>

function formatCurrency(sum) {

    var format_sum = new Intl.NumberFormat("ru-RU", { style: 'currency', currency: 'RUB' }).format(sum);
    return format_sum;
}
recalc();
</script>