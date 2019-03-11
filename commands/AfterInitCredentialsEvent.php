<?php
namespace mozzler\auth\commands;

class AfterInitCredentialsEvent extends \yii\base\Event {

    public $created;

    public $existed;

}

?>