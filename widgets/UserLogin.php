<?php

namespace mozzler\auth\widgets;

use mozzler\base\widgets\BaseWidget;
use Yii;
use yii\helpers\VarDumper;

class UserLogin extends BaseWidget
{

    public function defaultConfig()
    {
        return [
            'tag' => 'div',
            'options' => [
                'class' => 'row widget-model-user-login'
            ],
            'container' => [
                'tag' => 'div',
                'options' => [
                    'class' => 'col-md-12'
                ]
            ],
            'title' => 'Login',
            'formConfig' => [],
            'usernameField' => 'email',
            'passwordField' => 'password'
        ];
    }

    public function config($templatify = false)
    {
        $config = parent::config();

        // -- Allow having the email field prefilled for when logging in
        if (!empty($config['model']) && !empty(Yii::$app->request->get('email'))) {
            $config['model']->email = Yii::$app->request->get('email');
            Yii::debug("Setting the default email to be the url provided: " . VarDumper::export($config['model']->email));
        }
        return $config;
    }
}

