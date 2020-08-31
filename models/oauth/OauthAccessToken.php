<?php

namespace mozzler\auth\models\oauth;

use Yii;
use mozzler\auth\models\oauth\OAuthClient;
use mozzler\base\models\Model as Model;

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
class OauthAccessToken extends Model
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'mozzler.auth.access_tokens';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['access_token', 'client_id', 'expires'], 'required'],
            [['user_id'], 'string'],
            [['expires'], 'safe'],
            [['access_token'], 'string', 'max' => 40],
            [['client_id'], 'string', 'max' => 32],
            [['scope'], 'string', 'max' => 2000]
        ];
    }

    /**
     * @return array
     *
     * These are defined for the preload-data (DataController) to be able to create Access Tokens easily
     */
    protected function modelFields()
    {
        return [
            '_id' => [
                'type' => 'MongoId',
                'label' => 'ID'
            ],
            'access_token' => [
                'type' => 'Text',
                'label' => 'Access Token',
            ],
            'client_id' => [
                'type' => 'Text',
                'label' => 'Client Id',
            ],
            'expires' => [
                'type' => 'Timestamp', // The OAuth library doesn't allow this to be null
                'label' => 'Expires',
            ],
            'user_id' => [
                'type' => 'Text',
                'label' => 'User Id',
            ],
            'scope' => [
                'type' => 'Raw', // Allows for null
                'label' => 'Scope',
            ]
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
    public function findUserId($accessToken)
    {
        $clientModel = new OAuthClient();

        $client = $clientModel->findOne([
            'access_token' => $accessToken
        ]);

        return is_null($client) ? null : $client['access_token'];
    }

    public function behaviors()
    {
        return [];
    }
}