<?php
/**
 * Copyright (C) 2012 - 2013 Andrey F. Kupreychik (Foxel)
 *
 * This file is part of QuickFox SimpleOne.
 *
 * SimpleOne is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * SimpleOne is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with SimpleOne. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * @property bool  $registerAllowed
 *
 * @property array $refills
 * @property array $errors
 */
class SOne_Model_Object_LoginPage extends SOne_Model_Object
    implements SOne_Interface_Object_Structured
{
    /**
     * @param SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        if ($this->actionState == 'redirect') {
            $env->response->sendRedirect($this->path);
        }
        $node = new FVISNode('SONE_OBJECT_LOGINPAGE', 0, $env->getVIS());
        $user = $env->getUser();
        if ($user->id) {
            $node->addData('logged_as', $user->name);
        }
        $node->addDataArray($this->pool + (array) $this->refills);
        return $node;
    }

    /**
     * @param string $action
     * @param SOne_Model_User $user
     * @return bool
     */
    public function isActionAllowed($action, SOne_Model_User $user)
    {
        return ($action == 'register')
            ? $this->registerAllowed
            : parent::isActionAllowed($action, $user);
    }

    /**
     * @param string $action
     * @param SOne_Environment $env
     * @param bool $updated
     */
    public function doAction($action, SOne_Environment $env, &$updated = false)
    {
        parent::doAction($action, $env, $updated);

        /* @var SOne_Application $app */
        $app = $env->getApp();
        /* @var SOne_Repository_User $users */
        $users = SOne_Repository_User::getInstance($env->getDb());

        /* @var FLNGData $lang */
        $lang = $env->getLang();

        if ($action == 'login') {
            $user = $users->loadOne(array('login' => $env->request->getString('login', K3_Request::POST, FStr::WORD)));
            if ($user && $user->checkPassword($env->request->getString('password', K3_Request::POST, FStr::LINE))) {
                $app->setAuthUser($user, $env->request->getBinary('set-auto-login', K3_Request::POST));
            } else {
                $this->pool['errors'] = '<ul><li>'.$lang->lang('SONE_LOGIN_ERROR_LOGIN_INCORRECT').'</li></ul>';
            }
        } elseif ($action == 'logout') {
            $app->dropAuthUser();
            $this->pool['actionState'] = 'redirect';
        } elseif ($action == 'register') {
            $login    = $env->request->getString('reg_login', K3_Request::POST, FStr::LINE);
            $password = $env->request->getString('reg_password', K3_Request::POST, FStr::LINE);
            $username = $env->request->getString('reg_name', K3_Request::POST, FStr::LINE);
            $email    = $env->request->getString('reg_email', K3_Request::POST, FStr::LINE);

            $errors = array();
            $nameLen = FStr::strLen($username);
            if ($nameLen < 3 || $nameLen > 16) {
                $errors[] = $lang->lang('SONE_REGISTER_ERROR_NAME_INCORRECT');
            } elseif ($users->loadOne(array('name' => $username))) {
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

    /**
     * @param array $data
     * @return SOne_Model_Object_HTMLPage
     */
    public function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'registerAllowed' => false,
        );
        $this->pool['registerAllowed'] =& $this->pool['data']['registerAllowed'];
        return $this;
    }
}
