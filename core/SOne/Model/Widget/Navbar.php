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
 * @property string $visClass
 */
class SOne_Model_Widget_Navbar extends SOne_Model_Widget
{
    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        parent::__construct($init);

        $this->setData((array) $this->pool['data']);
    }

    /**
     * @param SOne_Environment $env
     * @param SOne_Model_Object $pageObject
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env, SOne_Model_Object $pageObject = null)
    {
        $node = new FVISNode('SONE_WIDGET_NAVBAR', 0, $env->getVIS());
        $node->addDataArray($this->pool);
        if (!empty($this->data['links'])) {
            $app = $env->getApp();
            $currentPath = $app->getRequest()->path;
            foreach ($this->data['links'] as $id => $link) {
                $linkNode = new FVISNode('SONE_WIDGET_NAVBAR_LINK', 0, $env->getVIS());

                if (!is_array($link)) {
                    $link = array(
                        'caption' => $link,
                        'href'    => $id,
                    );
                } elseif (isset($link['links'])) {
                    $subLinks = array();
                    $linkNode->setType('SONE_WIDGET_NAVBAR_DROPDOWN');
                    foreach ($link['links'] as $subId => $subLink) {
                        if (!is_array($subLink)) {
                            $subLink = array(
                                'caption' => $subLink,
                                'href'    => $subId,
                            );
                        }
                        $subLink['active'] = (trim($currentPath, '/') == trim($subLink['href'], '/'));
                        $subLinks[] = $subLink;
                    }
                    unset($link['links']);
                    $subLinksNode = new FVISNode('SONE_WIDGET_NAVBAR_LINK', FVISNode::VISNODE_ARRAY, $env->getVIS());
                    $subLinksNode->addDataArray($subLinks);
                    $linkNode->appendChild('links', $subLinksNode, true);
                }
                $link['active'] = isset($link['href']) && (trim($currentPath, '/') == trim($link['href'], '/'));
                $linkNode->addDataArray($link);
                $node->appendChild('links', $linkNode, false);
            }
        }
        return $node;
    }

    /**
     * @param array $data
     * @return SOne_Model_Object_HTMLPage
     */
    protected function setData(array $data)
    {
        if (isset($data['links'])) {
            $data['links'] = (array) $data['links'];
        }
        $this->pool['data'] = $data + array(
            'fixed' => null,
            'brand' => null,
            'links' => array(),
        );
        $this->pool['fixed'] =& $this->pool['data']['fixed'];
        $this->pool['brand'] =& $this->pool['data']['brand'];
        return $this;
    }
}
