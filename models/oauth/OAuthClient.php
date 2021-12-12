<?php

namespace mozzler\auth\models\oauth;

use mozzler\base\models\behaviors\AuditLogBehaviour;
use yii\helpers\ArrayHelper;

/**
 * Class OAuthClient
 * @package mozzler\auth\models\oauth
 *
 * @property string $client_id
 * @property string $client_secret
 */
class OAuthClient extends \mozzler\base\models\Model
{

    protected static $collectionName = 'mozzler.auth.clients';
    public $controllerRoute = 'auth/oauthclient';

    public static function rbac()
    {
        return ArrayHelper::merge(parent::rbac(), [
            'registered' => [
                'find' => [
                    'grant' => false
                ],
                'insert' => [
                    'grant' => false
                ],
                'update' => [
                    'grant' => false
                ],
                'delete' => [
                    'grant' => false
                ]
            ]
        ]);
    }

    public function modelIndexes()
    {
        return [
            'clientId' => [
                'columns' => ['client_id' => 1],
            ],
        ];
    }

    public function scenarios()
    {
        return ArrayHelper::merge(parent::scenarios(), [
            self::SCENARIO_CREATE => ['client_id', 'client_secret'],
            self::SCENARIO_UPDATE => ['client_id', 'client_secret'],
            self::SCENARIO_LIST => ['client_id', 'client_secret', 'createdUserId', 'createdAt'],
            self::SCENARIO_VIEW => ['client_id', 'client_secret', 'createdUserId', 'createdAt'],
            self::SCENARIO_SEARCH => ['client_id'],
            self::SCENARIO_EXPORT => ['_id', 'client_id', 'client_secret', 'createdAt', 'createdUserId', 'updatedAt', 'updatedUserId'],
            self::SCENARIO_DEFAULT => array_keys($this->modelFields()),
        ]);
    }

    protected function modelFields()
    {
        return ArrayHelper::merge(parent::modelFields(), [
            'client_id' => [
                'type' => 'Text',
                'label' => 'Client ID',
                'widgets' => [
                    'view' => [
                        'class' => 'mozzler\base\widgets\model\view\CodeField',
                    ]
                ]
            ],
            'client_secret' => [
                'type' => 'Text',
                'label' => 'Secret',
                'widgets' => [
                    'view' => [
                        'class' => 'mozzler\base\widgets\model\view\CodeField',
                    ]
                ]
            ]
        ]);
    }

    protected function modelConfig()
    {
        return [
            'label' => 'OAuth Client',
            'labelPlural' => 'OAuth Clients'
        ];
    }

}