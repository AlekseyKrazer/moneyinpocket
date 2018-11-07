<?php

use yii\helpers\Html;

?>
<?php if ($category['type'] == 'dep') : ?>
    <?= $tab ?>
    <?php if (isset($category['images']) and $category['images'] != ''):
        echo Html::img('@web/images/' . $category['images'], ['width' => 16]);
    endif; ?>
    <a href="<?= \yii\helpers\Url::to(['accounting/' . $this->source . '-update', 'id' => $category['id']]) ?>"><?= $category['name'] ?></a>
    <?= Html::a('<span class="glyphicon glyphicon-remove"></span>', [$this->source . '-delete', 'id' => $category['id']], [
        'data' => [
            'confirm' => 'Вы действительно хотите удалить?',
            'method' => 'post',
        ],
    ]) ?> <br>
<?php endif; ?>
<?php if ($category['type'] == 'cat') : ?>
    <b><?= $tab . $category['name'] ?></b>
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