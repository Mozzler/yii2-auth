<?php
namespace mozzler\auth\yii\oauth\auth;

class CompositeAuth extends \filsh\yii2\oauth2server\filters\auth\CompositeAuth
{
	
    /**
     * @inheritdoc
     */
    public function beforeAction($action)
    {
        $server = \Yii::$app->getModule('oauth2')->getServer();
        $server->verifyResourceRequest();
        
        \yii\filters\auth\CompositeAuth::beforeAction($action);
        
        // always return true -- dont' want to stop execution of this action
        // if not logged in -- let rappsio permission system take care of access
        // for not logged in users
        return true;
    }
    
    public function handleFailure($response) {
		// do nothing -- rappsio permission system will kick in using a null identity
    }
}