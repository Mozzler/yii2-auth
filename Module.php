<?php
namespace mozzler\auth;

class Module extends \yii\base\Module
{
	public static $viewPath = '@mozzler/auth/views';
	
    public function init()
    {
        parent::init();
        
        \Yii::configure($this, require __DIR__ . '/config.php');
    }
}
?>