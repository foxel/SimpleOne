<?php
/**
 * Copyright (C) 2013 Andrey F. Kupreychik (Foxel)
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
 * @property array $refills
 * @property array $errors
 */
class SOne_Model_Object_System_Profile extends SOne_Model_Object
{

    /**
     * @param  SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        if ($this->actionState == 'redirect') {
            $env->response->sendRedirect($this->path);
        }

        $node = new FVISNode('SONE_OBJECT_SYSTEM_PROFILE', 0, $env->getVIS());
        $user = $env->getUser();
        $node
            ->addDataArray($this->pool)
            ->addDataArray((array)$this->refills + $user->toArray(), 'user_');

        if ($env->session->get('dataSaved'.FStr::shortHash($this->path))) {
            $env->session->drop('dataSaved'.FStr::shortHash($this->path));
            $node->addData('dataSaved', 1);
        }
        return $node;
    }

    protected function updateAction(SOne_Environment $env, &$objectUpdated = false)
    {
        /* @var SOne_Repository_User $users */
        $users = SOne_Repository_User::getInstance($env->getDb());
        $lang = $env->getLang();
        $user = $env->getUser();

        $password = $env->request->getString('new_password1', K3_Request::POST, FStr::LINE);
        $password2 = $env->request->getString('new_password2', K3_Request::POST, FStr::LINE);
        $oldPassword = $env->request->getString('old_password', K3_Request::POST, FStr::LINE);
        $username = $env->request->getString('user_name', K3_Request::POST, FStr::LINE);

        $errors = array();
        $nameLen = FStr::strLen($username);
        if ($nameLen < 3 || $nameLen > 16) {
            $errors[] = $lang->lang('SONE_REGISTER_ERROR_NAME_INCORRECT');
        } elseif ($users->loadOne(array('name' => $username, 'id!=' => $user->id))) {
            $errors[] = $lang->lang('SONE_REGISTER_ERROR_NAME_USED');
        }

        if ($password) {
            if (!$user->checkPassword($oldPassword)) {
                $errors[] = $lang->lang('SONE_PROFILE_ERROR_PASSWORD_INCORRECT');
            }
            if (FStr::strLen($password) < 8) {
                $errors[] = $lang->lang('SONE_REGISTER_ERROR_PASSWORD_SHORT');
            }
            if ($password2 != $password) {
                $errors[] = $lang->lang('SONE_REGISTER_ERROR_PASSWORDS_DIFFER');
            }
        }

        if (empty($errors)) {
            $user->name = $username;
            if ($password) {
                $user->password = $password;
            }

            $users->save($user);
            $this->pool['actionState'] = 'redirect';
            $env->session->set('dataSaved'.FStr::shortHash($this->path), true);
        } else {
            $this->pool['errors'] = '<ul><li>'.implode('</li><li>', $errors).'</li></ul>';
            $this->pool['refills'] = array(
                'name' => $username,
            );
        }
    }
}
