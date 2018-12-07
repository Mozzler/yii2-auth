<?php
namespace mozzler\auth\models;

use yii\helpers\ArrayHelper;

use mozzler\base\models\Model;
use mozzler\auth\models\behaviors\UserSetNameBehavior;

class User extends Model { //implements \yii\web\IdentityInterface {

	public static $moduleClass = '\mozzler\auth\Module';	
	protected static $collectionName = "mozzler.auth.user";
	
	const SCENARIO_SIGNUP = 'signup';
	const SCENARIO_LOGIN = 'login';
	
	protected function modelConfig()
	{
		return [
			'label' => 'User',
			'labelPlural' => 'Users',
		];
	}
	
	protected function modelFields()
	{
		$fields = parent::modelFields();
		
		$fields['email'] = [
			'type' => 'Email',
			'label' => 'Email',
			'required' => true
		];
		$fields['firstName'] = [
			'type' => 'Text',
			'label' => 'First name',
			'required' => true
		];
		$fields['lastName'] = [
			'type' => 'Text',
			'label' => 'Last name',
			'required' => true
		];
		$fields['password'] = [
			'type' => 'Password',
			'label' => 'Password',
			'required' => true
		];
		
		return $fields;
	}
	
	public function scenarios()
    {
	    $scenarios = parent::scenarios();
//	    unset($scenarios[self::SCENARIO_CREATE]);
	    $scenarios[self::SCENARIO_SIGNUP] = ['firstName', 'lastName', 'email', 'password'];
	    $scenarios[self::SCENARIO_CREATE] = ['firstName', 'lastName', 'email', 'password'];
	    
	    return $scenarios;
    }
    
    public function behaviors() {
	    return ArrayHelper::merge(parent::behaviors(), [
		    [
		    	'class' => UserSetNameBehavior::className()
			]
	    ]);
    }
	
}