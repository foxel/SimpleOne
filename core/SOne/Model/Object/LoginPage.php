<?php

class SOne_Model_Object_LoginPage extends SOne_Model_Object
{
    public function visualize(K3_Environment $env)
    {
        if ($this->actionState) {
            $env->response->sendRedirect('/');
        }
        $node = new FVISNode('SONE_OBJECT_LOGINPAGE', 0, $env->get('VIS'));
        $node->addDataArray($this->pool);
        return $node;
    }

    public function doAction($action, K3_Environment $env, &$updated = false)
    {
        if ($action == 'login') {
            $users = new SOne_Repository_User($env->get('db'));
            $user = $users->loadOne(array('login' => $env->request->getString('login', K3_Request::POST, FStr::WORD)));
            if ($user && $user->checkPassword($env->request->getString('password', K3_Request::POST, FStr::LINE))) {
                $env->session->userId = $user->id;
                $env->put('user', $user);
                $this->pool['actionState'] = 'login';
            }
        } elseif ($action == 'logout') {
            $env->session->drop('userId');
            $env->put('user', new SOne_Model_User());
            $this->pool['actionState'] = 'logout';
        }
    }

}
