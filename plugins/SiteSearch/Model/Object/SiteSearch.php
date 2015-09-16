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
 * @property-read int $limit
 */
class SiteSearch_Model_Object_SiteSearch extends SOne_Model_Object
    implements SOne_Interface_Object_Structured
{
    /** @var int */
    protected $_itemPerPage  = 10;

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
            $curPage = max((int) $env->request->getNumber('page'), 1);

            $data = json_decode($plugin->search($query, $this->_itemPerPage, ($curPage - 1)*$this->_itemPerPage), true);

            if (isset($data['hits']['hits'])) {
                $index = 1;
                $totalItems = (int) $data['hits']['total'];

                $items = array_map(function($item) use(&$index) {
                    $data = isset($item['highlight'])
                        ? $item['highlight']
                        : array();
                    $data += $item['fields'];

                    $data = array_map(function($value) {
                        if (is_array($value)) {
                            $value = reset($value);
                        }
                        return (string) $value;
                    }, $data);

                    $data['index'] = $index++;

                    if (isset($data['createTime'])) {
                        $data['createTime'] = strtotime($data['createTime']);
                    }

                    return $data;
                }, (array) $data['hits']['hits']);

                if (!empty($items)) {
                    $node->appendChild('items', $contNode = new FVISNode('SONE_OBJECT_SITESEARCH_ITEM', FVISNode::VISNODE_ARRAY, $env->getVIS()));
                    $contNode->addDataArray($items);

                    $totalPages = ceil($totalItems/$this->_itemPerPage);
                    if ($totalPages > 1) {
                        $paginator = new SOne_VIS_Paginator(array(
                            'objectPath'  => $this->path,
                            'urlParams'   => array('q' => $query),
                            'totalPages'  => $totalPages,
                            'currentPage' => $curPage,
                            'actionState' => $this->actionState,
                        ));
                        $node->appendChild('paginator', $paginator->visualize($env));
                    }
                }
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
            'limit'    => 20,
        );
        $this->pool['limit']    =& $this->pool['data']['limit'];
        return $this;
    }
}
