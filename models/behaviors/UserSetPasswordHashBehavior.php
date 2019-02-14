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
            BaseActiveRecord::EVENT_BEFORE_VALIDATE => 'passwordHash'
        ];
    }

    /**
     */
    protected function getValue($event)
    {
        $model = $event->sender;

        if ($model->password) {
            return \Yii::$app->getSecurity()->generatePasswordHash($model->password);
        } else {
            return $model->passwordHash;
        }
    }
}
