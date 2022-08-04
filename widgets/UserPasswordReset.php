<?php

namespace mozzler\auth\widgets;

use mozzler\base\widgets\BaseWidget;
use yii\helpers\VarDumper;

class UserPasswordReset extends BaseWidget
{

    public function defaultConfig()
    {
        return [
            'tag' => 'div',
            'options' => [
                'class' => 'row widget-model-user-password-reset'
            ],
            'container' => [
                'tag' => 'div',
                'options' => [
                    'class' => 'col-md-12'
                ]
            ],
            'title' => 'Password Reset',
            'formConfig' => [],
            'model' => null,
            'usernameField' => 'email'
        ];
    }

    public function config($templatify = false)
    {
        $config = parent::config();
        if (!empty($config['model']) && !empty(\Yii::$app->request->get('email'))) {
            $config['model']->email = \Yii::$app->request->get('email');
            \Yii::debug("Defaulting the model->email to: " . VarDumper::export($config['model']->email));
        }

        return $config;
    }

}

