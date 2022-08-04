<?php
namespace mozzler\auth\widgets;

use mozzler\base\widgets\BaseWidget;
use yii\helpers\VarDumper;

class UserRequestPasswordReset extends BaseWidget {
	
	public function defaultConfig()
	{
		return [
			'tag' => 'div',
			'options' => [
				'class' => 'row widget-model-user-request-password-reset'
			],
			'container' => [
				'tag' => 'div',
				'options' => [
					'class' => 'col-md-12'
				]
			],
			'title' => 'Request Password Reset',
			'formConfig' => [],
			'model' => null,
			'usernameField' => 'email'
		];
	}

    public function config($templatify = false)
    {
        $config = parent::config();
        if (!empty($config['model']) &&  !empty(\Yii::$app->request->get('email'))) {
            $config['model']->email = \Yii::$app->request->get('email');
            \Yii::debug("Setting the default email to be the url provided: " . VarDumper::export($config['model']->email));
        }
        return $config;
    }
	
}