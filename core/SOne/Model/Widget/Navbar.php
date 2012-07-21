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
     * @param K3_Environment $env
     * @param SOne_Model_Object $pageObject
     * @return FVISNode
     */
    public function visualize(K3_Environment $env, SOne_Model_Object $pageObject = null)
    {
        $node = new FVISNode('SONE_WIDGET_NAVBAR', 0, $env->get('VIS'));
        $node->addDataArray($this->pool);
        if (!empty($this->data['links'])) {
            /** @var $app SOne_Application */
            $app = $env->get('app');
            $currentPath = $app->getRequest()->path;
            $links = array();
            foreach ($this->data['links'] as $id => $link) {
                if (!is_array($link)) {
                    $link = array(
                        'caption' => $link,
                        'href'    => $id,
                    );
                } else {
                    $link += array(
                        'caption' => $id,
                        'href'    => $id,
                    );
                }
                $link['active'] = (FStr::isUrl($link['href']) == 2) && (strcasecmp(trim($currentPath, '/').'/', trim($link['href'], '/').'/') == 0);
                $links[] = $link;
            }

            $linksNode = new FVISNode('SONE_WIDGET_NAVBAR_LINK', FVISNode::VISNODE_ARRAY, $env->get('VIS'));
            $linksNode->addDataArray($links);
            $node->appendChild('links', $linksNode, true);
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
