<?php
namespace mozzler\auth\actions;

use Yii;
use yii\web\ViewAction;

class UserPasswordReset extends \mozzler\base\actions\BaseModelAction {

	public $id = 'userPasswordReset';
	
	public function init() {
    	$this->id = 'userPasswordReset';
    	
    	parent::init();
	}

	public function run() {
		if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = \Yii::createObject(\Yii::$app->user->identityClass);
        $model->setScenario($model::SCENARIO_PASSWORD_RESET);

        if ($model->load(Yii::$app->request->post())) {
            $this->requestPasswordReset($model);
        }

        $this->controller->data['model'] = $model;

		return parent::run();
	}

	protected function requestPasswordReset($model) {
		$usernameField = $model::$usernameField;
		$user = $model::findByUsername($model->$usernameField);

		// -- Invalid user
		if (empty($user))
		{
            Yii::$app->session->setFlash('error', "Invalid Username or Password");
		    return false;
        }
        
        // generate password reset token
        $user->generatePasswordResetToken();
        // saving the user, running validation, but also disabling permission checks
        if (!$user->save(true, null, false))
        {
            Yii::$app->session->setFlash('error', "Unable to generate reset token at this time");
            return false;
        }
        
        // send reset email
        $appName = \Yii::$app->name;
        $subject = $appName . ": Reset Password";
        // TODO
        //\Yii::$app->t->sendEmail($user->email, $subject, "emails/passwordReset.twig", ["model" => user]);

        \Yii::$app->session->setFlash('info', "Reset password request sent to ".$user->email);
		return true;
	}

}