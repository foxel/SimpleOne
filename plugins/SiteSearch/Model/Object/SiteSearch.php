<?php
/**
 * Copyright (C) 2015 Andrey F. Kupreychik (Foxel)
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
 * Class SiteSearch_Model_Object_SiteSearch
 */
class SiteSearch_Model_Object_SiteSearch extends SOne_Model_Object
{
    /**
     * @param  SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        $env->app->requirePlugins(array('SiteSearch'));
        $plugin = SiteSearch_Bootstrap::getPluginInstance();

        $query = $env->request->getString('q');

        $node = new FVISNode('SONE_OBJECT_SITESEARCH', 0, $env->getVIS());
        $node->addDataArray($this->pool + array(
            'query'    => $query,
        ));

        if ($query) {
            $data = json_decode($plugin->search($query), true);
            $items = array_map(function($item) {
                return ($item['highlight'] ?: array())
                    + $item['fields'];
            }, (array) $data['hits']['hits']);

            if (!empty($items)) {
                $node->appendChild('items', $contNode = new FVISNode('SONE_OBJECT_SITESEARCH_ITEM', FVISNode::VISNODE_ARRAY, $env->getVIS()));
                $contNode->addDataArray($items);
            }
        }


        return $node;
    }
}
