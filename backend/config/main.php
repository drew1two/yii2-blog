<?php

use yii\bootstrap\BootstrapAsset;
use yii\bootstrap\BootstrapPluginAsset;
use yii\log\FileTarget;
use common\url\AppUrlManager;
use yii\helpers\ArrayHelper;
use dominus77\maintenance\BackendMaintenance;
use dominus77\maintenance\controllers\backend\MaintenanceController;
use modules\rbac\models\Permission;
use modules\users\models\User;
use modules\rbac\components\behavior\AccessBehavior;
use modules\users\behavior\LastVisitBehavior;
use modules\main\Bootstrap as MainBootstrap;
use modules\users\Bootstrap as UserBootstrap;
use modules\rbac\Bootstrap as RbacBootstrap;
use modules\rbac\Module;
use modules\blog\Bootstrap as BlogBootstrap;
use modules\comment\Bootstrap as CommentBootstrap;
use modules\config\Bootstrap as ConfigBootstrap;
use modules\config\Module as ConfigModule;
use modules\config\components\behaviors\ConfigBehavior;
use backend\config\Params;

$params = ArrayHelper::merge(
    require __DIR__ . '/../../common/config/params.php',
    require __DIR__ . '/../../common/config/params-local.php',
    require __DIR__ . '/params.php',
    require __DIR__ . '/params-local.php'
);

return [
    'id' => 'app-backend',
    'language' => 'ru', // en, ru
    'homeUrl' => '/admin',
    'basePath' => dirname(__DIR__),
    'controllerNamespace' => 'backend\controllers',
    'defaultRoute' => 'main/default/index',
    'bootstrap' => [
        'log',
        ConfigBootstrap::class,
        MainBootstrap::class,
        UserBootstrap::class,
        RbacBootstrap::class,
        BackendMaintenance::class,
        BlogBootstrap::class,
        CommentBootstrap::class
    ],
    'modules' => [
        'config' => [
            'class' => ConfigModule::class,
            'params' => [
                'accessRoles' => [Permission::PERMISSION_ACCESS_APP_SETTINGS],
                'paramsClass' => Params::class
            ],
        ],
        'main' => [
            'isBackend' => true
        ],
        'users' => [
            'isBackend' => true
        ],
        'rbac' => [
            'class' => Module::class,
            'params' => [
                'userClass' => User::class
            ]
        ],
        'blog' => [
            'isBackend' => true
        ],
        'comment' => [
            'isBackend' => true
        ]
    ],
    'controllerMap' => [
        'maintenance' => [
            'class' => MaintenanceController::class,
            'roles' => [Permission::PERMISSION_MANAGER_MAINTENANCE]
        ],
    ],
    'components' => [
        'request' => [
            'cookieValidationKey' => '',
            'csrfParam' => '_csrf-backend',
            'baseUrl' => '/admin'
        ],
        'assetManager' => [
            'bundles' => [
                BootstrapAsset::class => [
                    'sourcePath' => '@vendor/almasaeed2010/adminlte/bower_components/bootstrap/dist',
                    'css' => [
                        YII_ENV_DEV ? 'css/bootstrap.css' : 'css/bootstrap.min.css'
                    ]
                ],
                BootstrapPluginAsset::class => [
                    'sourcePath' => '@vendor/almasaeed2010/adminlte/bower_components/bootstrap/dist',
                    'js' => [
                        YII_ENV_DEV ? 'js/bootstrap.js' : 'js/bootstrap.min.js'
                    ]
                ]
            ]
        ],
        'user' => [
            'identityClass' => User::class,
            'enableAutoLogin' => true,
            'identityCookie' => ['name' => '_identity-backend', 'httpOnly' => true],
            'loginUrl' => ['/users/default/login']
        ],
        'session' => [
            // this is the name of the session cookie used for login on the backend
            'name' => 'advanced-backend'
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => FileTarget::class,
                    'levels' => ['error', 'warning']
                ]
            ]
        ],
        'errorHandler' => [
            'errorAction' => 'backend/error'
        ],
        'urlManager' => [
            'class' => AppUrlManager::class,
            'baseUrl' => '/admin',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => []
        ],
        'urlManagerBackend' => [
            'class' => AppUrlManager::class,
            'baseUrl' => '/admin',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => []
        ],
        'urlManagerFrontend' => [
            'class' => AppUrlManager::class,
            'baseUrl' => '',
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'enableStrictParsing' => true,
            'rules' => [
                'email-confirm' => 'users/default/email-confirm'
            ]
        ]
    ],
    // Последний визит
    'as afterAction' => [
        'class' => LastVisitBehavior::class
    ],
    // Доступ к админке
    'as AccessBehavior' => [
        'class' => AccessBehavior::class,
        'permission' => Permission::PERMISSION_VIEW_ADMIN_PAGE, // Разрешение доступа к админке
    ],
    // Подключаем поведение для замены параметров конфигурации нашими параметрами
    'as beforeConfig' => [
        'class' => ConfigBehavior::class,
    ],
    'params' => $params
];
