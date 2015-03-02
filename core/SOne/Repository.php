<?php
/**
 * Copyright (C) 2012, 2015 Andrey F. Kupreychik (Foxel)
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

abstract class SOne_Repository
{
    /** @var K3_Db_Abstract */
    protected $_db;
    /** @var array */
    protected $_cache = array();
    /** @var array */
    protected $_preparedFetches = array();
    /** @var array */
    protected $_fetchedItems = array();

    /**
     * @var SOne_Repository[]
     */
    protected static $instances = array();

    /**
     * @param K3_Db_Abstract $db
     */
    public function __construct(K3_Db_Abstract $db)
    {
        $this->_db = $db;
    }

    /**
     * @static
     * @param K3_Db_Abstract $db
     * @return static
     */
    public static function getInstance(K3_Db_Abstract $db)
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

    /**
     * @abstract
     * @param array $filters
     * @return array
     */
    abstract public function loadAll(array $filters = array());

    /**
     * @param mixed $filter
     * @return mixed
     */
    public function loadOne($filter) {
        if (!is_array($filter)) {
            $filter = array(
                'id' => $filter,
            );
        }

        $objects = $this->loadAll($filter);

        return reset($objects);
    }

    /**
     * @param array $filters
     * @return static
     */
    public function prepareFetch(array $filters)
    {
        $this->_preparedFetches[] = $filters;
        return $this;
    }

    /**
     * @param int|string $id
     * @return array|object|null
     */
    public function get($id)
    {
        if (isset($this->_fetchedItems[$id])) {
            return $this->_fetchedItems[$id];
        }

        $out = null;
        while (null === $out && ($filters = array_pop($this->_preparedFetches))) {
            $items = $this->loadAll($filters);
            foreach ($items as $item) {
                $itemId = is_array($item)
                    ? $item['id']
                    : $item->id;

                $this->_fetchedItems[$itemId] = $item;
                if ($itemId == $id) {
                    $out = $item;
                }
            }
        }

        if (null === $out) {
            $out = $this->loadOne(array('id' => $id));
            if ($out !== null) {
                $itemId = is_array($out)
                    ? $out['id']
                    : $out->id;

                $this->_fetchedItems[$itemId] = $out;
            }
        }

        return $out;
    }

    /**
     * @static
     * @param SOne_Model $model
     * @param array $map
     * @param null $exclude
     * @return array
     */
    public static function mapModelToDb(SOne_Model $model, array $map, $exclude = null)
    {
        $exclude = (array) $exclude;
        $res = array();
        foreach ($map as $modelField => $dbField) {
            if (in_array($modelField, $exclude)) {
                continue;
            }
            $res[$dbField] = $model->$modelField;
        }
        return $res;
    }

    /**
     * @static
     * @param array $filters
     * @param array $map
     * @param string $dbFieldPrefix
     * @return array
     */
    public static function mapFilters(array $filters, array $map, $dbFieldPrefix = '')
    {
        $res = array();
        foreach ($filters as $filterKey => $filterValue) {
            if (preg_match('#^(\w+)(!?=|>=?|<=?|~|)$#', $filterKey, $matches) && isset($map[$matches[1]])) {
                $dbField   = $map[$matches[1]];
                if ($dbFieldPrefix) {
                    $dbField = $dbFieldPrefix.'.'.$dbField;
                }
                $operand   = $matches[2];

                if ($operand == '~') {
                    $operand = 'ILIKE';
                }

                if (!$operand || $operand == '=') {
                    $res[$dbField] = $filterValue;
                } else {
                    switch (gettype($filterValue)) {
                        case 'NULL':
                            $operand = ($operand == '=') ? 'IS' : 'IS NOT';
                            $res[$dbField.' '.$operand.' NULL'] = true;
                            break;
                        case 'array':
                            $operand = ($operand == '=') ? 'IN' : 'NOT IN';
                            $res[$dbField.' '.$operand.' (?)'] = $filterValue;
                            break;
                        default:
                            $res[$dbField.' '.$operand.' ?'] = $filterValue;
                            break;
                    }
                }
            }
        }
        return $res;
    }
}

