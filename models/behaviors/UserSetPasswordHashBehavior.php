<?php

namespace mozzler\auth\models\behaviors;

use yii\db\BaseActiveRecord;
use yii\behaviors\AttributeBehavior;

class UserSetPasswordHashBehavior extends AttributeBehavior
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->attributes = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'passwordHash',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'passwordHash'
        ];
    }

    /**
     */
    protected function getValue($event)
    {
        $model = $event->sender;
		return \Yii::$app->getSecurity()->generatePasswordHash($model->password);
    }
}
