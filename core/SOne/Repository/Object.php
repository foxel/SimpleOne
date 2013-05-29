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

/**
 * @method SOne_Model_Object loadOne(array $filter)
 */
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
        'hideInTree'  => 'hide_in_tree',
    );

    public function loadNavigationByPath($path)
    {
        $path = trim($path, ' \\/');
        $pathHash = 'navi'.md5($path);
        if (isset($this->_cache[$pathHash])) {
            return $this->_cache[$pathHash];
        }

        $path = $this->parsePath($path);

        $paths = array();
        $hashes = array();

        // collectings paths and hashes
        for ($i = count($path); $i > 0; $i--)
        {
            $cpath = implode('/', array_slice($path, 0, $i));
            $chash = md5($cpath);
            array_unshift($paths, $cpath);
            array_unshift($hashes, $chash);
        }

        $navis = array();
        if ($datas = $this->_db->doSelectAll('objects_navi', '*', array('path_hash' => $hashes)))
        {
            foreach ($datas as $data) {
                if (list($i) = array_keys($hashes, $data['path_hash'])) {
                    $navis[$i] = $data;
                }
            }

            ksort($navis);
            F2DArray::keycol($navis, 'id');
        }

        return ($this->_cache[$pathHash] = $navis);
    }

    protected function parsePath($path)
    {
        $path = explode('/', trim($path, '/'));

        return preg_replace('#\W+#', '_', $path);
    }

    public function loadObjectsTreeByPath($path, $withChildrenAndSiblings = false, $withData = false)
    {
        $navis = $this->loadNavigationByPath($path);

        $ids = array_keys($navis);

        IF (!empty($ids)) {
            return $this->loadObjectsTree(array('id' => $ids), $withChildrenAndSiblings, $withData);
        } elseif ($withChildrenAndSiblings) {
            return $this->loadObjectsTree(array('parentId' => null), false, $withData);
        } else {
            return array();
        }
    }

    /**
     * @param array $filters
     * @param bool $withChildrenAndSiblings
     * @param bool $withData
     * @return SOne_Model_Object[]|null
     */
    public function loadObjectsTree(array $filters = array(), $withChildrenAndSiblings = false, $withData = false)
    {
        if (!empty($filters) && $withChildrenAndSiblings) {
            $select = $this->_db->select('objects', 'of', array());

            foreach (self::mapFilters($filters, self::$dbMap, 'of') as $key => $filter) {
                $select->where($key, $filter);
            }
            if ($naviFilters = self::mapFilters($filters, self::$dbMapNavi, 'nf')) {
                $select->join('objects_navi', array('id' => 'of.id'), 'nf', array());
                foreach ($naviFilters as $key => $filter) {
                    $select->where($key, $filter);
                }
            }

            $select
                ->join('objects', 'o.id = of.id OR o.parent_id IN (of.id, of.parent_id) OR (of.parent_id IS NULL AND o.parent_id IS NULL)', 'o', self::$dbMap)
                ->join('objects_navi', array('id' => 'o.id'), 'n', self::$dbMapNavi)
                ->order('o.order_id')
                ->order('o.id');

            $filters = array();
        } else {
            $select = $this->_db->select('objects', 'o', self::$dbMap)
                ->join('objects_navi', array('id' => 'o.id'), 'n', self::$dbMapNavi)
                ->order('o.order_id')
                ->order('o.id');
        }

        if ($withData) {
            $select->joinLeft('objects_data', array('o_id' => 'o.id'), 'd', array('data'));
        }

        foreach (self::mapFilters($filters, self::$dbMap, 'o') as $key => $filter) {
            $select->where($key, $filter);
        }
        foreach (self::mapFilters($filters, self::$dbMapNavi, 'n') as $key => $filter) {
            $select->where($key, $filter);
        }

        $tree = $select->fetchAll();

        $tree = F2DArray::tree($tree, 'id', 'parentId', 0, 'treeLevel');

        $tree = array_map(array('SOne_Model_Object', 'construct'), $tree);

        return $tree;
    }

    /**
     * @param array $filters
     * @param string|array|bool $order
     * @param int|bool $limit
     * @param int|bool $offset
     * @param null $rowsCount
     * @return SOne_Model_Object[]
     */
    public function loadAll(array $filters = array(), $order = false, $limit = false, $offset = false, &$rowsCount = null)
    {
        $select = $this->_db->select('objects', 'o', self::$dbMap)
            ->joinLeft('objects_navi', array('id' => 'o.id'), 'n', self::$dbMapNavi)
            ->joinLeft('objects_data', array('o_id' => 'o.id'), 'd', array('data'))
            ->order('o.order_id')
            ->order('o.create_time', true);

        foreach (self::mapFilters($filters, self::$dbMap, 'o') as $key => $filter) {
            $select->where($key, $filter);
        }
        foreach (self::mapFilters($filters, self::$dbMapNavi, 'n') as $key => $filter) {
            $select->where($key, $filter);
        }

        if ($limit) {
            $select->calculateRows()
                ->limit($limit, $offset);
        }

        $rows = $select->fetchAll();

        if ($limit) {
            $rowsCount = $this->_db->lastSelectRowsCount;
        }

        $objects = array();
        foreach ($rows as $row) {
            $row['data'] = unserialize($row['data']);

            $object = SOne_Model_Object::construct($row);
            if ($object instanceof SOne_Interface_Object_WithExtraData) {
                /** @var SOne_Interface_Object_WithExtraData $object */
                $object->loadExtraData($this->_db);
            }
            if ($object instanceof SOne_Interface_Object_WithSubObjects) {
                /** @var SOne_Interface_Object_WithSubObjects $object */
                $object->setSubObjects($this->loadAll($object->getSubObjectsFilter()));
            }
            $objects[$object->id] = $object;
        }

        return $objects;
    }

    /**
     * @param array $filters
     * @param bool $noExecute
     * @return int[]|FDBSelect
     */
    public function loadIds(array $filters, $noExecute = false)
    {
        $select = $this->_db->select('objects', 'o', array('id'))
            ->joinLeft('objects_navi', array('id' => 'o.id'), 'n', array())
            ->joinLeft('objects_data', array('o_id' => 'o.id'), 'd', array());

        foreach (self::mapFilters($filters, self::$dbMap, 'o') as $key => $filter) {
            $select->where($key, $filter);
        }
        foreach (self::mapFilters($filters, self::$dbMapNavi, 'n') as $key => $filter) {
            $select->where($key, $filter);
        }

        return $noExecute
            ? $select
            : $select->fetchAll();
    }

    /**
     * @param array $filters
     * @param bool $noExecute
     * @return string[]|FDBSelect
     */
    public function loadPaths(array $filters, $noExecute = false)
    {
        $select = $this->_db->select('objects', 'o', array())
            ->joinLeft('objects_navi', array('id' => 'o.id'), 'n', array('path'))
            ->joinLeft('objects_data', array('o_id' => 'o.id'), 'd', array());

        foreach (self::mapFilters($filters, self::$dbMap, 'o') as $key => $filter) {
            $select->where($key, $filter);
        }
        foreach (self::mapFilters($filters, self::$dbMapNavi, 'n') as $key => $filter) {
            $select->where($key, $filter);
        }

        return $noExecute
            ? $select
            : $select->fetchAll();
    }

    public function save(SOne_Model_Object $object)
    {
        $objData = self::mapModelToDb($object, self::$dbMap, 'id');

        if ($object->id) {
            $this->_db->doUpdate('objects', $objData, array('id' => $object->id));
        } else {
            $object->id = $this->_db->doInsert('objects', $objData);
        }

        if (!is_null($object->path)) {
            // TODO: checking paths with parents
            $naviData = self::mapModelToDb($object, self::$dbMapNavi);
            $this->_db->doInsert('objects_navi', $naviData + array('id' => $object->id), true);
        } else {
            $this->_db->doDelete('objects_navi', array('id' => $object->id));
        }

        $data = $object->serializeData();
        if (!is_null($data)) {
            $data = array(
                'o_id' => $object->id,
                'data' => $data,
            );
            $this->_db->doInsert('objects_data', $data, true);
        } else {
            $this->_db->doDelete('objects_data', array('id' => $object->id));
        }

        if ($object instanceof SOne_Interface_Object_WithExtraData) {
            /** @var SOne_Interface_Object_WithExtraData $object */
            $object->saveExtraData($this->_db);
        }
        if ($object instanceof SOne_Interface_Object_WithSubObjects) {
            /** @var SOne_Interface_Object_WithSubObjects $object */
            $this->saveAll($object->getSubObjects());
        }
    }

    /**
     * @param int $objectId
     * @return int|null
     */
    public function delete($objectId)
    {
        return $this->_db->doDelete('objects', array('id' => $objectId));
    }

    public function saveAll(array $objects)
    {
        foreach ($objects as $object) {
            $this->save($object);
        }
    }

    /**
     * @param array $filters
     * @param array $map
     * @param string $dbFieldPrefix
     * @return array
     */
    public static function mapFilters(array $filters, array $map, $dbFieldPrefix = '')
    {
        $notMappedFilters = array();
        $mappedFilters = array();

        $dbPrefix = $dbFieldPrefix
            ? $dbFieldPrefix.'.'
            : '';

        foreach ($filters as $filterKey => $filterValue) {
            switch ($filterKey) {
                case 'publishedOrOwnerId=':
                    if (isset($map['ownerId'], $map['createTime'])) {
                        $mappedFilters[$dbPrefix.$map['ownerId'].' = ? OR '.$dbPrefix.$map['createTime'].' <= '.time()] = $filterValue;
                    }
                    break;
                case 'published=':
                    if (isset($map['createTime'])) {
                        $mappedFilters['('.$dbPrefix.$map['createTime'].' <= '.time().') = ?'] = (bool) $filterValue;
                    }
                    break;
                default:
                    $notMappedFilters[$filterKey] = $filterValue;
            }
        }

        return array_merge($mappedFilters, parent::mapFilters($notMappedFilters, $map, $dbFieldPrefix));
    }


}
