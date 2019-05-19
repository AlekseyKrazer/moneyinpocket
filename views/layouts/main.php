<?php

/* @var $this \yii\web\View */
/* @var $content string */

use app\assets\AppAsset;
use app\widgets\Alert;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\widgets\Breadcrumbs;

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
            [ 'label' => 'Главная', 'url' => [ '/site/main' ] ],
            ['label' => 'Личный кабинет', 'url' => ['/accounting/index'], 'visible' => !Yii::$app->user->isGuest],
            ['label' => 'About', 'url' => ['/site/about']],
            ['label' => 'Contact', 'url' => ['/site/contact']],
            Yii::$app->user->isGuest ? (
                ['label' => 'Login', 'url' => ['/site/login']]
            ) : (
                '<li>'
                . Html::beginForm(['/site/logout'], 'post')
                . Html::submitButton(
                    'Выход (' . Yii::$app->user->identity->username . ')',
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

        <?php if (in_array(Yii::$app->controller->getUniqueId(), [
            'accounting',
            'deposit',
            'categories',
            'report'
        ])) : ?>
        <div class="col-md-4">

            <?= Nav::widget([
                'items' => [
                    // Important: you need to specify url as 'controller/action',
                    // not just as 'controller' even if default action is used.
                    ['label' => 'Ввод операций', 'url' => ['accounting/index'], 'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                    ['label' => 'План расходов', 'url' => ['accounting/planning'], 'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                    ['label' => 'Категории затрат', 'url' => ['categories/index', 'type' => 1], 'active' => in_array(Yii::$app->controller->module->requestedRoute, ['categories/index', 'categories/update']) && Yii::$app->getRequest()->getQueryParam('type') == 1, 'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                    ['label' => 'Счета (места хранения денег)', 'url' => ['deposit/index', 'debt' => 0], 'active' => in_array(Yii::$app->controller->module->requestedRoute, ['deposit/index','deposit/update']) && Yii::$app->getRequest()->getQueryParam('debt') == 0,'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                    ['label' => 'Доходы', 'url' => ['categories/index', 'type' => 2], 'active' => in_array(Yii::$app->controller->module->requestedRoute, ['categories/index', 'categories/update']) && Yii::$app->getRequest()->getQueryParam('type') == 2, 'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                    ['label' => 'Долги\Займы', 'url' => ['deposit/index', 'debt' => 1], 'active' => in_array(Yii::$app->controller->module->requestedRoute, ['deposit/index','deposit/update']) && Yii::$app->getRequest()->getQueryParam('debt') == 1, 'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                    [
                        'label' => 'Отчеты',
                        'url' => [ 'report/index' ],
                        'linkOptions' => [ 'class' => 'list-group-item' ],
                        'options' => [ 'class' => 'list-group-item' ]
                    ],
                    // 'Products' menu item will be selected as long as the route is 'product/index'
//            ['label' => 'Products', 'url' => ['product/index'], 'items' => [
//                ['label' => 'New Arrivals', 'url' => ['product/index', 'tag' => 'new']],
//                ['label' => 'Most Popular', 'url' => ['product/index', 'tag' => 'popular']],
//            ]],
//                    ['label' => 'Login', 'url' => ['site/login'], 'visible' => Yii::$app->user->isGuest, 'linkOptions' => ['class' => 'list-group-item'], 'options' => ['class' => 'list-group-item']],
                ],
            ]);

            ?>
            <BR><BR>
        </div>
        <?php endif; ?>
        <?= $content ?>
    </div>
</div>

<footer class="footer">
    <div class="container">
        <p class="pull-left">&copy; Moneyinpocket <?= date('Y') ?></p>

        <p class="pull-right"><!-- Yandex.Metrika counter -->
            <script type="text/javascript" >
                (function (d, w, c) {
                    (w[c] = w[c] || []).push(function() {
                        try {
                            w.yaCounter51124634 = new Ya.Metrika2({
                                id:51124634,
                                clickmap:true,
                                trackLinks:true,
                                accurateTrackBounce:true
                            });
                        } catch(e) { }
                    });

                    var n = d.getElementsByTagName("script")[0],
                        s = d.createElement("script"),
                        f = function () { n.parentNode.insertBefore(s, n); };
                    s.type = "text/javascript";
                    s.async = true;
                    s.src = "https://mc.yandex.ru/metrika/tag.js";

                    if (w.opera == "[object Opera]") {
                        d.addEventListener("DOMContentLoaded", f, false);
                    } else { f(); }
                })(document, window, "yandex_metrika_callbacks2");
            </script>
        <noscript><div><img src="https://mc.yandex.ru/watch/51124634" style="position:absolute; left:-9999px;" alt="" /></div></noscript>
        <!-- /Yandex.Metrika counter --></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
