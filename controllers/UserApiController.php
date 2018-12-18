<?php
namespace mozzler\auth\controllers;

use mozzler\base\controllers\ActiveController;
use yii\helpers\ArrayHelper;

class UserApiController extends ActiveController {

	public $modelClass = 'mozzler\auth\models\User';
	public $module;
	
	public function init() {
	    parent::init();
	    $this->module = \Yii::$app->getModule('oauth2');	    
    }

    public function actionToken()
    {
        /** @var $response \OAuth2\Response */
        $response = $this->module->getServer()->handleTokenRequest();
        $params = $response->getParameters();
        
        if (isset($params['access_token'])) {
		    // include the user_id in the access_token response
		    $tokens = new OauthAccessTokens();
		    $params['user_id'] = $tokens->findUserId($params['access_token']);
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
