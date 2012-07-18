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
    protected static $dbMap = array(
        'id'     => 'id',
        'userId' => 'user_id',
        'name'   => 'name',
        'time'   => 'time',
    );

    public function loadAll(array $filters = array())
    {
        $select = $this->db->select('tag', 't', self::$dbMap)
            ->order('t.name', 'ASC');

        foreach (self::mapFilters($filters, self::$dbMap, 't') as $key => $filter) {
            $select->where($key, $filter);
        }

        $rows = $select->fetchAll();

        return $rows;
    }

    /**
     * @param $objectId
     * @param array $filters
     * @return array|mixed|null
     */
    public function getObjectTags($objectId, array $filters = array())
    {
        $select = $this->db->select('tag', 't', array('name'))
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
        $select = $this->db->select('tag', 't', array())
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

        $useTransaction = !$this->db->inTransaction;

        if ($useTransaction) {
            $this->db->beginTransaction();
        }

        $this->db->doDelete('tag_object', array('object_id' => (int) $objectId));
        $bind = array();
        foreach ($tagIds as $tagId) {
            $bind[] = array(
                'tag_id' => (int) $tagId,
                'object_id' => (int) $objectId,
            );
        }

        $this->db->doInsert('tag_object', $bind, false, FDataBase::SQL_MULINSERT);

        if ($useTransaction) {
            $this->db->commit();
        }
    }

    /**
     * @param array $tagNames
     * @param bool $autoCreate
     * @return array
     */
    public function getTagIds(array $tagNames, $autoCreate = false)
    {
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
                if ($tagId = $this->createNewTag($tagName)) {
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

        return $this->db->doInsert('tag', $bind);
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
}
