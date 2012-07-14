<?php
/**
 * Copyright (C) 2012 Andrey F. Kupreychik (Foxel)
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

class SOne_Repository_User extends SOne_Repository
{
    protected static $dbMap = array(
        'id'              => 'id',
        'name'            => 'nick',
        'email'           => 'email',
        'accessLevel'     => 'level',
        'modLevel'        => 'mod_lvl',
        'adminLevel'      => 'adm_lvl',
        'frozen'          => 'frozen',
        'readonly'        => 'readonly',
        'avatar'          => 'avatar',
        'registerTime'    => 'regtime',
        'lastSeen'        => 'lastseen',
        'lastUrl'         => 'last_url',
        'lastUserAgent'   => 'last_uagent',
        'lastIP'          => 'last_ip',
        'lastSID'         => 'last_sid',
    );

    protected static $dbMapAuth = array(
        'login'           => 'login',
        'cryptedPassword' => 'pass_crypt',
        'lastAuth'        => 'last_auth',
    );

    public function loadAll(array $filters = array())
    {
        $select = $this->db->select('users', 'u', self::$dbMap)
            ->joinLeft('users_auth', array('uid' => 'u.id'), 'a', self::$dbMapAuth)
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

    public function loadNames(array $filters = array())
    {
        $select = $this->db->select('users', 'u', array('id', 'nick'))
            ->order('u.id');

        foreach ($filters as $key => $filter) {
            $select->where($key, $filter);
        }

        $rows = $select->fetchAll();

        $names = array();
        foreach ($rows as $row) {
            $names[$row['id']] = $row['nick'];
        }

        return $names;
    }

    public function save(SOne_Model_User $object)
    {
        $userData = self::mapModelToDb($object, self::$dbMap, 'id');

        if ($object->id) {
            $this->db->doUpdate('users', $userData, array('id' => $object->id));
        } else {
            $object->id = $this->db->doInsert('users', $userData);
        }

        if ($object->authUpdated) {
            if (!empty($object->login)) {
                $authData = self::mapModelToDb($object, self::$dbMapAuth);

                $this->db->doInsert('users_auth', $authData + array('uid' => $object->id), true);
            } else {
                $this->db->doDelete('users_auth', array('uid' => $object->id));
            }
        }
    }
}
