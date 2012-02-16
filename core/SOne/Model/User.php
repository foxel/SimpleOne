<?php

class SOne_Model_User extends FBaseClass
{
    public function __construct(array $init = array())
    {
        $this->pool = array(
            'id'              => isset($init['id'])          ? (int) $init['id']             : null,
            'nick'            => isset($init['nick'])        ? (string) $init['nick']        : '',
            'accessLevel'     => isset($init['level'])       ? (int) $init['level']          : 0,
            'modLevel'        => isset($init['mod_lvl'])     ? (int) $init['mod_lvl']        : 0,
            'adminLevel'      => isset($init['adm_lvl'])     ? (int) $init['adm_lvl']        : 0,
            'frozen'          => isset($init['frozen'])      ? (bool) $init['frozen']        : false,
            'readonly'        => isset($init['readonly'])    ? (bool) $init['readonly']      : false,
            'avatar'          => isset($init['avatar'])      ? (string) $init['avatar']      : null,
            'registerTime'    => isset($init['regtime'])     ? (int) $init['regtime']        : time(),
            'lastSeen'        => isset($init['lastseen'])    ? (int) $init['lastseen']       : time(),
            'lastUrl'         => isset($init['last_url'])    ? (string) $init['last_url']    : '',
            'lastUserAgent'   => isset($init['last_uagent']) ? (string) $init['last_uagent'] : '',
            'lastIP'          => isset($init['last_ip'])     ? (int) $init['last_ip']        : 0,
            'lastSID'         => isset($init['last_sid'])    ? (string) $init['last_sid']    : '',

            'login'           => isset($init['login'])       ? (string) $init['login']       : null,
            'cryptedPassword' => isset($init['pass_crypt'])  ? (string) $init['pass_crypt']  : null,
            'lastAuth'        => isset($init['last_auth'])   ? (int) $init['last_auth']      : null,
        );
    }

    public function checkPassword($password)
    {
        return (crypt($password, $this->cryptedPassword) == $this->cryptedPassword);
    }

    public function setPassword($password)
    {
        $this->pool['cryptedPassword'] = crypt($password, '$1$'.FStr::shortUID());
        return $this;
    }
}
