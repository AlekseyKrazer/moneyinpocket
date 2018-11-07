<?php
use yii\helpers\Html;
?>
<li>
    <a href="<?= \yii\helpers\Url::to(['accounting/'.$this->source.'-update', 'id' => $category['id']]) ?>"><?= $category['name'] ?></a>
    <?= Html::a('<span class="glyphicon glyphicon-remove"></span>', [$this->source.'-delete', 'id' => $category['id']], [
        'data' => [
            'confirm' => 'Вы действительно хотите удалить?',
            'method' => 'post',
        ],
    ]) ?>
    <?php if (isset($category['childs'])) : ?>
    <ul>
        <?= $this->getMenuHtml ($category['childs']) ?>
    </ul>
    <?php endif; ?>
</li>