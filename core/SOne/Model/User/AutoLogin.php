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
 * @property string  $id
 * @property int     $userId
 * @property string  $userSig
 * @property int     $lastUsed
 * @property int     $startTime
 */
class SOne_Model_User_AutoLogin extends SOne_Model
{
    const LIFETIME = 2592000; // 3600*24*30

    /**
     * @param array $init
     */
    public function __construct(array $init = array())
    {
        $this->pool = array(
            'id'        => isset($init['id'])        ? (string)$init['id']      : null,
            'userId'    => isset($init['userId'])    ? (int)$init['userId']     : null,
            'userSig'   => isset($init['userSig'])   ? (string)$init['userSig'] : '',
            'lastUsed'  => isset($init['lastUsed'])  ? (int)$init['lastUsed']   : time(),
            'startTime' => isset($init['startTime']) ? (int)$init['startTime']  : time(),
        );
    }

    /**
     * @param  string $id
     * @return SOne_Model_User_AutoLogin
     */
    public function setId($id)
    {
        $this->pool['id'] = $id;
        return $this;
    }

    /**
     * @return $this
     */
    public function update()
    {
        $this->pool['lastUsed'] = time();
        return $this;
    }

}
