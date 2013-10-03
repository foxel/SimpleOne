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
                    'scope'         => 'notify,offline',
                    'redirect_uri'  => FStr::fullUrl($this->path.'?vkauth'),
                    'response_type' => 'code',
                );

                $node->addDataArray(array(
                    'vkAuthLink' => 'http://oauth.vk.com/authorize?'.http_build_query($request, '_', '&amp;'),
                    'vkAppId'    => $config->vkAppId,
                ));
            }

            if ($config->fbAppId) {
                $request = array(
                    'client_id'     => $config->fbAppId,
                    //'scope'         => 'notify,offline',
                    'redirect_uri'  => FStr::fullUrl($this->path.'?fbauth'),
                    'state'         => md5(uniqid()),
                );

                $node->addDataArray(array(
                    'fbAuthLink' => 'https://www.facebook.com/dialog/oauth?'.http_build_query($request, '_', '&amp;'),
                    'fbAppId'    => $config->fbAppId,
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

        $code = $env->request->getString('code', K3_Request::GET, FStr::LINE);
        if (!$code) {
            $this->pool['actionState'] = 'redirect';
            return;
        }

        // code ok
        $tokenRequest = array(
            'client_id'     => $config->vkAppId,
            'client_secret' => $config->vkAppSecret,
            'code'          => $code,
        );

        $tokenData = file_get_contents('https://oauth.vkontakte.ru/access_token?'.http_build_query($tokenRequest, '_', '&'));
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
            $app->setAuthUser($user, true);
            $this->pool['actionState'] = 'redirect';
        } else {
            $infoRequest = array(
                'access_token' => $tokenData->access_token,
                'uids'         => $tokenData->user_id,
                'fields'       => 'nickname',
            );
            $userInfo    = file_get_contents('https://api.vk.com/method/users.get?'.http_build_query($infoRequest, '_', '&'));
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
            $app->setAuthUser($user, true);
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

        $code = $env->request->getString('code', K3_Request::GET, FStr::LINE);
        if (!$code) {
            $this->pool['actionState'] = 'redirect';
            return;
        }

        // code ok
        $tokenRequest = array(
            'client_id'     => $config->fbAppId,
            'client_secret' => $config->fbAppSecret,
            'code'          => $code,
            'redirect_uri'  => FStr::fullUrl($this->path.'?fbauth'),
        );

        $tokenData = file_get_contents('https://graph.facebook.com/oauth/access_token?'.http_build_query($tokenRequest, '_', '&'));
        parse_str($tokenData, $tokenData);
        if (empty($tokenData) || !$tokenData['access_token']) {
            $this->pool['actionState'] = 'redirect';
            return;
        }
        $tokenData = (object) $tokenData;
        $userData = file_get_contents('https://graph.facebook.com/me?'.http_build_query(array('access_token' => $tokenData->access_token, 'fields' => 'id,name,username'), '_', '&'));
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
            $app->setAuthUser($user, true);
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
            $app->setAuthUser($user, true);
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
            $app->setAuthUser($user, true);
            $this->pool['actionState'] = 'redirect';
        } else {
            $request = array(
                'client_id'     => $config->vkAppId,
                'scope'         => 'notify,offline',
                'redirect_uri'  => FStr::fullUrl($this->path.'?vkauth'),
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
}
