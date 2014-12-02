<?php
/**
 * Copyright (C) 2012 - 2014 Andrey F. Kupreychik (Foxel)
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

class SOne_Model_Widget_TagCloud extends SOne_Model_Widget
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

        $container = new FVISNode('SONE_WIDGET_TAGCLOUD_BLOCK', 0, $vis);
        $container->addDataArray($this->pool);
        if ($pageObject instanceof SOne_Model_Object_BlogRoot || $pageObject instanceof SOne_Model_Object_BlogItem) {
            if ($pageObject instanceof SOne_Model_Object_BlogMerge) {
                $blogId = $pageObject->blogIds;
                $blogPath = $pageObject->path;
            } elseif ($pageObject instanceof SOne_Model_Object_BlogRoot) {
                $blogId   = $pageObject->id;
                $blogPath = $pageObject->path;
            } else {
                $blogId = $pageObject->parentId;
                $blogPath = preg_replace('#/[^/]+$#', '', trim($pageObject->path, '/'));
            }

            $cloud = SOne_Repository_Tag::getInstance($db)->getTagsCloud(array('parentId=' => $blogId), $this->limit);

            $tagsNode = new FVISNode('SONE_WIDGET_TAGCLOUD_ITEM', FVISNode::VISNODE_ARRAY, $vis);
            $tagsNode->addDataArray($cloud)
                ->addData('path', $blogPath);
            $container->appendChild('tags', $tagsNode);
        }

        return $container;
    }

    /**
     * @param array $data
     * @return static
     */
    protected function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'limit' => null,
            'title' => null,
        );
        $this->pool['limit'] =& $this->pool['data']['limit'];
        $this->pool['title'] =& $this->pool['data']['title'];
        return $this;
    }
}
