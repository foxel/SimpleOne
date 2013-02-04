<?php
/**
 * Copyright (C) 2012 - 2013 Andrey F. Kupreychik (Foxel)
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

        if ($this->blogPath) {
            $blogObject = SOne_Repository_Object::getInstance($db)->loadOne(array('path=' => $this->blogPath));
        } else {
            $blogObject = $pageObject;
        }

        if ($blogObject instanceof SOne_Model_Object_BlogRoot || $blogObject instanceof SOne_Model_Object_BlogItem) {
            $blogId = $blogObject instanceof SOne_Model_Object_BlogRoot ? $blogObject->id : $blogObject->parentId;
            $blogPath = $blogObject instanceof SOne_Model_Object_BlogRoot ? $blogObject->path : preg_replace('#/[^/]+$#', '', trim($blogObject->path, '/'));
            $topObjects = $this->_getTopObjects($blogId, $blogPath, $db, $this->limit);

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
     * @param string $blogPath
     * @param FDataBase $db
     * @param int|null $limit
     * @return SOne_Model_Object
     */
    protected function _getTopObjects($blogId, $blogPath, FDataBase $db, $limit = null)
    {
        $config = Google_Bootstrap::getPluginInstance()->getConfig();

        $rawStats = null;
        if ($statsCache = FCache::get('googleStats.'.$blogId)) {
            if ($statsCache['timestamp'] >= time() - 900) {
                $rawStats = $statsCache['stats'];
            }
        }
        if ($rawStats === null) {
            try {
                $auth = Google_Bootstrap::getPluginInstance()->getAPIAuth(Google_API_Analytics::SCOPE_URL);
                $analytics = new Google_API_Analytics($auth);
                $rawStats = $analytics->getMostVisitedPagesStats($analytics->getFistProfileId($config->analytics->accountId), $blogPath.'/');
            } catch (Exception $e) {
                $rawStats = array();
            }
            FCache::set('googleStats.'.$blogId, array(
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
            $path = preg_replace('#^/+|[?\#]+.*$#', '', $rawRow['ga:pagePath']);
            unset($rawRow['ga:pagePath']);

            if (isset($stats[$path])) {
                foreach ($rawRow as $k => $v) {
                    $stats[$path][$k] += $v;
                }
            } elseif ($limit && count($stats) >= $limit) {
                break;
            } else {
                $stats[$path] = $rawRow;
            }
        }
        unset($rawStats);

        $paths = array_keys($stats);

        $objects = SOne_Repository_Object::getInstance($db)->loadAll(array('parentId=' => $blogId, 'path=' => $paths));

        $topObjects = array();
        foreach ($paths as $path) {
            foreach ($objects as $object) {
                if ($object->path == $path) {
                    $topObjects[$object->id] = $object;
                    continue;
                }
            }
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
