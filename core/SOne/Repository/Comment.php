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

class SOne_Repository_Comment extends SOne_Repository
{
    public function loadAll(array $filters = array())
    {
        $select = $this->db->select('comments', 'c')
            ->order('c.id', 'DESC');

        foreach ($filters as $key => $filter) {
            $select->where($key, $filter);
        }

        $rows = $select->fetchAll();

        $rows = F2DArray::tree($rows, 'id', 'answer_to', 0, 't_level');

        return $rows;

        // TODO: objects
        /* $objects = array();
        foreach ($rows as $row) {
            $object = new SOne_Model_Comment($row);
            $objects[$object->id] = $object;
        }

        return $objects; */
    }

    public function save(array &$comment)
    {
        $bind = $comment;
        unset($bind['id'], $bind['t_level']);

        if (isset($comment['id']) && is_numeric($comment['id'])) {
            $this->db->doUpdate('comments', $bind, array('id' => $comment['id']));
        } else {
            $comment['id'] = $this->db->doInsert('comments', $bind);
        }
    }
}
