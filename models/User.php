<?php
namespace mozzler\auth\models;

use mozzler\base\models\Base;
use yii\helpers\ArrayMerge;

class User extends Base { //implements \yii\web\IdentityInterface {
	
	public $label = "User";
	public $labelPlural = "Users";
	public $defaultRoute = "user";

	public static $moduleClass = '\mozzler\auth\Module';	
	protected static $collectionName = "mozzler.auth.user";
	
	public function getFields() {
		return ArrayMerge::merge(
			[
				'first_name' => '',
				'last_name' => ''
			],
			parent::getFields()
		);
	}
	
	public function attributes() {
		return [
			'_id', 'name'
		];
	}
	
}