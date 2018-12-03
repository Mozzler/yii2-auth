<?php
namespace mozzler\auth\controllers;

use mozzler\base\controllers\ActiveController;

use yii\helpers\ArrayHelper;

class UserApiController extends ActiveController {

	public $modelClass = 'mozzler\auth\models\User';
	
}
