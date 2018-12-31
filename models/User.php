<?php
namespace mozzler\auth\models;

use yii\helpers\ArrayHelper;
use Yii;

use mozzler\base\models\Model;
use mozzler\auth\models\behaviors\UserSetNameBehavior;
use mozzler\auth\models\behaviors\UserSetPasswordHashBehavior;

class User extends Model implements \yii\web\IdentityInterface, \OAuth2\Storage\UserCredentialsInterface {

	public static $moduleClass = '\mozzler\auth\Module';	
	protected static $collectionName = "mozzler.auth.user";
	public static $usernameField = 'email';
	
	const SCENARIO_SIGNUP = 'signup';
	const SCENARIO_LOGIN = 'login';
	
	protected function modelConfig()
	{
		return [
			'label' => 'User',
			'labelPlural' => 'Users'
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
			'required' => ['create','signup'],
			'save' => false
		];
		$fields['passwordHash'] = [
			'type' => 'Text',
			'label' => 'Password hash',
			'required' => true
		];
		$fields['roles'] = [
			'type' => 'MultiSelect',
			'label' => 'Roles',
			'options' => \Yii::$app->rbac->getRoleOptions()
		];
		
		return $fields;
	}
	
	/**
	 * Allow registered users to find and update their own records, but not delete or create
	 */
	public static function rbac() {
		return [
			'registered' => [
				'find' => [
					'grant' => [
						'class' => 'mozzler\rbac\policies\IsOwnerModelPolicy',
						'ownerAttribute' => '_id'
					]
				],
				'create' => [
					'grant' => false,
				],
				'update' => [
					'grant' => [
						'class' => 'mozzler\rbac\policies\IsOwnerModelPolicy',
						'ownerAttribute' => '_id'
					]
				],
				'delete' => [
					'grant' => false
				]
			]
		];
	}
	
	public function scenarios()
    {
	    $scenarios = parent::scenarios();
		$scenarios[self::SCENARIO_LIST] = ['name', 'email', 'roles', 'createdAt', 'createdUserId'];
	    $scenarios[self::SCENARIO_SIGNUP] = ['firstName', 'lastName', 'email', 'password'];
	    $scenarios[self::SCENARIO_CREATE] = ['firstName', 'lastName', 'email', 'password'];
	    $scenarios[self::SCENARIO_UPDATE] = ['firstName', 'lastName', 'email', 'password', 'roles'];
	    $scenarios[self::SCENARIO_VIEW] = ['firstName', 'lastName', 'email', 'roles', 'createdAt', 'updatedAt'];
	    $scenarios[self::SCENARIO_LOGIN] = ['email', 'password'];
	    
	    return $scenarios;
    }
    
    public function behaviors() {
	    return ArrayHelper::merge(parent::behaviors(), [
		    'UserSetNameBehavior' => UserSetNameBehavior::className(),
		    'UserSetPasswordHash' => UserSetPasswordHashBehavior::className()
	    ]);
    }
    
    /**
	 * Required for OAuth2 by `hosannahighertech/yii2-oauth2-server`
	 */
    public function checkUserCredentials($username, $password)
    {
	    $user = $this->findByUsername($username);
	    return $this->validatePassword($password);
    }
    
    /**
	 * Required for OAuth2 by `hosannahighertech/yii2-oauth2-server`
	 */
    public function getUserDetails($username)
    {
	    $user = $this->findByUsername($username);
	    $usernameField = $user::$usernameField;

	    return [
		    'user_id' => $user->$usernameField,	// MongoDB ID for the user
		    'id' => $user->id,
		    'scope' => ''		// optional space separated list of scopes
	    ];
    }
    
    public static function findIdentity($id)
    {
	    // Don't check permissions when finding an Identity
	    return self::findOne($id, false);
    }
    
    /**
     * @see yii\web\IdentityInterface
     */
    public static function findByUsername($id)
    {
	    $filter = [];
	    $filter[static::$usernameField] = $id;
        return static::findOne($filter, false);
    }
    
    public static function findIdentityByAccessToken($token, $type = null)
    {
        $retval = null;
		$oauth2 = Yii::$app->getModule('oauth2');

        $oauthServer = $oauth2->getServer();
        $oauthRequest = $oauth2->getRequest();

        $oauthServer->verifyResourceRequest($oauthRequest);

        $token = $oauthServer->getAccessTokenData($oauthRequest);
        
        return self::findByUsername($token['user_id']);
    }
	
	/**
     * Finds user by username.
     *
     * Requires the creation of a `username` field on the model.
     *
     * @param  string      $username	Username to locate the user
     * @return	User	Returns the User model matching the `$username`, otherwise returns null.
     */
    /*public static function findByUsername($username)
    {
        return static::findOne(['username' => $username], false);
    }*/
    
    /**
     * Finds a user by password reset token.
     *
     * @param  string		$token	Password reset token
     * @return Model		Returns `null` if the user was not found or their token is expired
     */
    /*public static function findByPasswordResetToken($token)
    {
        $expire = Dpi\Base::app()->getConfig('rappsio.auth.passwordResetTokenExpire');
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        if ($timestamp + $expire < time()) {
            // token expired
            return null;
        }

        return static::findOne([
            'password_reset_token' => $token,
            //'status' => self::STATUS_ACTIVE,
        ], false);
    }*/

    /**
     * Add support for Rappsio internals.
     *
     * @see yii\web\IdentityInterface
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * Add support for Rappsio internals.
     *
     * @see yii\web\IdentityInterface
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }
    
    /**
     * Validate a password.
     *
     * @param  string	$password	Password to validate
     * @return boolean 	Returns `true` if the password provided is valid for this user
     */
    public function validatePassword($password)
    {
	    return Yii::$app->getSecurity()->validatePassword($password, $this->passwordHash);
    }
    
    public function username() {
	    $usernameField = static::$usernameField;
	    return $this->$usernameField;
    }
    
}