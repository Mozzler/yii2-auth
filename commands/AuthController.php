<?php
/**
 */

namespace mozzler\auth\commands;

use Yii;
use yii\console\Controller;
use yii\helpers\Console;
use yii\console\ExitCode;
use yii\helpers\ArrayHelper;


/**
 * This command assists managing the auth database collections
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AuthController extends Controller
{
    /**
     * This command syncs all the indexes found in the application models.
     *
     * - New indexes are created
     * - Existing indexes are updated if they're different
     * - Deleted indexes are removed
     *
     * Everything is auto-detected based on the current indexes in the collection
     *
     * @return int Exit code
     */
    public function actionInitCredentials($username=null, $password=null)
    {
        $credentials = ArrayHelper::merge(\Yii::$app->getModule('auth')->initialCredentials, [
            'username' => $username,
            'password' => $password
        ]);
        
        
        $userModel = \Yii::createObject(\Yii::$app->user->identityClass);

        if ($userModel->findByUsername($username)) {
            $this->stdout("User ($username) already exists, no need to create");
        }
        else {
            $username = $credentials['username'];
            unset($credentials['username']);
            $credentials[$userModel::$usernameField] = $username;
            
            $userModel->load($credentials,"");
            if (!$userModel->save()) {
                $this->stdout("Unable to create initial user ($username)", Console::FG_RED);
                $this->stdout(print_r($userModel->getErrors(),true));
            }
        }
        
        
        
        // find all the models
        $models = [
            'app\models\Device'
        ];
        
        $indexManager = \Yii::createObject('mozzler\base\components\IndexManager');
        
        foreach ($models as $className) {
            $this->stdout('Processing model: '.$className."\n", Console::FG_GREEN);
            
            $indexManager->syncModelIndexes($className);
            $this->outputLogs($indexManager->logs);
        }

        return ExitCode::OK;
    }
    
}
