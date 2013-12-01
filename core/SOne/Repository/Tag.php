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

class SOne_Repository_Tag extends SOne_Repository
{
    const MAX_TAG_NAME_LENGTH = 125;

    protected static $dbMap = array(
        'id'     => 'id',
        'userId' => 'user_id',
        'name'   => 'name',
        'time'   => 'time',
    );

    public function loadAll(array $filters = array())
    {
        $select = $this->_db->select('tag', 't', self::$dbMap)
            ->order('t.name', 'ASC');

        foreach (self::mapFilters($filters, self::$dbMap, 't') as $key => $filter) {
            $select->where($key, $filter);
        }

        $rows = $select->fetchAll();

        return $rows;
    }

    public function loadNames(array $filters = array())
    {
        $select = $this->_db->select('tag', 't', array('name' => self::$dbMap['name']))
            ->order('t.name', 'ASC');

        foreach (self::mapFilters($filters, self::$dbMap, 't') as $key => $filter) {
            $select->where($key, $filter);
        }

        $rows = $select->fetchAll();

        return $rows;
    }

    /**
     * @param int $objectId
     * @param int $limit
     * @param array $excludeTags
     * @return mixed
     */
    public function getObjectsRelatedByTags($objectId, $limit = 6, array $excludeTags = array())
    {
        $select = $this->_db->select('tag_object', 't', array())
            ->join('tag_object', array('tag_id' => 't.tag_id'), 't1', array('object_id'))
            ->where('t.object_id', $objectId)
            ->where('t1.object_id != ?', $objectId)
            ->group('t1.object_id')
            ->order('count(t.tag_id)', 'DESC')
            ->order('t1.object_id', 'DESC')
            ->limit($limit);

        if ($excludeTags) {
            $select->where('t.tag_id NOT IN (?)', $excludeTags);
        }


        $ids = $select->fetchAll();

        return $ids;
    }

    /**
     * @param $objectId
     * @param array $filters
     * @return array|mixed|null
     */
    public function getObjectTags($objectId, array $filters = array())
    {
        $select = $this->_db->select('tag', 't', array('name'))
            ->join('tag_object', array('tag_id' => 't.id'), 'to', array())
            ->order('t.name', 'ASC');

        $select->where('to.object_id', $objectId);

        foreach ($filters as $key => $filter) {
            $select->where($key, $filter);
        }

        $names = $select->fetchAll();

        return $names;
    }

    /**
     * @param array|string $tags
     * @param bool $noExecute
     * @return array|FDBSelect
     */
    public function getObjectIdsByTags($tags, $noExecute = false)
    {
        $select = $this->_db->select('tag', 't', array())
            ->joinLeft('tag_object', array('tag_id' => 't.id'), 'to', array('object_id'))
            ->where('t.name', $tags);

        return $noExecute
            ? $select
            : $select->fetchAll();
    }

    /**
     * @param $objectId
     * @param array $tags
     */
    public function setObjectTags($objectId, array $tags)
    {
        $tagIds = $this->getTagIds($tags, true);

        $useTransaction = !$this->_db->inTransaction;

        if ($useTransaction) {
            $this->_db->beginTransaction();
        }

        $this->_db->doDelete('tag_object', array('object_id' => (int) $objectId));
        $bind = array();
        foreach ($tagIds as $tagId) {
            $bind[] = array(
                'tag_id' => (int) $tagId,
                'object_id' => (int) $objectId,
            );
        }

        $this->_db->doInsert('tag_object', $bind, false, FDataBase::SQL_MULINSERT);

        if ($useTransaction) {
            $this->_db->commit();
        }
    }

    /**
     * @param array $tagNames
     * @param bool $autoCreate
     * @return array
     */
    public function getTagIds(array $tagNames, $autoCreate = false)
    {
        $tagNames = array_map(array($this, 'stripTagName'), $tagNames);

        $tags = $this->loadAll(array(
            'name' => $tagNames,
        ));

        $out = array();
        foreach ($tags as $tag) {
            $out[$tag['name']] = $tag['id'];
        }

        if ($autoCreate) {
            $namesToCreate = array_udiff($tagNames, array_keys($out), array($this, 'compareTags'));
            foreach ($namesToCreate as $tagName) {
                if (($tagName = trim($tagName)) && ($tagId = $this->createNewTag($tagName))) {
                    $out[$tagName] = $tagId;
                }
            }
        }

        return $out;
    }

    /**
     * @param $tagName
     * @param null $userId
     * @return int|null
     */
    public function createNewTag($tagName, $userId = null)
    {
        $bind = array(
            'name' => $tagName,
            'time' => F()->Timer->qTime(),
            'user_id' => $userId,
        );

        return $this->_db->doInsert('tag', $bind);
    }

    /**
     * @param array $objectFilters
     * @param int $limit
     * @return array
     */
    public function getTagsCloud(array $objectFilters = array(), $limit = null)
    {
        $select = $this->_db->select('tag', 't', array('id', 'tag' => 'name'))
            ->join('tag_object', array('tag_id' => 't.id'), 'to', array('weight' => 'count(to.object_id)'))
            ->group('id')
            ->group('name');

        if ($objectFilters) {
            $objects = SOne_Repository_Object::getInstance($this->_db);
            $objectIds = $objects->loadIds($objectFilters, true);
            $select->where('to.object_id', $objectIds);
        }

        if ($limit) {
            $select->order('weight', true)
                ->order('name')
                ->limit($limit);
            $select = $this->_db->select($select, 't')->order('tag');
        } else {
            $select->order('name');
        }

        $tags = $select->fetchAll();

        return F2DArray::keycol($tags, 'id', true);
    }

    /**
     * @param $tag1
     * @param $tag2
     * @return int
     */
    public function compareTags($tag1, $tag2)
    {
        return strcmp(FStr::strToLower($tag1), FStr::strToLower($tag2));
    }

    /**
     * @param string $tagName
     * @return string
     */
    public function stripTagName($tagName)
    {
        if (FStr::strLen($tagName) > self::MAX_TAG_NAME_LENGTH) {
            $tagName = FStr::subStr($tagName, 0, self::MAX_TAG_NAME_LENGTH);
        }

        return $tagName;
    }
}
