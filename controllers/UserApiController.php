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
        return \yii\helpers\ArrayHelper::merge(parent::rbac(), [
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
		]);
	}


    /**
     * @return null\
     *
     * Now calling the $user->beforeLogin() and afterLogin() methods in order to trigger the associated events and let you do things like set the last logged in
     */
    public function actionToken()
    {
        $response = $this->module->getServer()->handleTokenRequest();

        $params = $response->getParameters();

        if (isset($params['access_token'])) {
            // include the user_id in the access_token response
            /** @var \app\models\User $user */
            $user = \Yii::createObject($this->modelClass);
            $user = $user->findIdentityByAccessToken($params['access_token']);

            if ($user->hasMethod('beforeLogin') && !$user->beforeLogin()) {
                \Yii::error("The Before Login Event is not valid");
                if (\Yii::$app->has('session')) {
                    \Yii::$app->session->addFlash('warning', 'You are unable to Login');
                }
                return null;
            }
            // Trigger the User After Login event
            $params['user_id'] = $user->id;
            $usernameField = $user::$usernameField;
            $params['username'] = $user->$usernameField;

            // Trigger the User After Login event, e.g you can use this for setting the $user->lastLoggedIn
            if ($user->hasMethod('afterLogin')) {
                // E.g Set the last logged in field
                $user->afterLogin();
            }
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
