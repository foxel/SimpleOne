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
     * @param K3_Environment $env
     * @param SOne_Model_Object $pageObject
     * @return FVISNode
     */
    public function visualize(K3_Environment $env, SOne_Model_Object $pageObject = null)
    {
        /** @var $vis FVISInterface */
        $vis = $env->get('VIS');
        /** @var $app FDataBase */
        $db = $env->get('db');

        $container = new FVISNode('SONE_GOOGLE_WIDGET_POPULAR_BLOCK', 0, $vis);
        $container->addDataArray($this->pool);
        if ($pageObject instanceof SOne_Model_Object_BlogRoot || $pageObject instanceof SOne_Model_Object_BlogItem) {
            $blogId = $pageObject instanceof SOne_Model_Object_BlogRoot ? $pageObject->id : $pageObject->parentId;
            $blogPath = $pageObject instanceof SOne_Model_Object_BlogRoot ? $pageObject->path : preg_replace('#/[^/]+$#', '', trim($pageObject->path, '/'));
            $topObjects = $this->_getTopObjects($blogId, $db, $this->limit);

            $items = array();
            foreach ($topObjects as $object) {
                $items[] = array(
                    'id'      => $object->id,
                    'path'    => $object->path,
                    'caption' => $object->caption,
                    'image'   => $object->thumbnailImage,
                );
            }
            $tagsNode = new FVISNode('SONE_GOOGLE_WIDGET_POPULAR_ITEM', FVISNode::VISNODE_ARRAY, $vis);
            $tagsNode->addDataArray($items);
            $container->appendChild('items', $tagsNode);
        }

        return $container;
    }

    /**
     * @param int $blogId
     * @param FDataBase $db
     * @param int|null $limit
     * @return SOne_Model_Object
     */
    protected function _getTopObjects($blogId, FDataBase $db, $limit = null)
    {
        $objects = SOne_Repository_Object::getInstance($db)->loadAll(array('parentId=' => $blogId));
        $config = Google_Bootstrap::getPluginInstance()->getConfig();

        $rawStats = null;
        if ($statsCache = FCache::get('googleStats')) {
            if ($statsCache['timestamp'] >= time() - 900) {
                $rawStats = $statsCache['stats'];
            }
        }
        if ($rawStats === null) {
            try {
                $auth = Google_Bootstrap::getPluginInstance()->getAPIAuth(Google_API_Analytics::SCOPE_URL);
                $analytics = new Google_API_Analytics($auth);
                $rawStats = $analytics->getMostVisitedPagesStats($analytics->getFistProfileId($config->analytics->accountId));
            } catch (Exception $e) {
                $rawStats = array();
            }
            FCache::set('googleStats', array(
                'timestamp' => time(),
                'stats'     => $rawStats,
            ));
        }

        uasort($objects, function(SOne_Model_Object $a, SOne_Model_Object $b) {
            $al = count(explode('/', $a->path));
            $bl = count(explode('/', $b->path));
            if ($al == $bl) {
                return 0;
            }
            return ($al < $bl) ? 1 : -1;
        });

        $stats = array();
        foreach ($rawStats as $rawRow) {
            $path = explode('/', preg_replace('#^/+|[?\#]+.*$#', '', $rawRow['ga:pagePath']));
            unset($rawRow['ga:pagePath']);
            /** @var $objectFound SOne_Model_Object */
            $objectFound = null;
            while (!empty($path)) {
                $pathString = implode('/', $path);
                foreach ($objects as $object) {
                    if ($object->path == $pathString) {
                        $objectFound = $object;
                        break 2;
                    }
                }
                array_pop($path);
            }

            if ($objectFound) {
                if (isset($stats[$objectFound->id])) {
                    foreach ($rawRow as $k => $v) {
                        $stats[$objectFound->id][$k] += $v;
                    }
                } elseif ($limit && count($stats) >= $limit) {
                    break;
                } else {
                    $stats[$objectFound->id] = $rawRow;
                }
            }
        }

        $topObjects = array();

        foreach ($stats as $objectId => $stat) {
            $topObjects[$objectId] = $objects[$objectId];
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
        );
        $this->pool['limit'] =& $this->pool['data']['limit'];
        $this->pool['title'] =& $this->pool['data']['title'];
        return $this;
    }
}