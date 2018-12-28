<?php
namespace mozzler\auth\widgets;

use mozzler\base\widgets\BaseWidget;

class UserLogin extends BaseWidget {
	
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
	
	/*
	public function config($templatify=false) {
		$config = parent::config($templatify);
		
		return $config;
	}*/
	
}

?>