<?php

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
