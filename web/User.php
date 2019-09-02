<?php

namespace mozzler\auth\web;

/**
 * Class User
 * @package mozzler\auth\web
 */
class User extends \yii\web\User
{

    public function afterLogin($identity, $cookieBased, $duration)
    {
        parent::afterLogin($identity, $cookieBased, $duration);
        \Yii::debug("The afterLogin of the User {$identity->getId()} is being called and the lastLoggedIn is being set to now: " . time());
        $identity->lastLoggedIn = time();
        $identity->save(true, null, false);
    }

}