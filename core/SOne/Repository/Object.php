<?php

class SOne_Repository_Object extends SOne_Repository
{
    protected static $dbMap = array(
        'id'          => 'id',
        'parentId'    => 'parent_id',
        'class'       => 'class',
        'caption'     => 'caption',
        'ownerId'     => 'owner_id',
        'createTime'  => 'create_time',
        'updateTime'  => 'update_time',
        'accessLevel' => 'acc_lvl',
        'editLevel'   => 'edit_lvl',
        'orderId'     => 'order_id',
    );

    protected static $dbMapNavi = array(
        'path'        => 'path',
        'pathHash'    => 'path_hash',
    );

    public function loadNavigationByPath($path)
    {
        $path = trim($path, ' \\/');
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
        $select = $this->db->select('objects', 'o', self::$dbMap)
            ->join('objects_navi', array('id' => 'o.id'), 'n', self::$dbMapNavi)
            ->where('o.id', $ids)
            ->order('o.order_id');

        if ($withData) {
            $select->joinLeft('objects_data', array('o_id' => 'o.id'), 'd', array('data'));
        }

        if ($withChilds) {
            $select->whereOr('o.parent_id', $ids);
        }

        $tree = $select->fetchAll();

        $tree = F2DArray::tree($tree, 'id', 'parentId', 0, 't_level');

        $tree = array_map(array('SOne_Model_Object', 'construct'), $tree);

        return $tree;
    }

    public function loadAll(array $filters = array())
    {
        $select = $this->db->select('objects', 'o', self::$dbMap)
            ->joinLeft('objects_navi', array('id' => 'o.id'), 'n', self::$dbMapNavi)
            ->joinLeft('objects_data', array('o_id' => 'o.id'), 'd', array('data'))
            ->order('o.order_id');

        foreach ($filters as $key => $filter) {
            $select->where($key, $filter);
        }

        $rows = $select->fetchAll();

        $objects = array();
        foreach ($rows as $row) {
            $row['data'] = unserialize($row['data']);

            $object = SOne_Model_Object::construct($row);
            if ($object instanceof SOne_Interface_Object_WithExtraData) {
                /** @var SOne_Interface_Object_WithExtraData $object */
                $object->loadExtraData($this->db);
            }
            if ($object instanceof SOne_Interface_Object_WithSubObjects) {
                /** @var SOne_Interface_Object_WithSubObjects $object */
                $object->setSubObjects($this->loadAll($object->getSubObjectsFilter()));
            }
            $objects[$object->id] = $object;
        }

        return $objects;
    }

    public function save(SOne_Model_Object $object)
    {
        $objData = self::mapModelToDb($object, self::$dbMap, 'id');

        if ($object->id) {
            $this->db->doUpdate('objects', $objData, array('id' => $object->id));
        } else {
            $object->id = $this->db->doInsert('objects', $objData);
        }

        if (!is_null($object->path)) {
            // TODO: checking paths with parents
            $naviData = self::mapModelToDb($object, self::$dbMapNavi);
            $this->db->doInsert('objects_navi', $naviData + array('id' => $object->id), true);
        } else {
            $this->db->doDelete('objects_navi', array('id' => $object->id));
        }

        $data = $object->serializeData();
        if (!is_null($data)) {
            $data = array(
                'o_id' => $object->id,
                'data' => $data,
            );
            $this->db->doInsert('objects_data', $data, true);
        } else {
            $this->db->doDelete('objects_data', array('id' => $object->id));
        }

        if ($object instanceof SOne_Interface_Object_WithExtraData) {
            /** @var SOne_Interface_Object_WithExtraData $object */
            $object->saveExtraData($this->db);
        }
        if ($object instanceof SOne_Interface_Object_WithSubObjects) {
            /** @var SOne_Interface_Object_WithSubObjects $object */
            $this->saveAll($object->getSubObjects());
        }
    }

    public function saveAll(array $objects)
    {
        foreach ($objects as $object) {
            $this->save($object);
        }
    }
}
