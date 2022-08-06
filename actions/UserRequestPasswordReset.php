<?php

namespace mozzler\auth\actions;

use app\models\User;
use Yii;
use yii\web\ViewAction;

class UserRequestPasswordReset extends \mozzler\base\actions\BaseModelAction
{

    public $id = 'userRequestPasswordReset';

    public function init()
    {
        $this->id = 'userRequestPasswordReset';

        parent::init();
    }

    public function run()
    {

        if (!Yii::$app->user->isGuest) {
            Yii::$app->session->setFlash('warning', "You are already logged in, no need to request a password reset");
            return $this->controller->goHome();
        }

        /** @var User $model */
        $model = Yii::createObject(Yii::$app->user->identityClass);
        $model->setScenario($model::SCENARIO_REQUEST_PASSWORD_RESET);

        if ($model->load(Yii::$app->request->post())) {
            $this->requestPasswordReset($model);
        }

        $this->controller->data['model'] = $model;

        return parent::run();
    }

    protected function requestPasswordReset($model)
    {
        $usernameField = $model::$usernameField;
        /** @var User $user */
        $user = $model::findByUsername($model->$usernameField);

        // -- Invalid user
        if (empty($user)) {
            Yii::$app->session->setFlash('error', "Invalid Username or Password");
            return false;
        }

        // -- Generate password reset token
        $user->generatePasswordResetToken();
        // Saving the user, running validation, but also disabling permission checks
        if (!$user->save(true, null, false)) {
            Yii::$app->session->setFlash('error', "Unable to generate reset token at this time");
            return false;
        }

        // -- Send reset email
        $appName = Yii::$app->name;
        $subject = $appName . ": Reset Password";

        $response = \Yii::$app->t->sendEmail($user->email, $subject, "user/passwordReset.twig", ["user" => $user]);

        if ($response) {
            \Yii::$app->session->setFlash('info', "Reset password request sent to " . $user->email);
            $this->controller->redirect($user->getUrl('login', ['email' => $user->email]));
            return true;
        }

        throw new \Exception('Unknown error occurred sending password reset email');
    }

}