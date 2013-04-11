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
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property int    $accessLevel
 * @property int    $modLevel
 * @property int    $adminLevel
 * @property bool   $frozen
 * @property bool   $readonly
 * @property string $avatar
 * @property int    $registerTime
 * @property int    $lastSeen
 * @property string $lastUrl
 * @property string $lastUserAgent
 * @property int    $lastIP
 * @property string $lastSID
 *
 * @property string $login
 * @property string $cryptedPassword
 * @property int    $lastAuth
 *
 * @property bool   $authUpdated
 */
class SOne_Model_User extends SOne_Model
{
    /**
     * @param array $init
     */
    public function __construct(array $init = array())
    {
        $this->pool = array(
            'id'              => isset($init['id'])              ?  (int)    $init['id']              : null,
            'name'            => isset($init['name'])            ?  (string) $init['name']            : '',
            'email'           => isset($init['email'])           ?  (string) $init['email']           : '',
            'accessLevel'     => isset($init['accessLevel'])     ?  (int)    $init['accessLevel']     : 0,
            'modLevel'        => isset($init['modLevel'])        ?  (int)    $init['modLevel']        : 0,
            'adminLevel'      => isset($init['adminLevel'])      ?  (int)    $init['adminLevel']      : 0,
            'frozen'          => isset($init['frozen'])          ?  (bool)   $init['frozen']          : false,
            'readonly'        => isset($init['readonly'])        ?  (bool)   $init['readonly']        : false,
            'avatar'          => isset($init['avatar'])          ?  (string) $init['avatar']          : null,
            'registerTime'    => isset($init['registerTime'])    ?  (int)    $init['registerTime']    : time   () ,
            'lastSeen'        => isset($init['lastSeen'])        ?  (int)    $init['lastSeen']        : time   () ,
            'lastUrl'         => isset($init['lastUrl'])         ?  (string) $init['lastUrl']         : '',
            'lastUserAgent'   => isset($init['lastUserAgent'])   ?  (string) $init['lastUserAgent']   : '',
            'lastIP'          => isset($init['lastIP'])          ?  (int)    $init['lastIP']          : 0,
            'lastSID'         => isset($init['lastSID'])         ?  (string) $init['lastSID']         : '',

            'login'           => isset($init['login'])           ?  (string) $init['login']           : null,
            'cryptedPassword' => isset($init['cryptedPassword']) ?  (string) $init['cryptedPassword'] : null,
            'lastAuth'        => isset($init['lastAuth'])        ?  (int)    $init['lastAuth']        : null,
        );

        $this->pool['authUpdated'] = !$this->pool['id'];
    }

    /**
     * @param string $password
     * @return bool
     */
    public function checkPassword($password)
    {
        return (crypt($password, $this->cryptedPassword) == $this->cryptedPassword);
    }

    /**
     * @param string $password
     * @return $this
     */
    public function setPassword($password)
    {
        $this->pool['cryptedPassword'] = crypt($password, '$1$'.FStr::shortUID());
        $this->pool['authUpdated']     = true;
        return $this;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->pool['name'] = $name;
        return $this;
    }

    /**
     * @param  integer|null $id
     * @return SOne_Model_User
     */
    public function setId($id)
    {
        $this->pool['id'] = $id > 0 ? (int) $id : null;
        return $this;
    }

    public function updateLastSeen(SOne_Environment $env)
    {
        $this->pool['lastSeen'] = time();
        $this->pool['lastIP']   = $env->client->IPInteger;
        $this->pool['lastSID']  = $env->session->getSID();
        $this->pool['lastUrl']  = $env->request->url;
        return $this;
    }

}
