<?php
namespace mozzler\auth\actions;

use yii\web\ViewAction;

class UserLoginAction extends ViewAction {

	public $modelClass = 'mozzler\auth\models\User';
	
	public function run() {
		return $this->controller->render('login');
	}
	
}