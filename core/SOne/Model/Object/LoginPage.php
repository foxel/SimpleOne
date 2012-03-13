<?php

/**
 * @property array $refills
 * @property array $errors
 */
class SOne_Model_Object_LoginPage extends SOne_Model_Object
{
    public function visualize(K3_Environment $env)
    {
        if ($this->actionState == 'redirect') {
            $env->response->sendRedirect($this->path);
        }
        $node = new FVISNode('SONE_OBJECT_LOGINPAGE', 0, $env->get('VIS'));
        $user = $env->get('user');
        if ($user->id) {
            $node->addData('logged_as', $user->name);
        }
        $node->addDataArray($this->pool + (array) $this->refills);
        return $node;
    }

    public function doAction($action, K3_Environment $env, &$updated = false)
    {
        parent::doAction($action, $env, $updated);

        /* @var SOne_Application $app */
        $app = $env->get('app');
        /* @var SOne_Repository_User $users */
        $users = SOne_Repository_User::getInstance($env->get('db'));

        if ($action == 'login') {
            $user = $users->loadOne(array('login' => $env->request->getString('login', K3_Request::POST, FStr::WORD)));
            if ($user && $user->checkPassword($env->request->getString('password', K3_Request::POST, FStr::LINE))) {
                $app->setAuthUser($user);
            }
        } elseif ($action == 'logout') {
            $app->dropAuthUser();
            $this->pool['actionState'] = 'redirect';
        } elseif ($action == 'register') {
            $login    = $env->request->getString('reg_login', K3_Request::POST, FStr::LINE);
            $password = $env->request->getString('reg_password', K3_Request::POST, FStr::LINE);
            $username = $env->request->getString('reg_name', K3_Request::POST, FStr::LINE);
            $email    = $env->request->getString('reg_email', K3_Request::POST, FStr::LINE);

            /* @var FLNGData $lang */
            $lang = $env->get('lang');

            $errors = array();
            $nameLen = FStr::strLen($username);
            if ($nameLen < 3 || $nameLen > 16) {
                $errors[] = $lang->lang('SONE_REGISTER_ERROR_NAME_INCORRECT');
            } elseif ($users->loadOne(array('nick' => $username))) {
                $errors[] = $lang->lang('SONE_REGISTER_ERROR_NAME_USED');
            }
            if (!preg_match('#\w{3,16}#', $login)) {
                $errors[] = $lang->lang('SONE_REGISTER_ERROR_LOGIN_INCORRECT');
            } elseif ($users->loadOne(array('login' => $login))) {
                $errors[] = $lang->lang('SONE_REGISTER_ERROR_LOGIN_USED');
            }
            if (FStr::strLen($password) < 8) {
                $errors[] = $lang->lang('SONE_REGISTER_ERROR_PASSWORD_SHORT');
            }
            if (!FStr::isEmail($email, true)) {
                $errors[] = $lang->lang('SONE_REGISTER_ERROR_EMAIL_INCORRECT');
            }

            if (empty($errors)) {
                $user = new SOne_Model_User(array(
                    'login'        => $login,
                    'name'         => $username,
                    'email'        => $email,
                    'accessLevel'  => 1,
                    'registerTime' => time(),
                ));
                $user->password = $password;
                $users->save($user);
                $app->setAuthUser($user);
                $this->pool['actionState'] = 'redirect';
            } else {
                $this->pool['errors'] = '<ul><li>'.implode('</li><li>', $errors).'</li></ul>';
                $this->pool['refills'] = array(
                    'reg_login'    => $login,
                    'reg_password' => $password,
                    'reg_name'     => $username,
                    'reg_email'    => $email,
                );
            }
        }
    }


}
