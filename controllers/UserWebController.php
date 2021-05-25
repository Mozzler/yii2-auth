<?php

namespace mozzler\auth\controllers;

use mozzler\base\controllers\ModelController;

class UserWebController extends ModelController
{

    public $modelClass = 'mozzler\auth\models\User';
    public static $moduleClass = 'mozzler\auth\Module';

    public function actions()
    {
        return \yii\helpers\ArrayHelper::merge(parent::actions(), [
            'login' => 'mozzler\auth\actions\UserLoginAction',
            'logout' => 'mozzler\auth\actions\UserLogoutAction',
            'requestPasswordReset' => 'mozzler\auth\actions\UserRequestPasswordReset',
            'passwordReset' => 'mozzler\auth\actions\UserPasswordReset'
        ]);
    }


    public static function rbac()
    {
        return [
            'public' => [
                'create' => [
                    'grant' => false
                ],
                'view' => [
                    'grant' => false
                ],
                'update' => [
                    'grant' => false
                ],
                'index' => [
                    'grant' => false
                ],
                'delete' => [
                    'grant' => false
                ],
                'deleteMany' => [
                    'grant' => false
                ],
                'export' => [
                    'grant' => false
                ],
            ],
            'admin' => [
                'create' => [
                    'grant' => true
                ],
                'view' => [
                    'grant' => true
                ],
                'update' => [
                    'grant' => true
                ],
                'index' => [
                    'grant' => true
                ],
                'delete' => [
                    'grant' => false
                ],
                'deleteMany' => [
                    'grant' => false
                ],
                'export' => [
                    'grant' => true
                ],
            ],
            'registered' => [
                'create' => [
                    'grant' => false
                ],
                'view' => [
                    'grant' => true
                ],
                'update' => [
                    'grant' => true
                ],
                'index' => [
                    'grant' => true
                ],
                'delete' => [
                    'grant' => false
                ],
                'deleteMany' => [
                    'grant' => false
                ],
                'export' => [
                    'grant' => false
                ],
            ]
        ];
    }
}