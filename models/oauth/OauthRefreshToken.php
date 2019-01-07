<?php

namespace mozzler\auth\models\oauth;

use Yii;
use mozzler\auth\models\oauth\OAuthClientModel;

/**
 * This is the model class for table "oauth_refresh_tokens".
 *
 * @property string $access_token
 * @property string $client_id
 * @property integer $user_id
 * @property string $expires
 * @property string $scope
 *
 * @property OauthClients $client
 */
class OauthRefreshToken extends \yii\mongodb\ActiveRecord
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
        $clientModel = new OAuthClientModel();

        $client = $clientModel->findOne([
            'refresh_token' => $refreshToken
        ]);

        return is_null($client) ? null : $client['refresh_token'];
    }
}