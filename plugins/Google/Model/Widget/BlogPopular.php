<?php
/**
 * Copyright (C) 2012 - 2015 Andrey F. Kupreychik (Foxel)
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
 * @property-read string $blogPath
 * @property-read string $title
 * @property-read int $limit
 */
class Google_Model_Widget_BlogPopular extends SOne_Model_Widget
{
    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        parent::__construct($init);

        $this->setData((array)$this->pool['data']);
    }

    /**
     * @param SOne_Environment $env
     * @param SOne_Model_Object $pageObject
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env, SOne_Model_Object $pageObject = null)
    {
        $vis = $env->getVIS();
        $db = $env->getDb();

        $container = new FVISNode('SONE_GOOGLE_WIDGET_POPULAR_BLOCK', 0, $vis);
        $container->addDataArray($this->pool);

        if ($pageObject instanceof SOne_Model_Object_BlogMerge) {
            $blogIds = $pageObject->blogIds;
        } elseif ($pageObject instanceof SOne_Model_Object_BlogRoot) {
            $blogIds = $pageObject->id;
        } elseif ($pageObject instanceof SOne_Model_Object_BlogItem) {
            $blogIds = $pageObject->parentId;
        } elseif ($this->blogPath) {
            $blogIds = SOne_Repository_Object::getInstance($db)->loadIds(array('path=' => $this->blogPath));
        } else {
            return $container;
        }

        if (empty($blogIds)) {
            return $container;
        }

        $topObjects = $this->_getTopObjects($blogIds, $db, $this->limit);

        $items = array();
        foreach ($topObjects as $object) {
            $items[] = array(
                'id'      => $object->id,
                'path'    => $object->path,
                'caption' => $object->caption,
                'image'   => $object->thumbnailImage,
                'createTime' => $object->createTime,
            );
        }

        if ($items) {
            $tagsNode = new FVISNode('SONE_GOOGLE_WIDGET_POPULAR_ITEM', FVISNode::VISNODE_ARRAY, $vis);
            $tagsNode->addDataArray($items);
            $container->appendChild('items', $tagsNode);
        }

        return $container;
    }

    /**
     * @param int|int[] $blogIds
     * @param K3_Db_Abstract $db
     * @param int|null $limit
     * @return SOne_Model_Object_BlogItem[]
     */
    protected function _getTopObjects($blogIds, K3_Db_Abstract $db, $limit = null)
    {
        $topIdsOrdered = SOne_Repository_Object::getInstance($db)->loadIds(array(
            'parentId=' => $blogIds,
            'class='    => 'BlogItem',
        ), true)
            ->join('google_stats', array('object_id' => 'id'), 's', array())
            ->where('s.period', 'D')
            ->order('s.pageviews', true)
            ->order('s.visitors', true)
            ->order('s.visits', true)
            ->limit($limit)
            ->fetchAll();

        if (empty($topIdsOrdered)) {
            return array();
        }

        $objects = SOne_Repository_Object::getInstance($db)->loadAll(array(
            'id=' => $topIdsOrdered,
        ));

        $topObjects = array();
        foreach ($topIdsOrdered as $id) {
            $topObjects[] = $objects[$id];
        }

        return $topObjects;
    }

    /**
     * @param array $data
     * @return static
     */
    protected function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'limit' => 10,
            'title' => null,
            'blogPath' => null,
        );
        $this->pool['limit'] =& $this->pool['data']['limit'];
        $this->pool['title'] =& $this->pool['data']['title'];
        $this->pool['blogPath'] =& $this->pool['data']['blogPath'];
        return $this;
    }
}
