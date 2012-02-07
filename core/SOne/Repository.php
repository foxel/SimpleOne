<?php

abstract class SOne_Repository
{
    protected $db    = null;
    protected $cache = array();

    public function __construct(FDataBase $db)
    {
        $this->db = $db;
    }


    abstract public function loadAll(array $filters = array());

    public function loadOne($filter) {
        if (!is_array($filter)) {
            $filter = array(
                'id' => $filter,
            );
        }

        $objects = $this->loadAll($filter);

        return reset($objects);
    }
}

