<?php

namespace mozzler\auth\models;

use mozzler\base\helpers\FieldHelper;
use yii\helpers\ArrayHelper;
use Yii;

use mozzler\base\models\Model;
use mozzler\auth\models\behaviors\UserSetNameBehavior;
use mozzler\auth\models\behaviors\UserSetPasswordHashBehavior;
use mozzler\auth\models\oauth\OauthAccessToken;
use yii\helpers\ReplaceArrayValue;
use yii\web\UserEvent;

/**
 * Class User
 * @package mozzler\auth\models
 *
 * @property string $email
 * @property string $firstName
 * @property string $lastName
 * @property string $password
 * @property string $passwordHash
 * @property string $status
 * @property string $passwordResetToken
 * @property string $lastLoggedIn
 * @property array $roles
 */
class User extends Model implements \yii\web\IdentityInterface, \OAuth2\Storage\UserCredentialsInterface
{

    public static $moduleClass = '\mozzler\auth\Module';
    protected static $collectionName = "mozzler.auth.user";
    public static $usernameField = 'email';

    // Events are from \yii\web\User
    const EVENT_BEFORE_LOGIN = 'beforeLogin';
    const EVENT_AFTER_LOGIN = 'afterLogin';

    const SCENARIO_SIGNUP = 'signup';
    const SCENARIO_LOGIN = 'login';
    const SCENARIO_REQUEST_PASSWORD_RESET = 'requestPasswordReset';
    const SCENARIO_PASSWORD_RESET = 'passwordReset';

    const STATUS_ACTIVE = 'active';
    const STATUS_ARCHIVED = 'archived';
    const STATUS_PENDING = 'pending';

    public function init()
    {
        parent::init();
        $this->on(self::EVENT_AFTER_LOGIN, [$this, 'handleUpdateLastLoggedIn']); // Add the last logged in time if we can
    }

    protected function modelConfig()
    {
        return ArrayHelper::merge(parent::modelConfig(), [
            'label' => 'User',
            'labelPlural' => 'Users'
        ]);
    }

    public function modelIndexes()
    {
        return ArrayHelper::merge(parent::modelIndexes(), [
            'uniqueUsername' => [
                'columns' => [
                    static::$usernameField => 1
                ],
                'options' => [
                    'unique' => true
                ],
                'duplicateMessage' => [ucfirst(static::$usernameField) . ' already exists']
            ],

        ]);
    }

    protected function modelFields()
    {
        $fields = parent::modelFields();

        $fields['email'] = [
            'type' => 'Email',
            'label' => 'Email',
            'required' => true,
            'rules' => [
                // -- Lowercase the email addresses to remove the need for string insensitive searches on login
                'filter' => [
                    'filter' => 'strtolower'
                ]
            ]
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
            'required' => ['create', 'signup', 'login'],
            'save' => false
        ];
        $fields['passwordHash'] = [
            'type' => 'Text',
            'label' => 'Password hash',
            'required' => true,
            'rules' => [
                'string' => [
                    'max' => '100'
                ]
            ]
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

        $validationRoles = new ReplaceArrayValue(array_keys(\Yii::$app->rbac->getRoleOptions(true)));

        $fields['roles'] = [
            'type' => 'MultiSelect',
            'label' => 'Roles',
            'options' => \Yii::$app->rbac->getRoleOptions(false),
            'rules' => [
                'in' => [
                    'range' => $validationRoles,
                ]
            ]
        ];
        $fields['passwordResetToken'] = [
            'type' => 'Text',
            'hidden' => true
        ];

        // authkey is used for web sessions
        $fields['authKey'] = [
            'type' => 'Text',
            'hidden' => true
        ];

        // Automatically set on the EVENT_AFTER_LOGIN https://www.yiiframework.com/doc/api/2.0/yii-web-user
        $fields['lastLoggedIn'] = [
            'type' => 'Timestamp',
            'label' => 'Last logged in'
        ];

        return $fields;
    }

    /**
     * Allow registered users to find and update their own records, but not delete or create
     */
    public static function rbac()
    {
        // NB: This doesn't merge the RBAC from the default Mozzler base model RBAC
        return [
            'admin' => [
                'delete' => [
                    'grant' => false // By default don't allow the deletion of users. Instead it's expected you'd set their status to Archived
                ],
                'find' => [
                    'grant' => true
                ],
                'insert' => [
                    'grant' => true
                ],
                'update' => [
                    'grant' => true
                ],
                'report' => [
                    'grant' => true
                ],
            ],
            // Default logged in users (not admin)
            'registered' => [
                'find' => [
                    // Only see your own account
                    'grant' => [
                        'class' => 'mozzler\rbac\policies\IsOwnerModelPolicy',
                        'ownerAttribute' => '_id'
                    ]
                ],
                'insert' => [
                    'grant' => false,
                ],
                'update' => [
                    // Only update your own account
                    'grant' => [
                        'class' => 'mozzler\rbac\policies\IsOwnerModelPolicy',
                        'ownerAttribute' => '_id'
                    ]
                ],
                'delete' => [
                    'grant' => false
                ],
                'report' => [
                    'grant' => true
                ],
            ],
            // You may need to enable public insert depending on your project's requirements or create a special endpoint which creates one but ignores permissions.
            'public' => [
                'find' => [
                    'grant' => false
                ],
                'insert' => [
                    'grant' => false
                ],
                'update' => [
                    'grant' => false
                ],
                'delete' => [
                    'grant' => false
                ],
                'report' => [
                    'grant' => false
                ],
            ],
        ];
    }


    public function scenarios()
    {
        $adminUpdatePermittedFields = [];

        // check if user is logged in, has user->roles, and if admin
        if ($this->canUpdateAdminFields()) {
            $adminUpdatePermittedFields = ['roles', 'status'];
        }

        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_LIST] = ArrayHelper::merge(['name', 'email'], $adminUpdatePermittedFields, ['createdAt', 'createdUserId', 'lastLoggedIn']);
        $scenarios[self::SCENARIO_SIGNUP] = ['firstName', 'lastName', 'email', 'password'];
        $scenarios[self::SCENARIO_CREATE] = ArrayHelper::merge(['firstName', 'lastName', 'email', 'password'], $adminUpdatePermittedFields);
        $scenarios[self::SCENARIO_UPDATE] = ArrayHelper::merge(['firstName', 'lastName', 'email', 'password'], $adminUpdatePermittedFields);
        $scenarios[self::SCENARIO_VIEW] = ArrayHelper::merge(['firstName', 'lastName', 'email'], $adminUpdatePermittedFields, ['createdAt', 'updatedAt', 'lastLoggedIn']);
        $scenarios[self::SCENARIO_LOGIN] = ['email', 'password'];
        $scenarios[self::SCENARIO_SEARCH] = ArrayHelper::merge(['name', 'email'], $adminUpdatePermittedFields);
        $scenarios[self::SCENARIO_REQUEST_PASSWORD_RESET] = ['email'];
        $scenarios[self::SCENARIO_PASSWORD_RESET] = ['email', 'passwordResetToken', 'password'];
        $scenarios[self::SCENARIO_AUDITABLE] = array_values(array_diff(array_keys($this->modelFields()), ['passwordHash', 'passwordResetToken', 'password', 'authKey', 'updatedAt', 'createdAt', 'createdUserId', 'updatedUserId'])); // Default to all fields except the updated and created auto-generated fields. Note the use of array_values to repack the array after array_diff removes the entries

        // Replace the normal Export scenario with a User specific one that removes the auth related fields
        $scenarios[self::SCENARIO_EXPORT] = array_keys(array_filter($this->getCachedModelFields(), function ($modelField, $modelKey) {
            if (in_array($modelKey, ['id', 'passwordHash', 'password', 'passwordResetToken', 'authKey'])) {
                return false; // Only want '_id' not 'id' otherwise it's doubling up, also don't want the password related fields
            }
            // This is used by the CSV export e.g model/export so you don't want to output fields that are relateMany
            return !($modelField['type'] === 'RelateMany');
        }, ARRAY_FILTER_USE_BOTH));

        return $scenarios;
    }

    /**
     * Determine if the current user can update admin fields (roles, status)
     */
    protected function canUpdateAdminFields()
    {
        $user = null;
        if (!\Yii::$app->has('user')) {
            return false;
        }

        $user = \Yii::$app->user->getIdentity();

        // check if user is logged in, has user->roles, and if admin
        if ($user && \Yii::$app->rbac->canAccessModel($this, 'insert')) {
            return true;
        }

        return false;
    }

    public function behaviors()
    {
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
            'user_id' => $user->$usernameField,    // MongoDB ID for the user
            'id' => $user->id,
            'scope' => ''        // optional space separated list of scopes
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
     * @param string $username Username to locate the user
     * @return    User    Returns the User model matching the `$username`, otherwise returns null.
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
     * @param string $token Password reset token
     * @return Model        Returns `null` if the user was not found or their token is expired
     */
    public static function findByPasswordResetToken($token)
    {
        $config = \Yii::$app->params['mozzler.auth']['user']['passwordReset'];
        $expire = $config['tokenExpiry'];
        $parts = explode('_', $token);
        $timestamp = (int)end($parts);
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
     * @param string $password Password to validate
     * @return boolean    Returns `true` if the password provided is valid for this user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->passwordHash);
    }

    public function username()
    {
        $usernameField = static::$usernameField;
        return $this->$usernameField;
    }


    public static function getCollectionName()
    {
        return self::$collectionName;
    }


    /**
     * Copied from \yii\web\User::beforeLogin
     *
     * This method is called before logging in a user.
     * The default implementation will trigger the [[EVENT_BEFORE_LOGIN]] event.
     * If you override this method, make sure you call the parent implementation
     * so that the event is triggered.
     * @param \yii\web\IdentityInterface $identity the user identity information
     * @param bool $cookieBased whether the login is cookie-based
     * @param int $duration number of seconds that the user can remain in logged-in status.
     * If 0, it means login till the user closes the browser or the session is manually destroyed.
     * @return bool whether the user should continue to be logged in
     */
    public function beforeLogin($cookieBased = false, $duration = 0)
    {

        \Yii::info("Triggering the User Before event");
        $event = new UserEvent([
            'identity' => $this,
            'cookieBased' => $cookieBased,
            'duration' => $duration,
        ]);
        $this->trigger(self::EVENT_BEFORE_LOGIN, $event);

        return $event->isValid;
    }

    /**
     * Copied from \yii\web\User::afterLogin
     *
     * This method is called after the user is successfully logged in.
     * The default implementation will trigger the [[EVENT_AFTER_LOGIN]] event.
     * If you override this method, make sure you call the parent implementation
     * so that the event is triggered.
     * @param \yii\web\IdentityInterface $identity the user identity information
     * @param bool $cookieBased whether the login is cookie-based
     * @param int $duration number of seconds that the user can remain in logged-in status.
     * If 0, it means login till the user closes the browser or the session is manually destroyed.
     */
    public function afterLogin($cookieBased = false, $duration = 0)
    {
        \Yii::info("Triggering the User After Login event");
        $this->trigger(self::EVENT_AFTER_LOGIN, new UserEvent([
            'identity' => $this,
            'cookieBased' => $cookieBased,
            'duration' => $duration,
        ]));
    }

    /**
     * @param $event
     *
     * If you would like the lastLoggedIn field to be saved then you'll need to configure this user model (e.g extending it with your own user model) with an init method
     *
     * e.g
     *  public function init()
     *  {
     *    parent::init();
     *    // Add the event listeners (because doing so in the web controller config doesn't seem to be working)
     *    $this->on(self::EVENT_BEFORE_LOGIN, [$this, 'handleIsArchivedUser']);
     *    $this->on(self::EVENT_AFTER_LOGIN, [$this, 'handleUpdateLastLoggedIn']);
     *  }
     *
     */
    public function handleUpdateLastLoggedIn($event)
    {
        // Expecting a Web User after login event
        try {
            /** @var User $user */
            $user = $event->identity;
            if ($user && $user->hasProperty('lastLoggedIn')) {
                $user->lastLoggedIn = time();
                $user->save(true, null, false);
            }
        } catch (\Throwable $exception) {
            \Yii::error("Error with handleUpdateLastLoggedIn() Unable to save the last logged in time: " . \Yii::$app->t::returnExceptionAsString($exception));
        }
    }


    /**
     * Init Model Fields
     *
     * Don't use the default getCachedModelFields for the User
     * The cached fields causes issues when initialising fields and the current user hasn't yet been instantiated
     *
     * NB: This allows you to manually provide the modelFields so you can adapt the fields as needed, e.g setting some fields to hidden
     *
     * Example Usage:
     *    // -- This is the piece of magic that applies the email hidden change for the displaying in the form
     *    // Note it's only available on user's where we've overridden the method to allow setting it this way
     *    $cachedModelFields = $model->getCachedModelFields();
     *    $cachedModelFields['email']['hidden'] = true;
     *    $model->initModelFields($cachedModelFields); // Re-set the model fields
     */
    public function initModelFields($modelFields = null)
    {
        $this->modelFields = FieldHelper::createFields($this, $modelFields ? $modelFields : $this->modelFields());
    }

    /**
     * @return User|null
     * @throws \Throwable
     *
     * This is a very common call you'll likely use a lot
     *
     * e.g $user = User::getCurrentUser();
     */
    public static function getCurrentUser()
    {
        if (!\Yii::$app->has('user')) {
            // -- Likely a CLI request
            return null;
        }
        /** @var User $user */
        $user = \Yii::$app->user->getIdentity();
        if (empty($user)) {
            return null;
        }
        return $user;
    }

}
