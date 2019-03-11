<?php
namespace mozzler\auth\commands;

class BeforeInitCredentialsEvent extends \yii\base\Event {

    public $credentials = [];

}

?>