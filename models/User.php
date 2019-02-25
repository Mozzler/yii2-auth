<?php
namespace mozzler\auth\models;

use yii\helpers\ArrayHelper;
use Yii;

use mozzler\base\models\Model;
use mozzler\auth\models\behaviors\UserSetNameBehavior;
use mozzler\auth\models\behaviors\UserSetPasswordHashBehavior;
use mozzler\auth\models\oauth\OauthAccessToken;

class User extends Model implements \yii\web\IdentityInterface, \OAuth2\Storage\UserCredentialsInterface {

	public static $moduleClass = '\mozzler\auth\Module';	
	protected static $collectionName = "mozzler.auth.user";
	public static $usernameField = 'email';
	
	const SCENARIO_SIGNUP = 'signup';
	const SCENARIO_LOGIN = 'login';
	const SCENARIO_REQUEST_PASSWORD_RESET = 'requestPasswordReset';
	const SCENARIO_PASSWORD_RESET = 'passwordReset';

	const STATUS_ACTIVE = 'active';
	const STATUS_ARCHIVED = 'archived';
	const STATUS_PENDING = 'pending';
	
	protected function modelConfig()
	{
		return ArrayHelper::merge(parent::modelConfig(), [
			'label' => 'User',
			'labelPlural' => 'Users'
		]);
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
			'required' => ['create','signup','login'],
			'save' => false
		];
		$fields['passwordHash'] = [
			'type' => 'Text',
			'label' => 'Password hash',
			'required' => true
		];
		$fields['status'] = [
			'type' => 'SingleSelect',
			'label' => 'Status',
			'default' => self::STATUS_ACTIVE,
			'options' => [
				'active' => 'Active', 
				'archived' => 'Archived',
				'pending' => 'Pending'
			],
			'required' => true
		];
		$fields['roles'] = [
			'type' => 'MultiSelect',
			'label' => 'Roles',
			'options' => \Yii::$app->rbac->getRoleOptions()
		];
		$fields['passwordResetToken'] = [
			'type' => 'Text',
			'hidden' => true
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
		$user = null;
		if (\Yii::$app->has('user')) {
			$user = \Yii::$app->user->getIdentity();
		}

        $adminUpdatePermittedFields = [];

        // check if user is logged in, has user->roles, and if admin
        if ($user && $user->roles && ArrayHelper::isIn('admin', $user->roles)) {
			$adminUpdatePermittedFields = ['roles', 'status'];
		}

		$scenarios = parent::scenarios();
		$scenarios[self::SCENARIO_LIST] 	= ArrayHelper::merge(['name', 'email'], $adminUpdatePermittedFields, ['createdAt', 'createdUserId']);
	    $scenarios[self::SCENARIO_SIGNUP] 	= ['firstName', 'lastName', 'email', 'password'];
	    $scenarios[self::SCENARIO_CREATE] 	= ArrayHelper::merge(['firstName', 'lastName', 'email', 'password'], $adminUpdatePermittedFields);
	    $scenarios[self::SCENARIO_UPDATE] 	= ArrayHelper::merge(['firstName', 'lastName', 'email', 'password'], $adminUpdatePermittedFields);
		$scenarios[self::SCENARIO_VIEW] 	= ArrayHelper::merge(['firstName', 'lastName', 'email', 'password'], $adminUpdatePermittedFields, ['createdAt', 'updatedAt']);
	    $scenarios[self::SCENARIO_LOGIN] 	= ['email', 'password'];
	    $scenarios[self::SCENARIO_SEARCH] 	= ArrayHelper::merge(['name', 'email'], $adminUpdatePermittedFields);
        $scenarios[self::SCENARIO_REQUEST_PASSWORD_RESET] = ['email'];
        $scenarios[self::SCENARIO_PASSWORD_RESET] = ['email', 'passwordResetToken', 'password'];
	    
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
     * Finds user by username.
     *
     * Requires the creation of a `username` field on the model.
     *
     * @param  string      $username	Username to locate the user
     * @return	User	Returns the User model matching the `$username`, otherwise returns null.
     */
    public static function findByUsername($id)
    {
	    $filter = [];
	    $filter[static::$usernameField] = $id;
        return static::findOne($filter, false);
    }
    
    public static function findIdentityByAccessToken($token, $type = null)
    {
	    $OAuth = \Yii::createObject(OauthAccessToken::className());
	    $token = $OAuth::findOne([
		    'access_token' => $token
	    ]);
	    
	    if ($token) {
        	return self::findByUsername($token['user_id']);
        }
    }
    
    public function generatePasswordResetToken()
    {
        $this->passwordResetToken = urlencode(utf8_encode(Yii::$app->getSecurity()->generateRandomKey() . '_' . time()));
    }
	
    /**
     * Finds a user by password reset token.
     *
     * @param  string		$token	Password reset token
     * @return Model		Returns `null` if the user was not found or their token is expired
     */
    public static function findByPasswordResetToken($token)
    {
		$config = \Yii::$app->params['mozzler.auth']['user']['passwordReset'];
        $expire = $config['tokenExpiry'];
        $parts = explode('_', $token);
        $timestamp = (int) end($parts);
        if ($timestamp + $expire < time()) {
            // token expired
            return null;
        }

        return static::findOne([
            'passwordResetToken' => $token,
            'status' => self::STATUS_ACTIVE,
        ], false);
    }

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