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
            Yii::debug("You are already logged in, redirecting you home");
            return $this->controller->goHome();
        }

        $model = Yii::createObject(Yii::$app->user->identityClass);
        $model->setScenario($model::SCENARIO_LOGIN);

        if ($model->load(Yii::$app->request->post()) && $this->login($model)) {
            // -- Check if they have a login redirect and then get the URL from the session
            // Note: We check if the query param is set so an old session redirect isn't accidentally triggered
            if (Yii::$app->getRequest()->get('hasRedirect') === 'true') {
                $postLoginRedirect = Yii::$app->getSession()->get('postLoginRedirect');
                if (!empty($postLoginRedirect)) {
                    Yii::info("Redirecting you to the postLoginRedirect of: $postLoginRedirect");
                    Yii::$app->getSession()->remove('postLoginRedirect');
                    return Yii::$app->getResponse()->redirect($postLoginRedirect);
                }
            }
            Yii::$app->getSession()->remove('postLoginRedirect');
            Yii::info("Redirecting to home");

            return $this->controller->goHome();
        }

        $model->password = '';
        $this->controller->data['model'] = $model;

        return parent::run();
    }

    protected function login($model)
    {
        $postLoginRedirect = Yii::$app->getSession()->get('postLoginRedirect');
        Yii::debug("The postLoginRedirect is: " . VarDumper::export($postLoginRedirect));

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