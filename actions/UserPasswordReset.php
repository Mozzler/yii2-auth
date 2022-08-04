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
		    \Yii::$app->session->setFlash('warning', "You are already logged in, no need to reset your password");
            return $this->controller->goHome();
        }

        $model = \Yii::createObject(\Yii::$app->user->identityClass);
        $model->setScenario($model::SCENARIO_PASSWORD_RESET);
        $model->passwordResetToken = \Yii::$app->request->get('token');

        $config = \Yii::$app->params['mozzler.auth']['user']['passwordReset'];

        if ($model->load(Yii::$app->request->post())) {
            $user = $model->findByPasswordResetToken($model->passwordResetToken);
            
            if ($user) {
                // check email matches
                if ($user->email != $model->email) {
                    $model->addError('email', $config['emailMismatch']);
                }
                else {
                    $user->setScenario($model::SCENARIO_PASSWORD_RESET);
                    $user->password = $model->password;
                    $user->passwordResetToken = null;

                    if ($user->save(true, null, false)) {
                        \Yii::info("Password reset for user {$user->email}", __METHOD__);
                        \Yii::$app->session->setFlash('info', $config['successMessage']);
                        return $this->controller->redirect($config['redirectUrl']);
                    } else {
                        \Yii::error("Password reset failed for user {$user->email}", __METHOD__);
                    }
                }
            } else {
                \Yii::$app->session->setFlash('error', $config['invalidToken']);
                $model->addError(null, $config['invalidToken']);
                \Yii::info("Invalid token supplied for {$model->email}", __METHOD__);
            }
        }

        $this->controller->data['model'] = $model;
		return parent::run();
    }

}