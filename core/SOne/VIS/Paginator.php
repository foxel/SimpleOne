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
 * @property string $objectPath
 * @property int $totalPages
 * @property int $currentPage
 * @property string $pageVarName
 * @property string $actionState
 * @property string $fragment
 * @property string[] $urlParams
 */
class SOne_VIS_Paginator extends FBaseClass
{
    const WIDTH = 2;

    /**
     * @param array $init
     */
    public function __construct(array $init = array())
    {
        $this->pool = array(
            'objectPath'  => isset($init['objectPath'])  ? (string)$init['objectPath'] : '/',
            'totalPages'  => isset($init['totalPages'])  ? (int)$init['totalPages'] : 1,
            'currentPage' => isset($init['currentPage']) ? (int)$init['currentPage'] : 1,
            'pageVarName' => isset($init['pageVarName']) ? (string)$init['pageVarName'] : 'page',
            'actionState' => isset($init['actionState']) ? (string)$init['actionState'] : null,
            'fragment'    => isset($init['fragment'])    ? (string)$init['fragment'] : null,
            'urlParams'   => array(),
        );
    }

    /**
     * @param SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        $vis = $env->getVIS();

        $container = new FVISNode('SONE_WIDGET_PAGINATOR', 0, $vis);

        $container->appendChild('links', $pagesNode = new FVISNode('SONE_WIDGET_PAGINATOR_LINK', FVISNode::VISNODE_ARRAY, $vis));
        $pages     = array();
        $urlParams = $this->urlParams
            ? http_build_query($this->urlParams, null, '&amp;')
            : null;

        $collapse = false;
        if ($this->totalPages > 5 + self::WIDTH*2) {
            $minVisiblePage = min($this->totalPages - self::WIDTH - 2, $this->currentPage) - self::WIDTH;
            $maxVisiblePage = max(self::WIDTH + 2, $this->currentPage) + self::WIDTH;
            if ($minVisiblePage == 3) {
                $minVisiblePage = 1;
            }
            if ($maxVisiblePage == $this->totalPages - 2) {
                $maxVisiblePage = $this->totalPages;
            }
        } else {
            $minVisiblePage = 1;
            $maxVisiblePage = $this->totalPages;
        }

        for ($i = 1; $i <= $this->totalPages; $i++) {
            if ($i == 1 || $i == $this->totalPages || ($i >= $minVisiblePage && $i <= $maxVisiblePage)) {
                $pages[] = array(
                    'objectPath'  => $this->objectPath,
                    'pageVarName' => $this->pageVarName,
                    'urlParams'   => $urlParams,
                    'page'        => $i,
                    'current'     => ($i == $this->currentPage) ? 1 : null,
                );
                $collapse = false;
            } elseif(!$collapse) {
                $pages[] = array();
                $collapse = true;
            }
        }

        $pagesNode->addDataArray($pages);

        return $container;
    }
}
