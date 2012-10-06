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

class SOne_Model_Widget_TagCloud extends SOne_Model_Widget
{
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

        $container = new FVISNode('SONE_WIDGET_TAGCLOUD_BLOCK', 0, $vis);
        if ($pageObject instanceof SOne_Model_Object_BlogRoot || $pageObject instanceof SOne_Model_Object_BlogItem) {
            $blogId = $pageObject instanceof SOne_Model_Object_BlogRoot ? $pageObject->id : $pageObject->parentId;
            $blogPath = $pageObject instanceof SOne_Model_Object_BlogRoot ? $pageObject->path : preg_replace('#/[^/]+$#', '', trim($pageObject->path, '/'));
            $cloud = SOne_Repository_Tag::getInstance($db)->getTagsCloud(array('parentId=' => $blogId));

            $tagsNode = new FVISNode('SONE_WIDGET_TAGCLOUD_ITEM', FVISNode::VISNODE_ARRAY, $vis);
            $tagsNode->addDataArray($cloud)
                ->addData('path', $blogPath);
            $container->appendChild('tags', $tagsNode);
        }

        return $container;
    }
}
