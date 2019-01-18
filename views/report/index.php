<?php
/* @var $this yii\web\View */

use bs\Flatpickr\FlatpickrWidget as Flatpickr;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>
<script src="https://code.highcharts.com/highcharts.js"></script>
<script src="https://code.highcharts.com/modules/exporting.js"></script>
<script src="https://code.highcharts.com/modules/export-data.js"></script>

<div class="col-md-4">
    <h1>report/index</h1>
	<?php $form = ActiveForm::begin( [ 'options' => [ 'style' => 'width: 100%;' ] ] ); ?>
	<?= $form->field( $model, "type" )->dropDownList( [
		1 => 'Расходы',
		2 => 'Доходы',
		3 => 'Обмен'
	], [ 'options' => [ 1 => [ 'Selected' => true ] ] ] ) ?>
	<?= $form->field( $model, "view" )->dropDownList( [
		0 => 'Детально',
		1 => 'По категориям'
	], [ 'options' => [ 1 => [ 'Selected' => true ] ] ] ) ?>
	<?= $form->field( $model, "daterange" )->dropDownList( [
		'day'        => 'Этот день',
		'week'       => 'Эта неделя',
		'month'      => 'Этот месяц',
		'year'       => 'Этот год',
		'last_month' => 'Прошлый месяц',
		'last_year'  => 'Прошлый год',
		'other'      => 'Другой период'
	] ) ?>
	<?= $form->field( $model, 'datetime' )->widget( Flatpickr::className(), [
		'locale'  => 'ru',
		'options' => [
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

		],
	] ) ?>
	<?= Html::submitButton( 'Составить отчет' ) ?>
	<?php ActiveForm::end(); ?>
    <p>
        You may change the content of this page by modifying
        the file <code><?= __FILE__; ?></code>.
    </p>
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
            text: 'Browser market shares in January, 2018'
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