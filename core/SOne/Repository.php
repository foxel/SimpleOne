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

abstract class SOne_Repository
{
    protected $db    = null;
    protected $cache = array();

    /**
     * @var SOne_Repository[]
     */
    protected static $instances = array();

    /**
     * @param FDataBase $db
     */
    public function __construct(FDataBase $db)
    {
        $this->db = $db;
    }

    /**
     * @static
     * @param FDataBase $db
     * @return static
     */
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

