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
        \Yii::error("The afterLogin of the Customer is being called");
        $identity->lastLoggedIn = time();
        $identity->save(true, null, false);
    }

}