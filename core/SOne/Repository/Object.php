<?php

class SOne_Repository_Object
{
    protected $db    = null;
    protected $cache = array();

    public function __construct(FDataBase $db)
    {
        $this->db = $db;
    }

    public function loadNavigationByPath($path)
    {
        $pathHash = 'navi'.md5($path);
        if (isset($this->cache[$pathHash])) {
            return $this->cache[$pathHash];
        }

        $path = $this->parsePath($path);

        $paths = Array();
        $hashes = Array();

        // collectings paths and hashes
        for ($i = count($path); $i > 0; $i--)
        {
            $cpath = implode('/', array_slice($path, 0, $i));
            $chash = md5($cpath);
            array_unshift($paths, $cpath);
            array_unshift($hashes, $chash);
        }

        $navis = Array();
        if ($datas = $this->db->doSelectAll('objects_navi', '*', Array('path_hash' => $hashes)))
        {
            foreach ($datas as $data) {
                if (list($i) = array_keys($hashes, $data['path_hash'])) {
                    $navis[$i] = $data;
                }
            }

            ksort($navis);
            F2DArray::keycol($navis, 'id');
        }

        return ($this->cache[$pathHash] = $navis);
    }

    protected function parsePath($path)
    {
        $path = explode('/', $path);

        if (reset($path) !== '') {
            array_unshift($path, '');
        }

        return preg_replace('#\W+#', '_', $path);
    }

    public function loadObjectsTreeByPath($path, $withChilds = false, $withData = false)
    {
        $navis = $this->loadNavigationByPath($path);

        $ids = array_keys($navis);
        $select = $this->db->select('objects', 'o')
            ->join('objects_navi', array('id' => 'o.id'), 'n', array('path', 'path_hash'))
            ->where('o.id', $ids)
            ->order('o.order_id');

        if ($withData) {
            $select->joinLeft('objects_data', array('o_id' => 'o.id'), 'd', array('data'));
        }

        if ($withChilds) {
            $select->whereOr('o.parent_id', $ids);
        }

        $tree = $select->fetchAll();

        $tree = F2DArray::tree($tree, 'id', 'parent_id', 0, 't_level');

        $tree = array_map(array('SOne_Model_Object', 'construct'), $tree);

        return $tree;
    }

    public function loadAll(array $filters = array())
    {
        $select = $this->db->select('objects', 'o')
            ->join('objects_navi', array('id' => 'o.id'), 'n', array('path', 'path_hash'))
            ->joinLeft('objects_data', array('o_id' => 'o.id'), 'd', array('data'))
            ->order('o.order_id');
        
        foreach ($filters as $key => $filter) {
            $select->where($key, $filter);
        }

        $rows = $select->fetchAll();

        $objects = array();
        foreach ($rows as $row) {
            $object = SOne_Model_Object::construct($row);
            $objects[$object->id] = $object;
        }

        return $objects;
    }

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
