<?php
namespace mozzler\auth\widgets;

use mozzler\base\widgets\BaseWidget;

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
	
}

?>