<?php

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

    public function checkPassword($password)
    {
        return (crypt($password, $this->cryptedPassword) == $this->cryptedPassword);
    }

    public function setPassword($password)
    {
        $this->pool['cryptedPassword'] = crypt($password, '$1$'.FStr::shortUID());
        $this->pool['authUpdated']     = true;
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

    public function updateLastSeen(K3_Environment $env)
    {
        $this->pool['lastSeen'] = time();
        $this->pool['lastIP']   = $env->clientIPInteger;
        $this->pool['lastSID']  = $env->session->getSID();
        $this->pool['lastUrl']  = $env->requestUrl;
        return $this;
    }

}
