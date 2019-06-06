<?php

namespace mozzler\auth\actions;

use mozzler\auth\models\User;
use Yii;
use yii\web\ViewAction;

class UserLoginAction extends \mozzler\base\actions\BaseModelAction
{

    public $id = 'login';

    public function run()
    {
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

    protected function login($model)
    {
        $usernameField = $model::$usernameField;
        // NB: We force the email address to be lowercase on login
        $user = $model::findByUsername(strtolower($model->$usernameField));

        // -- Invalid user
        if (empty($user)) {
            Yii::$app->session->setFlash('error', "Invalid Username or Password");
            return false;
        }

        $valid = $user->validatePassword($model->password);


        if ($valid && User::STATUS_ACTIVE === $user->status) {
            $duration = Yii::$app->user->authTimeout;
            Yii::$app->user->login($user, empty($duration) ? 0 : $duration);
        } else {
            if ($user->status !== User::STATUS_ACTIVE) {
                $valid = false;
            }
            Yii::$app->session->setFlash('error', "Invalid Username and/or Password");
        }

        return $valid;
    }

}