<?php
namespace mozzler\auth\actions;

use Yii;
use yii\web\ViewAction;

class UserLoginAction extends \mozzler\base\actions\BaseModelAction {

	public $id = 'login';

	public function run() {
		if (!Yii::$app->user->isGuest) {
            return $this->controller->goHome();
        }

        $model = \Yii::createObject(\Yii::$app->user->identityClass);
        $model->setScenario($model::SCENARIO_LOGIN);

        if ($model->load(Yii::$app->request->post()) && $this->login($model)) {
            return $this->controller->goHome();
        }

        $model->password = '';

        $this->controller->data['model'] = $model;

		return parent::run();
	}

	protected function login($model) {
		$usernameField = $model::$usernameField;
		// NB: We force the email address to be lowercase on login
		$user = $model::findByUsername(strtolower($model->$usernameField));

		// -- Invalid user
		if (empty($user)) {
            Yii::$app->session->setFlash('error', "Invalid Username or Password");
		    return false;
        }

		$valid = $user->validatePassword($model->password);

		if ($valid) {
			Yii::$app->user->login($user, 0);
		} else {
            Yii::$app->session->setFlash('error', "Invalid Username and/or Password");
        }

		return $valid;
	}

}