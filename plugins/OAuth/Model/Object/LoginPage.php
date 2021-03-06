<?php
/**
 * Copyright (C) 2012 - 2014 Andrey F. Kupreychik (Foxel)
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

class OAuth_Model_Object_LoginPage extends SOne_Model_Object_LoginPage
{
    /**
     * @param SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        $node = parent::visualize($env);
        if (!ini_get('allow_url_fopen') || !extension_loaded('openssl')) {
            return $node;
        }

        $node->setType('SONE_OBJECT_LOGINPAGE_OAUTH');

        $config = OAuth_Bootstrap::getConfig();

        if (($baseDomain = $config->baseDomain) && !preg_match('#([\w\.]+\.)?'.preg_quote($baseDomain, '#').'$#i', $env->server->domain)) {
            $node->addDataArray(array(
                'oauthMoveToLogin' => 'http://'.$baseDomain.'/'.($env->server->rootPath ? $env->server->rootPath.'/' : '').$this->path,
            ));
        } else {
            if ($config->vkAppId) {
                $request = array(
                    'client_id'     => $config->vkAppId,
                    'scope'         => 'notify',
                    'redirect_uri'  => K3_Util_Url::fullUrl($this->path.'?vkauth', $env),
                    'response_type' => 'code',
                );

                $node->addDataArray(array(
                    'vkAuthLink' => 'https://oauth.vk.com/authorize?'.urldecode(http_build_query($request, '_', '&')),
                    'vkAppId'    => $config->vkAppId,
                ));
            }

            if ($config->fbAppId) {
                $request = array(
                    'client_id'     => $config->fbAppId,
                    //'scope'         => 'notify,offline',
                    'redirect_uri'  => K3_Util_Url::fullUrl($this->path.'?fbauth', $env),
                    'state'         => md5(uniqid()),
                    'response_type' => 'code',
                );

                $node->addDataArray(array(
                    'fbAuthLink' => 'https://www.facebook.com/dialog/oauth?'.http_build_query($request, '_', '&'),
                    'fbAppId'    => $config->fbAppId,
                ));
            }

            if ($config->gAppId) {
                $request = array(
                    'client_id'     => $config->gAppId,
                    'scope'         => 'https://www.googleapis.com/auth/userinfo.profile',
                    'redirect_uri'  => K3_Util_Url::fullUrl($this->path.'?gauth', $env),
                    'state'         => md5(uniqid()),
                    'response_type' => 'code',
                );

                $node->addDataArray(array(
                    'gAuthLink' => 'https://accounts.google.com/o/oauth2/auth?'.http_build_query($request, '_', '&'),
                    'gAppId'    => $config->gAppId,
                ));
            }
        }

        return $node;
    }

    /**
     * @param SOne_Environment $env
     * @param bool $updated
     * @return mixed
     */
    protected function vkauthAction(SOne_Environment $env, &$updated = false)
    {
        $config = OAuth_Bootstrap::getConfig();
        /* @var SOne_Application $app */
        $app = $env->getApp();

        $code = $env->request->getString('code', K3_Request::GET, K3_Util_String::FILTER_LINE);
        if (!$code) {
            $this->pool['actionState'] = 'redirect';
            return;
        }

        // code ok
        $tokenRequest = array(
            'client_id'     => $config->vkAppId,
            'client_secret' => $config->vkAppSecret,
            'redirect_uri'  => K3_Util_Url::fullUrl($this->path.'?vkauth', $env),
            'code'          => $code,
        );

        $tokenData = $this->_httpRequest('https://oauth.vkontakte.ru/access_token', $tokenRequest);
        $tokenData = json_decode($tokenData);
        if (empty($tokenData) || !$tokenData->access_token) {
            $this->pool['actionState'] = 'redirect';
            return;
        }

        /* @var SOne_Repository_User $users */
        $users = SOne_Repository_User::getInstance($env->getDb());

        // TODO: move to repository
        $db = $env->getDb();
        if ($userId = $db->doSelect('oauth_tokens', 'uid', array('oauth_uid' => $tokenData->user_id, 'api' => 'vk'))) {
            $db->doUpdate('oauth_tokens', array('token' => $tokenData->access_token), array('oauth_uid' => $tokenData->user_id, 'api' => 'vk'));
            $user = $users->loadOne(array('id' => $userId));
            $app->setAuthUser($user, true, true);
            $this->pool['actionState'] = 'redirect';
        } else {
            $infoRequest = array(
                'access_token' => $tokenData->access_token,
                'uids'         => $tokenData->user_id,
                'fields'       => 'nickname',
            );
            $userInfo    = $this->_httpRequest('https://api.vk.com/method/users.get', $infoRequest);
            $userInfo    = json_decode($userInfo);
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
            $db->doInsert('oauth_tokens', array(
                'token'     => $tokenData->access_token,
                'oauth_uid' => $tokenData->user_id,
                'uid'       => $user->id,
                'api'       => 'vk',
            ));
            $app->setAuthUser($user, true, true);
            $this->pool['actionState'] = 'redirect';
        }
    }

    /**
     * @param SOne_Environment $env
     * @param bool $updated
     * @return mixed
     */
    protected function fbauthAction(SOne_Environment $env, &$updated = false)
    {
        $config = OAuth_Bootstrap::getConfig();
        /* @var SOne_Application $app */
        $app = $env->getApp();

        $code = $env->request->getString('code', K3_Request::GET, K3_Util_String::FILTER_LINE);
        if (!$code) {
            $this->pool['actionState'] = 'redirect';
            return;
        }

        // code ok
        $tokenRequest = array(
            'client_id'     => $config->fbAppId,
            'client_secret' => $config->fbAppSecret,
            'code'          => $code,
            'redirect_uri'  => K3_Util_Url::fullUrl($this->path.'?fbauth', $env),
        );

        $tokenData = $this->_httpRequest('https://graph.facebook.com/oauth/access_token', $tokenRequest);
        parse_str($tokenData, $tokenData);
        if (empty($tokenData) || !$tokenData['access_token']) {
            $this->pool['actionState'] = 'redirect';
            return;
        }
        $tokenData = (object) $tokenData;
        $userData = $this->_httpRequest('https://graph.facebook.com/me', array('access_token' => $tokenData->access_token, 'fields' => 'id,name,username'));
        $userData = json_decode($userData);
        if (empty($userData) || !$userData->id) {
            $this->pool['actionState'] = 'redirect';
            return;
        }

        /* @var SOne_Repository_User $users */
        $users = SOne_Repository_User::getInstance($env->getDb());

        // TODO: move to repository
        $db = $env->getDb();
        if ($userId = $db->doSelect('oauth_tokens', 'uid', array('oauth_uid' => $userData->id, 'api' => 'fb'))) {
            $db->doUpdate('oauth_tokens', array('token' => $tokenData->access_token), array('oauth_uid' => $userData->id, 'api' => 'fb'));
            $user = $users->loadOne(array('id' => $userId));
            $app->setAuthUser($user, true, true);
            $this->pool['actionState'] = 'redirect';
        } else {
            $user = new SOne_Model_User(array(
                'name'         => !empty($userData->username)
                    ? $userData->username
                    : $userData->name,
                'email'        => '',
                'accessLevel'  => 1,
                'registerTime' => time(),
            ));
            $users->save($user);
            $db->doInsert('oauth_tokens', array(
                'token'     => $tokenData->access_token,
                'oauth_uid' => $userData->id,
                'uid'       => $user->id,
                'api'       => 'fb',
            ));
            $app->setAuthUser($user, true, true);
            $this->pool['actionState'] = 'redirect';
        }
    }

    /**
     * @param SOne_Environment $env
     * @param bool $updated
     * @return mixed
     */
    protected function gauthAction(SOne_Environment $env, &$updated = false)
    {
        $config = OAuth_Bootstrap::getConfig();
        /* @var SOne_Application $app */
        $app = $env->getApp();

        $code = $env->request->getString('code', K3_Request::GET, K3_Util_String::FILTER_LINE);
        if (!$code) {
            $this->pool['actionState'] = 'redirect';
            return;
        }

        // code ok
        $tokenRequest = array(
            'client_id'     => $config->gAppId,
            'client_secret' => $config->gAppSecret,
            'code'          => $code,
            'redirect_uri'  => K3_Util_Url::fullUrl($this->path.'?gauth', $env),
            'grant_type'    => 'authorization_code',
        );

        $tokenData = $this->_httpRequest('https://accounts.google.com/o/oauth2/token', $tokenRequest, true);
        $tokenData = json_decode($tokenData);
        if (empty($tokenData) || !$tokenData->access_token) {
            $this->pool['actionState'] = 'redirect';
            return;
        }

        $userData = $this->_httpRequest('https://www.googleapis.com/oauth2/v1/userinfo', array('access_token' => $tokenData->access_token));
        $userData = json_decode($userData);
        if (empty($userData) || !$userData->id) {
            $this->pool['actionState'] = 'redirect';
            return;
        }

        /* @var SOne_Repository_User $users */
        $users = SOne_Repository_User::getInstance($env->getDb());

        // TODO: move to repository
        $db = $env->getDb();
        if ($userId = $db->doSelect('oauth_tokens', 'uid', array('oauth_uid' => $userData->id, 'api' => 'g'))) {
            $db->doUpdate('oauth_tokens', array('token' => $tokenData->access_token), array('oauth_uid' => $userData->id, 'api' => 'g'));
            $user = $users->loadOne(array('id' => $userId));
            $app->setAuthUser($user, true, true);
            $this->pool['actionState'] = 'redirect';
        } else {
            $user = new SOne_Model_User(array(
                'name'         => $userData->name,
                'email'        => '',
                'accessLevel'  => 1,
                'registerTime' => time(),
            ));
            $users->save($user);
            $db->doInsert('oauth_tokens', array(
                'token'     => $tokenData->access_token,
                'oauth_uid' => $userData->id,
                'uid'       => $user->id,
                'api'       => 'g',
            ));
            $app->setAuthUser($user, true, true);
            $this->pool['actionState'] = 'redirect';
        }
    }

    /**
     * @param SOne_Environment $env
     * @param bool $updated
     * @return mixed
     */
    protected function vkauthcookieAction(SOne_Environment $env, &$updated = false)
    {
        /* @var SOne_Application $app */
        $app = $env->getApp();

        $this->pool['actionState'] = 'redirect';

        $config      = OAuth_Bootstrap::getConfig();
        $cookieName  = 'vk_app_'.$config->vkAppId;
        $cookieValue = $env->client->getCookie($cookieName, false);

        $vkUserId = $this->checkVkCookie($cookieValue, $config->vkAppSecret);
        if (!$vkUserId) {
            return;
        }

        /* @var SOne_Repository_User $users */
        $users = SOne_Repository_User::getInstance($env->getDb());

        // TODO: move to repository
        $db = $env->getDb();
        if ($userId = $db->doSelect('oauth_tokens', 'uid', array('oauth_uid' => $vkUserId, 'api' => 'vk'))) {
            $user = $users->loadOne(array('id' => $userId));
            $app->setAuthUser($user, true, true);
            $this->pool['actionState'] = 'redirect';
        } else {
            $request = array(
                'client_id'     => $config->vkAppId,
                'scope'         => 'notify,offline',
                'redirect_uri'  => K3_Util_Url::fullUrl($this->path.'?vkauth', $env),
                'response_type' => 'code',
            );
            $env->response->sendRedirect('http://oauth.vk.com/authorize?'.http_build_query($request, '_', '&amp;'));
        }
    }

    /**
     * @param SOne_Environment $env
     * @param bool $updated
     */
    protected function logoutAction(SOne_Environment $env, &$updated = false)
    {
        $this->pool['actionState'] = 'redirect';

        $config     = OAuth_Bootstrap::getConfig();
        $cookieName = 'vk_app_'.$config->vkAppId;
        $env->client->setCookie($cookieName, false, false, false, false);
    }

    /**
     * @param $cookieValue
     * @param $appSecret
     * @return bool|int
     */
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

            $stringToHash .= $pName.'='.$params[$pName];
        }
        $stringToHash .= $appSecret;
        $hash = md5($stringToHash);

        if ($params['sig'] != $hash) {
            return false;
        }

        return (int) $params['mid'];
    }

    /**
     * @param $url
     * @param array $data
     * @param bool $doPOST
     * @return string
     */
    protected function _httpRequest($url, array $data = array(), $doPOST = false)
    {
        if ($doPOST) {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

            $response = curl_exec($curl);
            if (curl_errno($curl)) {
                return false;
            }

            return $response;
        } else {
            return file_get_contents($url.(strpos($url, '?') !== false ? '&' : '?').http_build_query($data, '_', '&'));
        }
    }
}
