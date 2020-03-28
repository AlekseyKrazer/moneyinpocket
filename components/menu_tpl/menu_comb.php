<?php

use yii\helpers\Html;

?>
<?php if ($category['type'] == 'dep') : ?>
    <?= $tab ?>
    <?php if (isset($category['images']) and $category['images'] != '') :
        echo Html::img('@web/images/' . $category['images'], ['width' => 16]);
    endif; ?>
    <a href="<?= \yii\helpers\Url::to(['deposit/update', 'id' => $category['id'], 'debt' => $this->debt]) ?>"
    <?php if ($category['hide'] == 1) :
        echo " style='color: rgb(204, 204, 204)' ";
    endif; ?>
    >
        <?= $category['name'] ?>
    </a>
    <?= Html::a('<span class="glyphicon glyphicon-remove"></span>', ['delete', 'id' => $category['id'], 'debt' => $this->debt], [
        'data' => [
            'confirm' => 'Вы действительно хотите удалить?',
            'method' => 'post',
        ],
    ]) ?> <br>
<?php endif; ?>
<?php if ($category['type'] == 'cat') : ?>
    <b><?= $tab . $category['name'] ?></b>
            <?= Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['group-update', 'id' => $category['id'], 'debt' => $this->debt], [
        ]) ?>
    <!--        --><? //= Html::a('<span class="glyphicon glyphicon-remove"></span>', [$this->source.'-delete', 'id' => $category['id']], [
//            'data' => [
//                'confirm' => 'Вы действительно хотите удалить?',
//                'method' => 'post',
//            ],
//        ]) ?>
    <br>
<?php endif; ?>
<?php if (isset($category['childs'])) : ?>
    <?= $this->getMenuHtml($category['childs'], $tab . "&nbsp;&#9;&nbsp;&#9;&nbsp;&#9;") ?>
<?php endif; ?>