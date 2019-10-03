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
<?php
//print_r("<PRE>");
//print_r($model);
//print_r("</PRE>");
?>
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
</div>

<div class="col-md-4">
    Табличка сводник с графиком
</div>
<div id="container" style="min-width: 500px; height: 400px; max-width: 700px; margin: 0 auto"></div>
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
<script>
    changedate();
</script>