<?php

class SOne_Model_Object_LoginPage extends SOne_Model_Object
{
    public function visualize(K3_Environment $env)
    {
        if ($this->actionState == 'redirect') {
            $env->response->sendRedirect('/');
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
        if ($action == 'login') {
            $users = SOne_Repository_User::getInstance($env->get('db'));
            $user = $users->loadOne(array('login' => $env->request->getString('login', K3_Request::POST, FStr::WORD)));
            if ($user && $user->checkPassword($env->request->getString('password', K3_Request::POST, FStr::LINE))) {
                $env->session->userId = $user->id;
                $users->save($user->updateLastSeen($env));
                $env->put('user', $user);
                $this->pool['actionState'] = 'redirect';
            }
        } elseif ($action == 'logout') {
            $env->session->drop('userId');
            $env->put('user', new SOne_Model_User());
            $this->pool['actionState'] = 'redirect';
        } elseif ($action == 'register') {
            $users    = SOne_Repository_User::getInstance($env->get('db'));
            $login    = $env->request->getString('reg_login', K3_Request::POST, FStr::LINE);
            $password = $env->request->getString('reg_password', K3_Request::POST, FStr::LINE);
            $username = $env->request->getString('reg_name', K3_Request::POST, FStr::LINE);

            $errors = array();
            $nameLen = FStr::strLen($username);
            if ($nameLen < 3 || $nameLen > 16) {
                $errors[] = F()->LNG->lang('SONE_REGISTER_ERROR_NAME_INCORRECT');
            } elseif ($users->loadOne(array('nick' => $username))) {
                $errors[] = F()->LNG->lang('SONE_REGISTER_ERROR_NAME_USED');
            }
            if (!preg_match('#\w{3,16}#', $login)) {
                $errors[] = F()->LNG->lang('SONE_REGISTER_ERROR_LOGIN_INCORRECT');
            } elseif ($users->loadOne(array('login' => $login))) {
                $errors[] = F()->LNG->lang('SONE_REGISTER_ERROR_LOGIN_USED');
            }
            if (FStr::strLen($password) < 8) {
                $errors[] = F()->LNG->lang('SONE_REGISTER_ERROR_PASSWORD_SHORT');
            }

            if (empty($errors)) {
                $user = new SOne_Model_User(array(
                    'login' => $login,
                    'nick'  => $username,
                    'level' => 1,
                    'regtime' => time(),
                ));
                $user->password = $password;
                $users->save($user);
                $env->session->userId = $user->id;
                $env->put('user', $user);
                $this->pool['actionState'] = 'redirect';
            } else {
                $this->pool['errors'] = implode(', <br />', $errors);
                $this->pool['refills'] = array(
                    'reg_login'    => $login,
                    'reg_password' => $password,
                    'reg_name'     => $iusername,
                );
            }
        }
    }

}
