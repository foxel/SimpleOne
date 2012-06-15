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

class SOne_Model_Object_BlogItem extends SOne_Model_Object_PlainPage
    implements SOne_Interface_Object_WithExtraData
{
    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        parent::__construct($init);
        $this->pool['tags'] = array();
    }

    /**
     * @param  K3_Environment $env
     * @return FVISNode
     */
    public function visualize(K3_Environment $env)
    {
        $node = parent::visualize($env);

        $node->setType('SONE_OBJECT_BLOG_ITEM');

        return $node;
    }

    /**
     * @param K3_Environment $env
     * @return FVISNode
     */
    public function visualizeForList(K3_Environment $env)
    {
        $node = new FVISNode('SONE_OBJECT_BLOG_LISTITEM', 0, $env->get('VIS'));
        $data = $this->pool;
        unset($data['comments']);
        $node->addDataArray($data + array(
            'canEdit'       => $this->isActionAllowed('edit', $env->get('user')) ? 1 : null,
            'parentPath' => preg_replace('#/[^/]+$#', '', $this->path),
        ));

        return $node;
    }

    /**
     * @param string[] $tags
     * @return SOne_Model_Object_BlogItem
     */
    public function setTags(array $tags)
    {
        $this->pool['tags'] = $tags;

        return $this;
    }

    /**
     * @param FDataBase $db
     */
    public function loadExtraData(FDataBase $db)
    {
        parent::loadExtraData($db);
        // todo: load tags
    }

    /**
     * @param FDataBase $db
     */
    public function saveExtraData(FDataBase $db)
    {
        parent::saveExtraData($db);
        // todo: save tags
    }

}