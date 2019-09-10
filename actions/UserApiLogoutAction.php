<?php

namespace mozzler\auth\actions;

use mozzler\auth\models\oauth\OauthAccessToken;
use mozzler\auth\models\oauth\OauthRefreshToken;
use yii\base\Exception;
use mozzler\base\actions\BaseModelAction;

/**
 * Class UserApiLogoutAction
 * @package mozzler\auth\actions
 *
 * Logs users out and also deletes all their OAuth Access Tokens.
 * Designed for API calls e.g For a mobile app
 *
 * You might also want to remove the OAuth Refresh Tokens (currently not enabled by default)
 */
class UserApiLogoutAction extends BaseModelAction
{

    public $id = 'userLogout';
    public $resultScenario = \mozzler\base\models\Model::SCENARIO_VIEW_API;

    public $removeOauthAccessToken = true;
    public $removeOauthRefreshToken = false; // You can extend the action and change this locally if you want, or configure it

    /**
     * @return array
     */
    public function run()
    {
        \Yii::debug("Logging the customer out");
        $customer = \Yii::$app->user->getIdentity();

        $deleteAllQuery = ['user_id' => $customer->{$customer::$usernameField}];

        // NB: Don't want to accidentally remove ALL the Access Tokens if there's some weird issue with the query
        if (true === $this->removeOauthAccessToken && !empty($deleteAllQuery)) {
            $deletedOAuthAccessTokens = OauthAccessToken::deleteAll($deleteAllQuery);
            \Yii::info("Removed all {$deletedOAuthAccessTokens} of the User's Access tokens using the query: " . json_encode($deleteAllQuery));
        }

        // -- Remove the Refresh Tokens (disabled by default)
        if (true === $this->removeOauthRefreshToken && !empty($deleteAllQuery)) {
            $deletedOAuthAccessTokens = OauthRefreshToken::deleteAll($deleteAllQuery);
            \Yii::info("Removed all {$deletedOAuthAccessTokens} of the Users's Refresh tokens using the query: " . json_encode($deleteAllQuery));
        }
        $logout = \Yii::$app->user->logout(true); // Log the user out the Yii2 way (mostly for sessions / cookies)
        return ['loggedOut' => $logout]; // Mobile apps want some valid JSON not just a bool
    }
}