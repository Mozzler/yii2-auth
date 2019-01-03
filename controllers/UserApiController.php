<?php
namespace mozzler\auth\controllers;

use mozzler\base\controllers\ActiveController;
use yii\helpers\ArrayHelper;
use mozzler\auth\models\oauth\OauthAccessToken;

class UserApiController extends ActiveController {

	public $modelClass = 'mozzler\auth\models\User';
	public $module;
	
	public function init() {
	    parent::init();
	    $this->module = \Yii::$app->getModule('oauth2');	    
    }
	
	public static function rbac() {
		return [
			'registered' => [
				'create' => [
					'grant' => false
				],
				'update' => [
					'grant' => true
				],
				'delete' => [
					'grant' => false
				]
			]
		];
	}

    public function actionToken()
    {
        $response = $this->module->getServer()->handleTokenRequest();
		
        $params = $response->getParameters();
        
        if (isset($params['access_token'])) {
		    // include the user_id in the access_token response
		    $user = \Yii::createObject($this->modelClass);
		    $user = $user->findIdentityByAccessToken($params['access_token']);

		    $params['user_id'] = $user->id;
		    $usernameField = $user::$usernameField;
		    $params['username'] = $user->$usernameField;
	    }
	    
	    return $params;
    }
    
    public function actionRevoke()
    {
        /** @var $response \OAuth2\Response */
        $response = $this->module->getServer()->handleRevokeRequest();
        return $response->getParameters();
    }

    public function actionUserInfo()
    {
        $response = $this->module->getServer()->handleUserInfoRequest();
        return $response->getParameters();
    }
	
}
