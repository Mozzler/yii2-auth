<?php
namespace mozzler\auth\actions;

use Yii;
use yii\web\ViewAction;

class UserLogoutAction extends \mozzler\base\actions\BaseModelAction {

	public $id = 'logout';
	
	public function run() {
		Yii::$app->user->logout();

        return $this->controller->goHome();
	}
	
}