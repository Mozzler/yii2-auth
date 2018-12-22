<?php

namespace mozzler\auth\models\behaviors;

use yii\db\BaseActiveRecord;
use yii\behaviors\AttributeBehavior;

class UserDeletePasswordBehavior extends AttributeBehavior
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->attributes = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'password',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'password'
        ];
    }

    /**
     */
    protected function getValue($event)
    {
        return null;
    }
}
