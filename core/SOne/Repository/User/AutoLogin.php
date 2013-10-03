<?php
/**
 * Copyright (C) 2013 Andrey F. Kupreychik (Foxel)
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
 * @method SOne_Model_User_AutoLogin loadOne
 * @method SOne_Model_User_AutoLogin get
 */
class SOne_Repository_User_AutoLogin extends SOne_Repository
{
    protected static $dbMap = array(
        'id'        => 'id',
        'userId'    => 'user_id',
        'userSig'   => 'user_sig',
        'lastUsed'  => 'lastused',
        'startTime' => 'starttime',
    );

    /**
     * @param array $filters
     * @return SOne_Model_User[]
     */
    public function loadAll(array $filters = array())
    {
        $select = $this->_db->select('users_al', 'al', self::$dbMap);

        foreach (self::mapFilters($filters, self::$dbMap, 'al') as $key => $filter) {
            $select->where($key, $filter);
        }

        $rows = $select->fetchAll();

        $objects = array();
        foreach ($rows as $row) {
            $object = new SOne_Model_User_AutoLogin($row);
            $objects[$object->id] = $object;
        }

        return $objects;
    }

    /**
     * @param SOne_Model_User_AutoLogin $object
     */
    public function save(SOne_Model_User_AutoLogin $object)
    {
        $bind = self::mapModelToDb($object, self::$dbMap, 'id');

        if ($object->id) {
            $this->_db->doUpdate('users_al', $bind, array('id' => $object->id));
        } else {
            $id = md5(uniqid());
            $bind['id'] = $id;
            if ($this->_db->doInsert('users_al', $bind)) {
                $object->id = $id;
            }
        }
    }

    /**
     * @param array $filters
     * @return int|null
     */
    public function delete(array $filters)
    {
        $filters = self::mapFilters($filters, self::$dbMap);
        return $this->_db->doDelete('users_al', $filters);
    }
}
