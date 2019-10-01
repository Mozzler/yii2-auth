<?php

namespace mozzler\auth\models\oauth;

use Yii;
use mozzler\auth\models\oauth\OAuthClient;
use mozzler\base\models\Model as Model;

/**
 * This is the model class for table "oauth_refresh_tokens".
 *
 * @property string $refresh_token
 * @property string $client_id
 * @property integer $user_id
 * @property string $expires
 * @property string $scope
 *
 * @property OauthClients $client
 */
class OauthRefreshToken extends Model
{
    /**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'mozzler.auth.refresh_tokens';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['refresh_token', 'client_id', 'expires'], 'required'],
            [['user_id'], 'integer'],
            [['expires'], 'safe'],
            [['refresh_token'], 'string', 'max' => 40],
            [['client_id'], 'string', 'max' => 32],
            [['scope'], 'string', 'max' => 2000]
        ];
    }

    public function behaviors()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'refresh_token' => 'Refresh Token',
            'client_id' => 'Client ID',
            'user_id' => 'User ID',
            'expires' => 'Expires',
            'scope' => 'Scope',
        ];
    }

    public function attributes()
    {
        return [
            'refresh_token',
            'user_id',
            'expires',
            'scope',
            'client_id',
            '_id',
        ];
    }

    /**
     * @return array
     *
     * These are defined for the preload-data (DataController) to be able to create Refresh Tokens easily
     */
    protected function modelFields()
    {
        return [
            '_id' => [
                'type' => 'MongoId',
                'label' => 'ID'
            ],
            'refresh_token' => [
                'type' => 'Text',
                'label' => 'Access Token',
            ],
            'scope' => [
                'type' => 'Text',
                'label' => 'Scope',
            ],
            'client_id' => [
                'type' => 'Text',
                'label' => 'Client Id',
            ],
            'expires' => [
                'type' => 'Timestamp',
                'label' => 'Expires',
            ],
            'user_id' => [
                'type' => 'Integer',
                'label' => 'User Id',
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
    public function findUserId($refreshToken)
    {
        $clientModel = new OAuthClient();

        $client = $clientModel->findOne([
            'refresh_token' => $refreshToken
        ]);

        return is_null($client) ? null : $client['refresh_token'];
    }
}