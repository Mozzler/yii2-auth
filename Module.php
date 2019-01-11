<?php
namespace mozzler\auth;

use mozzler\base\MozzlerModule;

class Module extends MozzlerModule
{	
	public static $viewPath = '@mozzler/auth/views';
	
	/**
     * Initial credentials for the first admin user in the application
     */
	public $initialCredentials = [
    	'firstName' => 'Initial',
    	'lastName' => 'Admin',
    	'username' => 'admin@yourdomain.com',
    	'password' => 'helloworld',
    	'roles' => ['registered','admin']
	];
}