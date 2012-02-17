<?php

class SOne_Repository_User extends SOne_Repository
{

    public function loadAll(array $filters = array())
    {
        $select = $this->db->select('users', 'u')
            ->joinLeft('users_auth', array('uid' => 'u.id'), 'a', array('login', 'pass_crypt'))
            ->order('u.id');

        foreach ($filters as $key => $filter) {
            $select->where($key, $filter);
        }

        $rows = $select->fetchAll();

        $objects = array();
        foreach ($rows as $row) {
            $object = new SOne_Model_User($row);
            $objects[$object->id] = $object;
        }

        return $objects;
    }

    public function save(SOne_Model_User $object)
    {
        $userData = array(
            'nick'        => $object->name,
            'level'       => $object->accessLevel,
            'mod_lvl'     => $object->modLevel,
            'adm_lvl'     => $object->adminLevel,
            'frozen'      => $object->frozen,
            'readonly'    => $object->readonly,
            'avatar'      => $object->avatar,
            'regtime'     => $object->registerTime,
            'lastseen'    => $object->lastSeen,
            'last_url'    => $object->lastUrl,
            'last_uagent' => $object->lastUserAgent,
            'last_ip'     => $object->lastIP,
            'last_sid'    => $object->lastSID,
        );

        if ($object->id) {
            $this->db->doUpdate('users', $userData, array('id' => $object->id));
        } else {
            $object->id = $this->db->doInsert('users', $userData);
        }
        
        if ($object->authUpdated) {
            if (!empty($object->login)) {
                $authData = array(
                    'uid'        => $object->id,
                    'login'      => $object->login,
                    'pass_crypt' => $object->cryptedPassword,
                    'last_auth'  => $object->lastAuth,
                );

                $this->db->doInsert('users_auth', $authData, true);
            } else {
                $this->db->doDelete('users_auth', array('uid' => $object->id));
            }
        }
    }
}
