<?php
/* @var $this yii\web\View */

use bs\Flatpickr\FlatpickrWidget as Flatpickr;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<script> var exports = {}; </script>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>

<script>
    function showStat() {
        var element_income, index, element_spend, element_total;
        var selector = document.getElementById('stat_type');
        var value = selector[selector.selectedIndex].value;
        document.getElementById("income").style.visibility = "visible";

        var elements_income = document.querySelectorAll('#income');
        var elements_spend = document.querySelectorAll('#spend');
        var elements_total = document.querySelectorAll('#total');

        if (value == 1) {
            for (index = 0; index < elements_income.length; index++) {
                element_income = elements_income[index];
                element_income.style.visibility = "collapse";
            }

            for (index = 0; index < elements_spend.length; index++) {
                element_spend = elements_spend[index];
                element_spend.style.visibility = "visible";
            }
            for (index = 0; index < elements_total.length; index++) {
                element_total = elements_total[index];
                element_total.style.visibility = "hidden";
            }
        }

        if (value == 2) {
            for (index = 0; index < elements_income.length; index++) {
                element_income = elements_income[index];
                element_income.style.visibility = "visible";
            }

            for (index = 0; index < elements_spend.length; index++) {
                element_spend = elements_spend[index];
                element_spend.style.visibility = "collapse";
            }
            for (index = 0; index < elements_total.length; index++) {
                element_total = elements_total[index];
                element_total.style.visibility = "hidden";
            }
        }

        if (value == 3) {
            for (index = 0; index < elements_income.length; index++) {
                element_income = elements_income[index];
                element_income.style.visibility = "visible";
            }

            for (index = 0; index < elements_spend.length; index++) {
                element_spend = elements_spend[index];
                element_spend.style.visibility = "visible";
            }
            for (index = 0; index < elements_total.length; index++) {
                element_total = elements_total[index];
                element_total.style.visibility = "visible";
            }
        }
    }

    function formatDate(date, format = false) {

        var dd = date.getDate();
        if (dd < 10) dd = '0' + dd;

        var mm = date.getMonth() + 1;
        if (mm < 10) mm = '0' + mm;

        var yy = date.getFullYear();
        if (yy < 10) yy = '0' + yy;

        if (format == "input") {
            return yy + '-' + mm + '-' + dd;
        } else {
            return dd + '-' + mm + '-' + yy;
        }
    }

    function startOfWeek(date) {
        var diff = date.getDate() - date.getDay() + (date.getDay() === 0 ? -6 : 1);

        return new Date(date.setDate(diff));
    }

    function endOfWeek(date) {
        var lastday = date.getDate() - (date.getDay() - 1) + 6;
        return new Date(date.setDate(lastday));

    }

    function changedate() {
        let id = document.getElementById("report-daterange").value;
        let cl = document.getElementById("block");
        let text = document.getElementById("datetime-info");
        cl.className = "hide";

        var curr = new Date(); // get current date
        switch (id) {
            case "other":
                cl.className = "show";
                text.innerHTML = "";
                break;
            case "day":
                firstday = new Date();
                lastday = new Date();
                text.innerHTML = "Период: " + formatDate(new Date()) + "<BR><BR>";
                break;
            case "week":
                firstday = startOfWeek(curr);
                lastday = endOfWeek(curr);
                break;
            case "last_7days":
                var lastday = new Date();
                var firstday = new Date(curr.setDate(curr.getDate() - curr.getDay() - 6));
                break;
            case "month":
                var lastday = new Date();
                var firstday = new Date(curr.getFullYear(), curr.getMonth(), 1);
                break;
            case "last_month":
                var firstday = new Date(curr.getFullYear(), curr.getMonth() - 1, 1);
                var lastday = new Date(curr.getFullYear(), curr.getMonth(), 0);
                break;
            case "year":
                var lastday = new Date();
                var firstday = new Date(curr.getFullYear(), 0, 1);
                break;
            case "last_year":
                var firstday = new Date(curr.getFullYear() - 1, 0, 1);
                var lastday = new Date(curr.getFullYear(), 0, 0);
                break;
            default:
                text.innerHTML = "";
                break;
        }
        text.innerHTML = "Период: " + formatDate(firstday) + " - " + formatDate(lastday) + "<BR><BR>";
        document.getElementById('report-datetime').value = formatDate(firstday, "input") + ' to ' + formatDate(lastday, "input");

    }
</script>

<div class="col-md-4">
    <h1>Отчеты</h1>
    <?php $form = ActiveForm::begin( [ 'options' => [ 'style' => 'width: 100%;' ] ] ); ?>
    <?= $form->field( $model, "type" )->dropDownList( [
        1 => 'Расходы',
        2 => 'Доходы'
    ] ) ?>
    <?= $form->field( $model, "view" )->dropDownList( [
        1 => 'По категориям',
        0 => 'Детально'
    ] ) ?>
    <?= $form->field( $model, "daterange" )->dropDownList( [
        'day'        => 'Этот день',
        'week'       => 'Эта неделя',
        'last_7days' => 'Последние 7 дней',
        'month'      => 'Этот месяц',
        'last_month' => 'Прошлый месяц',
        'year'       => 'Этот год',
        'last_year'  => 'Прошлый год',
        'other'      => 'Свой выбор',
    ], [ 'onchange' => 'changedate()' ] ) ?>

    <div id="datetime-info"></div>
    <div id="block" class="hide">
        <?= $form->field( $model, 'datetime' )->widget( Flatpickr::className(), [
            'locale'        => 'ru',
            'options'       => [
                'class' => 'form-control',
            ],
            'clientOptions' => [
                // config options https://chmln.github.io/flatpickr/options/
                'mode'        => 'range',
                'altInput'    => true,
                'altFormat'   => "j F Y",
                'defaultDate' => $model->datetime,
                'dateFormat'  => "Y-m-d",
                'weekNumbers' => true,
                'allowInput'  => false,
            ]
        ] ) ?>
    </div>
    <?= Html::submitButton( 'Составить отчет' ) ?>
    <?php ActiveForm::end(); ?>

    <?php
    if ($model->type==1) {
        $class="text-danger";
    } else {
        $class="text-success";
    }

    if (isset($model->view) and $model->view==0) {
        echo "<h4> Итого <span class='".$class."'>".Yii::$app->formatter->asCurrency($data['total'])."</span></h4>";

        foreach ($data['data'] as $k=>$value) {
            echo Html::img('@web/images/' . $value['deposit_image'], ['width' => 16, 'alt' => $value['deposit'], 'title' => $value['deposit']]) . "    ";
            echo $value['category_name'] . "   ";
            echo "<font size=4><span class='".$class."'>" . Yii::$app->formatter->asCurrency($value['amount']) . "</span></font>   ";
            echo Yii::$app->formatter->asDate(strtotime($value['datetime']), 'php:d M H:i') . "<BR>";
            echo $value['comment'] . "<BR>";
        }
    }
    ?>
    <div id="container" style="min-width: 500px; height: 400px; max-width: 700px; margin: 0 auto; float: left"></div>
</div>

<div style="margin-left: 82%;">
    <div>
    <select id="stat_type" onchange="showStat();">
        <option value="1">Расходы</option>
        <option value="2">Доходы</option>
        <option value="3">Доходы+Расходы</option>
    </select>
    </div>
    <?php
    echo "<table>";
    foreach ($operation as $k=>$v) {
        $sum = 0;
        $sum_class='';
        $sum = $v['spend']+$v['income'];

        if (!isset($v['income'])) {
            $v['income']=0;
        }
        if (!isset($v['spend'])) {
            $v['spend']=0;
        }

        echo "<tr><td>В ".Yii::$app->formatter->asDate(strtotime("01-".$v['date_month_year']), 'php:M Y').": </td><td><span id='total' style='visibility: hidden; color:#CCCCCC;'>".Yii::$app->formatter->asCurrency($sum)."</span></td></tr>";
        echo "<tr id='spend' style='visibility: visible;'><td><span class='text-danger'>Расход</span></td><td><span class='text-danger'>" . Yii::$app->formatter->asCurrency($v['spend']) . "</span></td></tr>";
        echo "<tr id='income' style='visibility: collapse;'><td><span class='text-success'>Доход</span></td><td><span class='text-success'>" . Yii::$app->formatter->asCurrency($v['income']) . "</span></td></tr>";
        echo "<tr><td>&nbsp;</td></tr>";

    }
    echo "</table>";
    ?>
</div>

<!--Вывод по категориям-->
<? if ($model->view == 1) { ?>
<script>
    Highcharts.chart('container', {
        chart: {
            plotBackgroundColor: null,
            plotBorderWidth: null,
            plotShadow: false,
            type: 'pie'
        },
        title: {
            text: 'Итого <?= Yii::$app->formatter->asCurrency( $data['total'] ) ?>'
        },
        tooltip: {
            valueDecimals: 2,
            valueSuffix: ' руб.',
            pointFormat: '{series.name}: <b>{point.percentage:.1f}% ({point.y})</b>',
        },
        plotOptions: {
            pie: {
                allowPointSelect: true,
                cursor: 'pointer',
                dataLabels: {
                    enabled: true,
                    format: '<b>{point.name}</b>: {point.percentage:.0f} % ({point.y:,.0f} руб.)',
                    style: {
                        color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black',
                    }
                }
            }
        },
        series: [{
            name: 'Деньги',
            colorByPoint: true,
            data: <?= $data['data'] ?>
        }]
    });
</script>
    <!--Вывод по категориям конец-->
<?php } ?>
<script>
    changedate();
</script>