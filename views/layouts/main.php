<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\widgets\Alert;
use yii\helpers\Html;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use yii\helpers\Url;

AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => Yii::$app->name,
        'brandUrl' => Yii::$app->homeUrl,
        'options' => [
            'class' => 'navbar-inverse navbar-fixed-top',
        ],
    ]);
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav navbar-right'],
        'items' => [
            ['label' => 'Home', 'url' => ['/site/index']],
            ['label' => 'Личный кабинет', 'url' => ['/accounting/index'], 'visible' => !Yii::$app->user->isGuest],
            ['label' => 'About', 'url' => ['/site/about']],
            ['label' => 'Contact', 'url' => ['/site/contact']],
            Yii::$app->user->isGuest ? (
                ['label' => 'Login', 'url' => ['/site/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Logout (' . Yii::$app->user->identity->username . ')',
                    ['class' => 'btn btn-link logout']
                )
                . Html::endForm()
                . '</li>'
            ),
        ],
    ]);
    NavBar::end();
    ?>

    <div class="container">
        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <?= Alert::widget() ?>

        <?php if (Yii::$app->controller->getUniqueId()=='accounting'): ?>
        <div class="col-md-4">

            <?= Nav::widget([
                'items' => [
                    // Important: you need to specify url as 'controller/action',
                    // not just as 'controller' even if default action is used.
                    ['label' => 'Ввод операций', 'url' => ['accounting/index'], 'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                    ['label' => 'План расходов', 'url' => ['accounting/planning'], 'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                    ['label' => 'Категории затрат', 'url' => ['accounting/category'], 'active' => in_array(Yii::$app->controller->module->requestedRoute, ['accounting/category','accounting/category-update']), 'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                    ['label' => 'Счета (места хранения денег)', 'url' => ['accounting/deposit'], 'active' => in_array(Yii::$app->controller->module->requestedRoute, ['accounting/deposit','accounting/deposit-update']),'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                    ['label' => 'Доходы', 'url' => ['accounting/income'], 'active' => in_array(Yii::$app->controller->module->requestedRoute, ['accounting/income','accounting/income-update']), 'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                    ['label' => 'Долги\Займы', 'url' => ['accounting/debt'], 'active' => in_array(Yii::$app->controller->module->requestedRoute, ['accounting/debt','accounting/debt-update']), 'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                    ['label' => 'Отчеты', 'url' => ['accounting/report'], 'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                    // 'Products' menu item will be selected as long as the route is 'product/index'
//            ['label' => 'Products', 'url' => ['product/index'], 'items' => [
//                ['label' => 'New Arrivals', 'url' => ['product/index', 'tag' => 'new']],
//                ['label' => 'Most Popular', 'url' => ['product/index', 'tag' => 'popular']],
//            ]],
//                    ['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest, 'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                ],
            ]);

            ?>
        </div>
        <?php endif; ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; My Company <?= date('Y') ?></p>

        <p class="pull-right"><?= Yii::powered() ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
