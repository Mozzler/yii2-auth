<?php
namespace mozzler\auth\controllers;

use mozzler\base\controllers\ModelController;
use yii\helpers\ArrayHelper;

class OauthclientController extends ModelController
{
	
	public $modelClass = 'mozzler\auth\models\oauth\OAuthClient';

	public static function rbac() {
        return ArrayHelper::merge(parent::rbac(), [
            'registered' => [
				'index' => [
					'grant' => false
				],
				'view' => [
					'grant' => false
				],
                'create' => [
                    'grant' => false
                ],
                'update' => [
                    'grant' => false
                ],
                'delete' => [
                    'grant' => false
                ]
            ],
            'admin' => [
                'create' => [
                    'grant' => false
                ],
                'update' => [
                    'grant' => false
                ],
                'delete' => [
                    'grant' => false
                ]
            ]
        ]);
    }
	
}