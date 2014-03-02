<?php
/**
 * Copyright (C) 2014 Andrey F. Kupreychik (Foxel)
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
 * @property-read int[] $blogIds
 */
class SOne_Model_Object_BlogMerge extends SOne_Model_Object_BlogRoot
{
    /**
     * @param SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        if ($this->actionState == 'new') {
            $env->response->sendRedirect($this->path);
        }

        $node = parent::visualize($env);

        $node->setType('SONE_OBJECT_BLOG_MERGE');
        $node->addData('canAddItem', null, true);

        if ($this->actionState == 'edit') {
            $roots = SOne_Repository_Object::getInstance($env->getDb())->loadAll(array(
                'class=' => 'BlogRoot',
            ));

            $blogOptions = array();
            foreach ($roots as $item) {
                if (!$item instanceof SOne_Model_Object_BlogRoot) {
                    continue;
                }
                $blogOptions[] = array(
                    'value'    => $item->id,
                    'caption'  => $item->caption,
                    'selected' => in_array($item->id, $this->blogIds) ? 1 : null,
                );
            }

            if ($blogOptions) {
                $node->appendChild('blogOptions', $optionsNode = new FVISNode('SONE_HTML_WIDGET_FORM_SELECT_OPTION', FVISNode::VISNODE_ARRAY, $env->getVIS()));
                $optionsNode->addDataArray($blogOptions);
            }
        }

        return $node;
    }

    /**
     * @param SOne_Environment $env
     * @return array
     */
    public function _prepareFilter(SOne_Environment $env)
    {
        $filter = parent::_prepareFilter($env);

        $filter['parentId='] = $this->blogIds;

        return $filter;
    }

    /**
     * @param string $subPath
     * @param SOne_Request $request
     * @param SOne_Environment $env
     * @return SOne_Model_Object
     */
    public function routeSubPath($subPath, SOne_Request $request, SOne_Environment $env)
    {
        $this->pool['actionState'] = '';
        $this->_filterParams = FStr::getZendStyleURLParams($subPath);
        $this->_subPath = $subPath;
        if (array_diff(array_keys($this->_filterParams), array('date', 'tag', 'author'))) {
            return new SOne_Model_Object_Page404(array('path' => $this->path.'/'.$subPath));
        }

        return $this;
    }

    /**
     * @param SOne_Environment $env
     * @param bool $updated
     */
    protected function saveAction(SOne_Environment $env, &$updated = false)
    {
        parent::saveAction($env, $updated);
        $this->pool['blogIds'] = (array) $env->request->get('blogIds', K3_Request::POST, array());

        $updated = true;
    }

    /**
     * @param array $data
     * @return SOne_Model_Object_HTMLPage
     */
    public function setData(array $data)
    {
        parent::setData($data);
        $this->pool['data'] += array(
            'blogIds' => array(),
        );

        $this->pool['blogIds'] =& $this->pool['data']['blogIds'];

        return $this;
    }
}
