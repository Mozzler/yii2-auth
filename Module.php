<?php
namespace mozzler\auth;

use mozzler\base\MozzlerModule;

class Module extends MozzlerModule
{	
	public static $viewPath = '@mozzler/auth/views';
	
	public $identityClass = 'app\models\User';
	
	/**
     * Initial credentials for the first admin user in the application
     */
	public $initialCredentials = [
    	'firstName' => 'Initial',
    	'lastName' => 'Admin',
    	'username' => 'admin@yourdomain.com',
		'password' => 'helloworld',
		'status' => 'active',
    	'roles' => ['registered','admin']
	];
}