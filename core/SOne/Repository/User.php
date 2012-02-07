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
    }

}
