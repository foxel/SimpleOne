<?php

abstract class SOne_Repository
{
    protected $db    = null;
    protected $cache = array();
    public static $instances = array();

    public function __construct(FDataBase $db)
    {
        $this->db = $db;
    }

    public static function getInstance(FDataBase $db)
    {
        $class = get_called_class();
        $UID = $class.'_'.$db->UID;

        if (isset(self::$instances[$UID])) {
            return self::$instances[$UID];
        }

        $instance = new $class($db);
        self::$instances[$UID] = $instance;

        return $instance;
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

