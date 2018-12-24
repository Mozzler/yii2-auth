<?php
namespace mozzler\auth\models\oauth;
use Yii;
use mozzler\auth\models\oauth\OAuthClientModel;

/**
 * This is the model class for table "oauth_access_tokens".
 *
 * @property string $access_token
 * @property string $client_id
 * @property integer $user_id
 * @property string $expires
 * @property string $scope
 *
 * @property OauthClients $client
 */
class OauthAccessToken extends \yii\mongodb\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth_access_tokens}}';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['access_token', 'client_id', 'expires'], 'required'],
            [['user_id'], 'integer'],
            [['expires'], 'safe'],
            [['access_token'], 'string', 'max' => 40],
            [['client_id'], 'string', 'max' => 32],
            [['scope'], 'string', 'max' => 2000]
        ];
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'access_token' => 'Access Token',
            'client_id' => 'Client ID',
            'user_id' => 'User ID',
            'expires' => 'Expires',
            'scope' => 'Scope',
        ];
    }
    
    public function attributes() {
	    return [
		    'access_token',
		    'user_id',
		    'expires',
		    'scope',
		    'client_id'
	    ];
    }
    
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getClient()
    {
        return $this->hasOne(OauthClients::className(), ['client_id' => 'client_id']);
    }
    
    /**
	 * Find the UserID for a given access token
	 */
    public function findUserId($accessToken) {
	    \Yii::trace('finduser',__METHOD__);
	    $clientModel = new OAuthClientModel();
	    /*$oauth = \Yii::$app->getModule("oauth2");
		$tableName = 'mozzler.auth.clients'; //$oauth->storageConfig['access_token_table'];
	    
	    $db = $this->db;
	    $collection = $db->getCollection($tableName);
	    //$collection->checkPermissions = false;
	    $result = $collection->findOne([
		    'access_token' => $accessToken
	    ]);
	    
	    return $result['user_id'];*/
	    $client = $clientModel->findOne([
		    'access_token' => $accessToken
	    ]);
	    
	    return is_null($client) ? null : $client['access_token'];
    }
}