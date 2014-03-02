<?php
/**
 * Copyright (C) 2013 - 2014 Andrey F. Kupreychik (Foxel)
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
 * Class SOne_Model_Object_BlogFrontPage
 *
 * @property-read string $blogPath
 * @property-read string[] $tags
 */
class SOne_Model_Object_BlogFrontPage extends SOne_Model_Object
    implements SOne_Interface_Object_Structured, SOne_Interface_Object_WithSubObjects
{

    /**
     * @param  SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        $node = new FVISNode('SONE_OBJECT_BLOG_FRONT', 0, $env->getVIS());
        $node->addDataArray($this->pool);

        /** @var $blogObject SOne_Model_Object_BlogRoot */
        $blogObject = SOne_Repository_Object::getInstance($env->db)->loadOne(array(
            'path='  => $this->blogPath,
        ));

        if (!$blogObject instanceof SOne_Model_Object_BlogRoot) {
            return $node;
        }

        /** RSS proxy */
        if ($this->actionState == 'rss' && $blogObject->rssEnabled) {
            $blogObject->doAction('rss', $env);
            return $blogObject->visualize($env);
        }

        $blogPostings = $blogObject->loadListItems($env, 50, 0);

        $items = array();
        foreach ($blogPostings as $item) {
            if (!$item->thumbnailImage) {
                continue;
            }
            $items[] = array(
                'image'   => $item->thumbnailImage,
                'caption' => $item->caption,
                'path'    => $item->path,
            );
            if (count($items) >= 5) {
                break;
            }
        }

        if (!empty($items)) {
            $itemsNode = new FVISNode('SONE_OBJECT_BLOG_FRONT_CAROUSEL_ITEM', FVISNode::VISNODE_ARRAY, $env->getVIS());
            $itemsNode->addDataArray($items);
            $node->appendChild('carouselItems', $itemsNode);
        }

        foreach ($this->tags as $tag) {
            $items = array();
            foreach ($blogPostings as $item) {
                if (!$item->thumbnailImage) {
                    continue;
                }
                if (in_array($tag, $item->tags)) {
                    $items[] = array(
                        'image'   => $item->thumbnailImage,
                        'caption' => $item->caption,
                        'path'    => $item->path,
                    );
                }
                if (count($items) >= 3) {
                    break;
                }
            }

            if (!empty($items)) {
                $tagBlockNode = new FVISNode('SONE_OBJECT_BLOG_FRONT_TAG_BLOCK', 0, $env->getVIS());
                $tagBlockNode->addDataArray(array(
                    'caption' => $tag,
                    'path'    => $blogObject->path.'/tag/'.$tag,
                ));
                $itemsNode = new FVISNode('SONE_OBJECT_BLOG_FRONT_TAG_BLOCK_ITEM', FVISNode::VISNODE_ARRAY, $env->getVIS());
                $itemsNode->addDataArray($items);
                $tagBlockNode->appendChild('items', $itemsNode);
                $node->appendChild('tagBlocks', $tagBlockNode);
            }
        }

        return $node;
    }

    /**
     * @param array $data
     * @return static
     */
    public function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'blogPath' => false,
            'tags'     => array(),
        );

        $this->pool['blogPath'] =& $this->pool['data']['blogPath'];
        $this->pool['tags']     =& $this->pool['data']['tags'];

        return $this;
    }

    /**
     * @return array
     */
    public function getSubObjectsFilter()
    {
        // TODO: Implement getSubObjectsFilter() method.
    }

    /**
     * @param SOne_Model_Object[] $subObjects
     */
    public function setSubObjects(array $subObjects)
    {
        // TODO: Implement setSubObjects() method.
    }

    /**
     * @return SOne_Model_Object[]
     */
    public function getSubObjects()
    {
        // TODO: Implement getSubObjects() method.
    }
}
