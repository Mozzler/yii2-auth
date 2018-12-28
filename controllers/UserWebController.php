<?php
namespace mozzler\auth\controllers;

use mozzler\base\controllers\ModelController;

class UserWebController extends ModelController {

	public $modelClass = 'mozzler\auth\models\User';
	public static $moduleClass = 'mozzler\auth\Module';
	
	public function actions() {
		return \yii\helpers\ArrayHelper::merge(parent::actions(), [
			'login' => 'mozzler\auth\actions\UserLoginAction',
			'logout' => 'mozzler\auth\actions\UserLogoutAction'
		]);
	}
	
}