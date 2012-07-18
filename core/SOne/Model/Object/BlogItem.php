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
 * @property array $tags
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
        $parentPath = preg_replace('#/[^/]+$#', '', $this->path);

        $node->setType('SONE_OBJECT_BLOG_ITEM')
            ->addData('parentPath', $parentPath, true);

        if ($this->pool['tags']) {
            $tags = array();
            foreach ($this->pool['tags'] as $tag) {
                $tags[] = array(
                    'name' => $tag,
                    'parentPath' => $parentPath,
                );
            }
            $tagsNode = new FVISNode('SONE_OBJECT_BLOG_TAG', FVISNode::VISNODE_ARRAY, $env->get('VIS'));
            $tagsNode->addDataArray($tags);
            $node->appendChild('tags', $tagsNode, true);
        }

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
        $parentPath = preg_replace('#/[^/]+$#', '', $this->path);

        unset($data['comments']);

        if (preg_match('#<!--\s*pagebreak\s*-->#', $data['content'])) {
            $data['content'] = preg_replace('#(<\w+>)*<!--\s*pagebreak\s*-->.*$#', '', $data['content']);
            $data['content'] = F()->Parser->XMLCheck($data['content']);
            $data['showReadMore'] = 1;
        }

        $node->addDataArray($data + array(
            'canEdit'    => $this->isActionAllowed('edit', $env->get('user')) ? 1 : null,
            'parentPath' => $parentPath,
        ));

        if ($this->pool['tags']) {
            $tags = array();
            foreach ($this->pool['tags'] as $tag) {
                $tags[] = array(
                    'name' => $tag,
                    'parentPath' => $parentPath,
                );
            }
            $tagsNode = new FVISNode('SONE_OBJECT_BLOG_TAG', FVISNode::VISNODE_ARRAY, $env->get('VIS'));
            $tagsNode->addDataArray($tags);
            $node->appendChild('tags', $tagsNode, true);
        }

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
     * @param K3_Environment $env
     * @param bool $updated
     */
    protected function saveAction(K3_Environment $env, &$updated = false)
    {
        parent::saveAction($env, $updated);
        $this->pool['tags'] = array_unique(array_map('trim', explode(',', $env->request->getString('tags', K3_Request::POST, FStr::LINE))));
    }


    /**
     * @param FDataBase $db
     */
    public function loadExtraData(FDataBase $db)
    {
        parent::loadExtraData($db);

        /** @var $tagsRepo SOne_Repository_Tag */
        $tagsRepo = SOne_Repository_Tag::getInstance($db);
        $this->setTags($tagsRepo->getObjectTags($this->id));
    }

    /**
     * @param FDataBase $db
     */
    public function saveExtraData(FDataBase $db)
    {
        parent::saveExtraData($db);

        /** @var $tagsRepo SOne_Repository_Tag */
        $tagsRepo = SOne_Repository_Tag::getInstance($db);
        $tagsRepo->setObjectTags($this->id, $this->tags);
    }

}
