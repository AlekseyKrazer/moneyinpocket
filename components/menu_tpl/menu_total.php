<?php

use yii\helpers\Html;

?>
<?php if ($category['total']!='' and $category['total']!=0) : ?>
    <?php if ($category['type'] == 'dep') : ?>
    <div class="col-sm-8">
        <?= $tab ?>
        <?php if (isset($category['images']) and $category['images'] != '') :
            echo Html::img('@web/images/' . $category['images'], ['width' => 16]);
        endif; ?>
        <?= $category['name'] ?>
    </div>
        <div class="col-sm-4">
        <?php
        if ($category['total']<0) {
            echo "<span style=\"color:red\">" . Yii::$app->formatter->asCurrency($category['total']) . "</span>";
        } else {
            echo "<span style=\"color:green\">" . Yii::$app->formatter->asCurrency($category['total']) . "</span>";
        }

        ?>
        </div>
        <br>
    <?php endif; ?>
<?php endif; ?>
<?php if ($category['type'] == 'cat') : ?>
<BR>
    <div class="col-sm-7">
    <b><?= $tab . $category['name'] ?></b>
    </div>
    <br>
<?php endif; ?>
<?php if (isset($category['childs'])) : ?>
    <?= $this->getMenuHtml($category['childs'], $tab . "&nbsp;&#9;&nbsp;&#9;&nbsp;&#9;") ?>
<?php endif; ?>