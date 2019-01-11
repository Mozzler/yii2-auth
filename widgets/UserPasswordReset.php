<?php
namespace mozzler\auth\widgets;

use mozzler\base\widgets\BaseWidget;

class UserPasswordReset extends BaseWidget {
	
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
	
}

?>