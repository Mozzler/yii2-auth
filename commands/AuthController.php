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
        $auth = \Yii::$app->getModule('auth');
        $credentials = $auth->initialCredentials;
        $credentials['username'] = $username ? $username : $credentials['username'];
        $credentials['password'] = $username ? $username : $credentials['password'];

        $userModel = \Yii::createObject($auth->identityClass);

        if ($userModel->findByUsername($username)) {
            $this->stdout("User ($username) already exists, no need to create");
        }
        else {
            $username = $credentials['username'];
            unset($credentials['username']);
            $credentials[$userModel::$usernameField] = $username;

            $userModel->load($credentials,"");
            try
            {
                if (!$userModel->save(true, null, false)) {
                    $this->stdout("Unable to create initial user ($username)", Console::FG_RED."\n");
                    $this->stdout(print_r($userModel->getErrors(),true));
                }
            }
            catch (\yii\mongodb\Exception $e) {
                $code = (int) $e->getCode();
                if ($code == 11000) {
                    $this->stdout("User already exists (".$credentials[$userModel::$usernameField].") - ignoring"."\n", Console::FG_YELLOW);
                }
                else {
                    $this->stdout("Database error: ".$e->getMessage()."\n", Console::FG_RED);
                }
            }
            
        }

        return ExitCode::OK;
    }
    
}
