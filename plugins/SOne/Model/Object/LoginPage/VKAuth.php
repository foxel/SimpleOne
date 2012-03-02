<?php

class SOne_Model_Object_LoginPage_VKAuth extends SOne_Model_Object_LoginPage
{

    public function visualize(K3_Environment $env)
    {
        $node = parent::visualize($env);
        $config = $env->get('app')->config;
        $request = array(
            'client_id' => $config['vk.appId'],
            'scope' => 'notify,offline',
            'redirect_uri' => FStr::fullUrl($this->path.'?vkauth'),
            'response_type' => 'code',
        );
        $node->setType('SONE_OBJECT_LOGINPAGE_VKAUTH')
            ->addDataArray(array(
                'vkAuthLink' => 'http://oauth.vk.com/authorize?'.http_build_query($request, '_', '&amp;'),
                'vkAppId'    => $config['vk.appId'],
            ));

        return $node;
    }

    protected function vkauthAction(K3_Environment $env, &$updated = false)
    {
        $config = $env->get('app')->config;
        $code = $env->request->getString('code', K3_Request::GET, FStr::LINE);
        if (!$code) {
            $this->pool['actionState'] = 'redirect';
            return;
        }

        // code ok
        $tokenRequest = array(
            'client_id'     => $config['vk.appId'],
            'client_secret' => $config['vk.appSecret'],
            'code'          => $code,
        );

        $tokenData = file_get_contents('https://oauth.vkontakte.ru/access_token?'.http_build_query($tokenRequest, '_', '&'));
        $tokenData = json_decode($tokenData);
        if (empty($tokenData) || !$tokenData->access_token) {
            $this->pool['actionState'] = 'redirect';
            return;
        }

        $users = SOne_Repository_User::getInstance($env->get('db'));
        // TODO: move to repository
        $db = $env->get('db');
        if ($userId = $db->doSelect('vk_users', 'uid', array('vk_id' => $tokenData->user_id))) {
            $db->doUpdate('vk_users', array('token' => $tokenData->access_token), array('vk_id' => $tokenData->user_id));
            $user = $users->loadOne(array('id' => $userId));
            $env->get('app')->setAuthUser($user);
            $this->pool['actionState'] = 'redirect';
        } else {
            $infoRequest = array(
                'access_token' => $tokenData->access_token,
                'uids'         => $tokenData->user_id,
                'fields'       => 'nickname',
            );
            $userInfo = file_get_contents('https://api.vk.com/method/users.get?'.http_build_query($infoRequest, '_', '&'));
            $userInfo = json_decode($userInfo);
            if (empty($userInfo) || !$userInfo->response) {
                $this->pool['actionState'] = 'redirect';
                return;
            }
            $userInfo = array_shift($userInfo->response);

            $user = new SOne_Model_User(array(
                'name'         => isset($userInfo->nickname) && $userInfo->nickname
                    ? $userInfo->nickname
                    : $userInfo->first_name.' '.$userInfo->last_name,
                'email'        => '',
                'accessLevel'  => 1,
                'registerTime' => time(),
            ));
            $users->save($user);
            $db->doInsert('vk_users', array(
                'token' => $tokenData->access_token,
                'vk_id' => $tokenData->user_id,
                'uid'   => $user->id,
            ));
            $env->get('app')->setAuthUser($user);
            $this->pool['actionState'] = 'redirect';
        }
    }

    protected function vkauthcookieAction(K3_Environment $env, &$updated = false)
    {
        $this->pool['actionState'] = 'redirect';
        $config = $env->get('app')->config;
        $cookieName = 'vk_app_'.$config['vk.appId'];
        $cookieValue = $env->getCookie($cookieName, false);

        $vkUserId = $this->checkVkCookie($cookieValue, $config['vk.appSecret']);
        if (!$vkUserId) {
            return;
        }

        $db = $env->get('db');
        if ($userId = $db->doSelect('vk_users', 'uid', array('vk_id' => $vkUserId))) {
            $users    = SOne_Repository_User::getInstance($env->get('db'));
            $user = $users->loadOne(array('id' => $userId));
            $env->get('app')->setAuthUser($user);
            $this->pool['actionState'] = 'redirect';
        } else {
            $request = array(
                'client_id' => $config['vk.appId'],
                'scope' => 'notify,offline',
                'redirect_uri' => FStr::fullUrl($this->path.'?vkauth'),
                'response_type' => 'code',
            );
            $env->response->sendRedirect('http://oauth.vk.com/authorize?'.http_build_query($request, '_', '&amp;'));
        }
    }

    protected function logoutAction(K3_Environment $env, &$updated = false)
    {
        $this->pool['actionState'] = 'redirect';
        $config = $env->get('app')->config;
        $cookieName = 'vk_app_'.$config['vk.appId'];
        $env->setCookie($cookieName, false, false, false, false);
    }

    protected function checkVkCookie($cookieValue, $appSecret)
    {
        static $hashParams = array('expire', 'mid', 'secret', 'sid');
        $params = array();
        parse_str($cookieValue, $params);

        if (!isset($params['expire']) || !isset($params['sig']) || $params['expire'] < time()) {
            return false;
        }

        $stringToHash = '';
        foreach ($hashParams as $pName) {
            if (!isset($params[$pName])) {
                return false;
            }

            $stringToHash.= $pName.'='.$params[$pName];
        }
        $stringToHash.= $appSecret;
        $hash = md5($stringToHash);

        if ($params['sig'] != $hash) {
            return false;
        }

        return (int) $params['mid'];
    }
}
