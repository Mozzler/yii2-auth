<?php
namespace mozzler\auth;
use yii\helpers\ArrayHelper;

class OauthModule extends \filsh\yii2\oauth2server\Module
{	
	public function __construct($id, $parent=null, $config=[])
	{
		$defaultConfig = [
			'tokenParamName' => 'accessToken',
		    'tokenAccessLifetime' => 3600 * 24,
		    'options' => [
		        // Server options
		        'allow_implicit' => true
		    ],
		    'storageMap' => [
		        'user_credentials'		=> 'mozzler\auth\yii\oauth\storage\MongoDB',
		        'access_token'          => 'mozzler\auth\yii\oauth\storage\MongoDB',
		        'authorization_code'    => 'mozzler\auth\yii\oauth\storage\MongoDB',
		        'client_credentials'    => 'mozzler\auth\yii\oauth\storage\MongoDB',
		        'client'                => 'mozzler\auth\yii\oauth\storage\MongoDB',
		        'refresh_token'         => 'mozzler\auth\yii\oauth\storage\MongoDB',
		        'public_key'            => 'mozzler\auth\yii\oauth\storage\MongoDB',
		        'jwt_bearer'            => 'mozzler\auth\yii\oauth\storage\MongoDB',
		        'scope'                 => 'mozzler\auth\yii\oauth\storage\MongoDB',
		    ],
		    'grantTypes' => [
		        'user_credentials' => [
		            'class' => 'OAuth2\GrantType\UserCredentials',
		        ],
		        'refresh_token' => [
		            'class' => 'OAuth2\GrantType\RefreshToken',
		            'always_issue_new_refresh_token' => true
		        ]
		    ]
		];
		
		return parent::__construct($id, $parent, ArrayHelper::merge($defaultConfig, $config));
	}
	
}