<?php

use yii\helpers\ArrayHelper;
use yii\web\Application;

defined('YII_APP_BASE_PATH') || define('YII_APP_BASE_PATH', dirname(dirname(__DIR__)) . '/');

require YII_APP_BASE_PATH . 'common/web/index.php';
require YII_APP_BASE_PATH . 'backend/config/bootstrap.php';

$config = ArrayHelper::merge(
    require YII_APP_BASE_PATH . 'common/config/main.php',
    require YII_APP_BASE_PATH . 'common/config/main-local.php',
    require YII_APP_BASE_PATH . 'backend/config/main.php',
    require YII_APP_BASE_PATH . 'backend/config/main-local.php'
);

$application = new Application($config);
$application->run();
