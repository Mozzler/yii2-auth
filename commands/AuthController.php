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
     * Creates a new 'mozzler.auth.user' with the roles of 'registered' and 'admin' based on the email and password
     * The $username is actually the email address e.g 'user@mozzler.com.au'
     *
     * @return int Exit code
     */
    public function actionInitCredentials($username = null, $password = null)
    {

        $auth = \Yii::$app->getModule('auth');
        $credentials = $auth->initialCredentials;
        $credentials['username'] = $username ? $username : $credentials['username'];
        $credentials['password'] = $password ? $password : $credentials['password'];

        $userModel = \Yii::createObject($auth->identityClass);

        if ($userModel->findByUsername($username)) {
            $this->stdout("User ($username) already exists, no need to create");
        } else {
            $username = $credentials['username'];
            unset($credentials['username']);
            $credentials[$userModel::$usernameField] = $username;

            $userModel->load($credentials, "");
            try {
                if (!$userModel->save(true, null, false)) {
                    $this->stdout("Unable to create initial user ($username)", Console::FG_RED . "\n");
                    $this->stdout("Model save errors:\n" . print_r($userModel->getErrors(), true));
                }
            } catch (\yii\mongodb\Exception $e) {
                $code = (int)$e->getCode();
                if ($code == 11000) {
                    $this->stdout("User already exists (" . $credentials[$userModel::$usernameField] . ") - ignoring" . "\n", Console::FG_YELLOW);
                } else {
                    $this->stdout("Database error: " . $e->getMessage() . "\n", Console::FG_RED);
                }
            }

        }

        return ExitCode::OK;
    }

    /**
     * For a given password this generates a passwordHash
     *
     * Useful if you can't seem to login and want to manually edit the database
     * @return int Exit code
     */
    public function actionGeneratePasswordHash($password = null)
    {

        $this->stdout("password: {$password}\n");
        $this->stdout("passwordHash: " . \Yii::$app->getSecurity()->generatePasswordHash($password) . "\n");
        return ExitCode::OK;
    }

    /**
     * For a given password this generates a passwordHash
     *
     * Useful if you can't seem to login and want to manually edit the database
     * @return int Exit code
     */
    public function actionVerifyPassword($password = null, $passwordHash = null)
    {

        $this->stdout("password: {$password}\n");
        $this->stdout("passwordHash: {$passwordHash}\n");
        $this->stdout("Valid Password: " . var_export(\Yii::$app->getSecurity()->validatePassword($password, $passwordHash), true) . "\n");
        return ExitCode::OK;
    }


}
