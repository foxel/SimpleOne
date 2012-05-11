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

class SOne_Model_Object_Page404 extends SOne_Model_Object
{
    /**
     * @param K3_Environment $env
     * @return FVISNode
     */
    public function visualize(K3_Environment $env)
    {
        $env->getResponse()->setStatusCode(404);
        $node = new FVISNode('SONE_PAGE_404', 0, $env->get('VIS'));
        $node->addDataArray($this->pool);
        return $node;
    }
}

