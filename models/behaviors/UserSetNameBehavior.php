<?php

namespace mozzler\auth\models\behaviors;

use yii\db\BaseActiveRecord;
use yii\behaviors\AttributeBehavior;

class UserSetNameBehavior extends AttributeBehavior
{
    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();
        $this->attributes = [
            BaseActiveRecord::EVENT_BEFORE_INSERT => 'name',
            BaseActiveRecord::EVENT_BEFORE_UPDATE => 'name'
        ];
    }

    /**
     */
    protected function getValue($event)
    {
        $model = $event->sender;
		$name = $model->firstName;
		if ($model->lastName) {
    		$name .= ' '.$model->lastName;
		}
		
		return $name;
    }
}
