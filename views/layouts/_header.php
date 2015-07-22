<header class="header-wraper">
<?php
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;

$basePath = '/site/';
NavBar::begin([
    'brandLabel' => 'OpenMath',
    'brandUrl' => Yii::$app->homeUrl.'site/login',
    'options' => [
        'class' => 'navbar-inverse navbar-fixed-top',
    ],
]);
echo '<div class="dropdown dropdown-class">
        <img src="../../img/class.png">
        <select>
            <option value="" disabled="disabled" selected="selected">My Classes</option>
            <option value="1">One</option>
            <option value="2">Two</option>
        </select>
      </div>';
echo Nav::widget([
    'options' => ['class' => 'navbar-nav navbar-right'],
    'items' => [
        Yii::$app->user->isGuest ?
            ['label' => 'Notifications', 'url' => [$basePath.'login'], 'options' => ['class' => 'notification-alignment',
]]:
            ['label' => 'Notifications', 'url' => [$basePath.'dashboard'], 'options' => ['class' => 'notification-alignment']],
        Yii::$app->user->isGuest ?
            ['label' => 'Diagnostics', 'url' => [$basePath.'diagnostics']]:'',
        Yii::$app->user->isGuest ?
            ['label' => ''] :
            ['label' => (ucfirst(Yii::$app->user->identity->FirstName) .' '.ucfirst(Yii::$app->user->identity->LastName)),
                'url' => ['/site/logout'],
                'linkOptions' => ['data-method' => 'post'], 'class' => 'user-alignment'],

    ],
]);
NavBar::end();
?>

</header>

</div>
<script>



</script>
