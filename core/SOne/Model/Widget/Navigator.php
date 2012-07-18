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

class SOne_Model_Widget_Navigator extends SOne_Model_Widget
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
        /** @var $app SOne_Application */
        $app = $env->get('app');
        /** @var $config  */
        $config = $app->getConfig();

        $currentPath = $pageObject->path;

        $tree = $app->getObjects()->loadObjectsTreeByPath($currentPath, true);
        // loading static routes
        if ($staticRoutes = $config->staticRoutes) {
            $staticRoutes = $staticRoutes->toArray();
            foreach ($staticRoutes as $path => $data) {
                // nodes with no caption or hidden nodes is not included
                if (!is_array($data) || !isset($data['caption']) || (isset($data['hideInTree']) && $data['hideInTree'])) {
                    continue;
                }

                // sub nodes are hidden for now
                if (substr_count($path, '/')) {
                    continue;
                }

                $data['path']      = $path;
                $data['isStatic']  = true;
                $data['treeLevel'] = 1;
                $tree[]            = SOne_Model_Object::construct($data);
            }
        }

        $container = new FVISNode('NAVIGATOR_BLOCK', 0, $vis);
        /** @var $parents FVISNode[] */
        $parents = array($container, $container);

        if (is_array($tree)) {
            foreach ($tree as $item) {
                if ($item->hideInTree || $item->accessLevel > $env->get('user')->accessLevel || !$parents[$item->treeLevel]) {
                    $parents[$item->treeLevel + 1] = null;
                    continue;
                }

                $node       = new FVISNode('NAVIGATOR_ITEM', 0, $vis);
                $parentNode = $parents[$item->treeLevel];
                if ($isActive = (strpos(trim($currentPath, '/').'/', trim($item->path, '/').'/') === 0)) {
                    $parentNode->addData('isCurrent', null, true);
                }
                $node->addDataArray(array(
                    'href'         => FStr::fullUrl(ltrim($item->path, '/')),
                    'caption'      => $item->caption,
                    'shortCaption' => FStr::smartTrim($item->caption, 23 - $item->treeLevel),
                    'isCurrent'    => $isActive ? 1 : null,
                ));
                $parentNode->appendChild('subs', $node);
                $parents[$item->treeLevel + 1] = $node;
            }
        }

        return $container;
    }
}
