<?php
namespace mozzler\auth\actions;

use Yii;
use yii\web\ViewAction;

class UserLoginAction extends \mozzler\base\actions\BaseModelAction {

	public $id = 'login';
	
	public function run() {
		if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }
        
        $model = \Yii::createObject(\Yii::$app->user->identityClass);
        $model->setScenario($model::SCENARIO_LOGIN);
        
        if ($model->load(Yii::$app->request->post()) && $this->login($model)) {
            return $this->controller->goBack();
        }

        $model->password = '';
        
        $this->controller->data['model'] = $model;
		
		return parent::run();
	}
	
	protected function login($model) {
		$usernameField = $model::$usernameField;
		$user = $model::findByUsername($model->$usernameField);
		$valid = $user->validatePassword($model->password);
		
		if ($valid) {
			Yii::$app->user->login($user, 0);
		}
		
		return $valid;
	}
	
}