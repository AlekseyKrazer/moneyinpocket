<?php

use yii\helpers\Html;

?>
<?php if ($category['total']!='' and $category['total']!=0 and $category['hide']!=1) : ?>
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
        echo "<span id='".$this->source." ".$category['id']."' style='display:none'>".$category['total']."</span>";

        ?>
        </div>
        <br>
    <?php endif; ?>
<?php endif; ?>
<?php if ($category['type'] == 'cat') : ?>
<BR>
    <div class="col-sm-7">
        <b><a onclick="show_main_category('category_<?= $category['id'] ?>')" style="cursor: pointer; color: black"><?= $tab . $category['name'] ?></a></b>
    </div>
    <br>
    <?php if (isset($category['childs'])) : ?>
        <span id="category_<?= $category['id'] ?>">
        <?= $this->getMenuHtml($category['childs'], $tab . "&nbsp;&#9;&nbsp;&#9;&nbsp;&#9;") ?>
        </span>
    <?php endif; ?>
<?php endif; ?>
