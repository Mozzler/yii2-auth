<?php

namespace mozzler\auth\models;

use Yii;

use mozzler\base\models\Model;

/**
 * Class Session
 * @package mozzler\auth\models
 *
 * @property string $id
 * @property string $data A specially formatted serialised string
 * @property string $expire Unix timestamp of when the entry expires
 *
 * NB: RBAC for this collection is ignored by default
 */
class Session extends Model
{

    protected static $collectionName = "app.session"; // Note in the getCollectionName method we set this to what the \Yii::$app->session->sessionCollection entry is, if defined

    public static function getCollectionName()
    {
        return \Yii::$app->session->sessionCollection ?? self::$collectionName;
    }

    /**
     * @return array
     *
     * The whole reason for this class is to set this model index
     * and for the `./yii deploy/sync` controller to add this
     */
    public function modelIndexes()
    {
        return [
            'idExpire' => ['columns' => [
                'id' => 1,
                'expire' => -1
            ]],
            'expire' => ['columns' => [
                'expire' => -1 // I think the garbage collector uses this, it was suggested by the MongoDB Performance Advisor
            ]],
        ];
    }

    protected function modelConfig()
    {
        return [
            'label' => 'Web Session',
            'labelPlural' => 'Web Sessions',
            'searchAttribute' => 'id'
        ];
    }


    protected function modelFields()
    {
        return [
            'id' => [
                'type' => 'Text',
                'label' => 'Session ID',
            ],
            'data' => [
                'type' => 'Text',
                'label' => 'Data',
            ],
            'expire' => [
                'type' => 'DateTime',
                'label' => 'Expiry',
            ],

        ];
    }

}
