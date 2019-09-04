<?php

namespace mozzler\auth\models\behaviors;

use yii\db\BaseActiveRecord;
use yii\behaviors\AttributeBehavior;
use yii\helpers\ArrayHelper;

/**
 * Class UserSetDefaultRolesBehavior
 * @package mozzler\auth\models\behaviors
 *
 * This is useful for setting default roles. e.g 'api-user'
 *
 * Example usage:
 *
 * app/user.php:
 *
 * public function behaviors()
 * {
 * return [
 * 'userAddDefaultACPRole' => [
 * 'class' => UserSetDefaultRolesBehavior::className(),
 * 'defaultRoles' => ['acp-user'],
 * ],
 * ];
 * }
 */
class UserSetDefaultRolesBehavior extends AttributeBehavior
{

    // You should have 'registered' already if the user is
    public $defaultRoles = []; // The roles to add.

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->attributes = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'roles',
        ];
    }

    protected function getValue($event)
    {
        $model = $event->sender;
        $roles = array_unique(ArrayHelper::merge($model->roles, $this->defaultRoles)); // Add in the default roles (but prevent any roles being defined multiple times)

        return $roles;
    }
}
