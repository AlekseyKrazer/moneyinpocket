<?php
use yii\helpers\Html;
?>
<?php
if ($type==3) {
    foreach ($data_for as $k => $v) {
        echo " Из ";
        echo Html::img('@web/images/' . $v['deposit_from_images'], ['width' => 16]) . "    ";
        echo $v['deposit_from_name'] . "   ";
        echo " в ";
        echo Html::img('@web/images/' . $v['deposit_to_images'], ['width' => 16]) . "    ";
        echo $v['deposit_to_name'] . "<BR>";
        echo "<font size=4>" . Html::a(Yii::$app->formatter->asCurrency($v['amount']), ['accounting/index', 'type' => 3, 'id' => $v['id']]) . "</font>   ";
        echo Yii::$app->formatter->asDate(strtotime($v['datetime']), 'php:d M H:i') . "<BR>";
        echo $v['comment'] . "<BR><BR>";
    }

} else {
    $sum_amount=0;
    if ($type==1) {
        $class="text-danger";
    } else {
        $class="text-success";
    }
    foreach ($data_for as $k => $v) {
        $sum_amount=$sum_amount+$v['amount'];
        echo Html::img('@web/images/' . $v['images'], ['width' => 16]) . "    ";
        echo $v['category_name'] . "   ";
        echo "<font size=4>" . Html::a(Yii::$app->formatter->asCurrency($v['amount']), ['accounting/index', 'type' => $_GET['type'], 'id' => $v['id']],['class' => $class ]) . "</font>   ";
        echo Yii::$app->formatter->asDate(strtotime($v['datetime']), 'php:d M H:i') . "<BR>";
        echo $v['comment'] . "<BR><BR>";
    }
    if ($sum_amount<>0) {
        echo "Итого: <font size=4 class=" . $class . ">" . Yii::$app->formatter->asCurrency($sum_amount) . "</font>";
    }
}
    ?>

