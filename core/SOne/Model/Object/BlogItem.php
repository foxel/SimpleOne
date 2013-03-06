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
 * @property array $tags
 * @property string $thumbnailImage
 */
class SOne_Model_Object_BlogItem extends SOne_Model_Object_PlainPage
    implements SOne_Interface_Object_WithExtraData
{
    /** @var SOne_Repository_Tag */
    protected $_tagsRepo;
    
    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        parent::__construct($init);
        $this->pool['tags'] = array();
    }

    /**
     * @param  SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        $parentPath = preg_replace('#/[^/]+$#', '', $this->path);

        if ($this->actionState == 'delete') {
            $env->response->sendRedirect($parentPath);
        }

        $node = parent::visualize($env);
        $node->setType('SONE_OBJECT_BLOG_ITEM')
            ->addData('canSetPubTime', !$this->id || $this->createTime > time(), true)
            ->addData('parentPath', $parentPath, true);

        if ($this->pool['tags']) {
            if ($this->actionState == 'edit') {
                $node->addData('tags', implode(', ', $this->pool['tags']), true);
            } else {
                $tags = array();
                foreach ($this->pool['tags'] as $tag) {
                    $tags[] = array(
                        'name' => $tag,
                        'parentPath' => $parentPath,
                    );
                }
                $tagsNode = new FVISNode('SONE_OBJECT_BLOG_TAG', FVISNode::VISNODE_ARRAY, $env->getVIS());
                $tagsNode->addDataArray($tags);
                $node->appendChild('tags', $tagsNode, true);
            }
        }

        if ($this->id && $this->actionState == 'edit') {
            $allTags = $this->_tagsRepo->loadNames();
            $node->addData('allTagsJson', json_encode($allTags));
        } else {
            $user = SOne_Repository_User::getInstance($env->getDb())->get($this->ownerId);
            $node->addNode('SONE_OBJECT_BLOG_USERTAG', 'userTag', $user->toArray() + array(
                'parentPath' => $parentPath,
            ));
        }

        return $node;
    }

    /**
     * @param SOne_Environment $env
     * @return FVISNode
     */
    public function visualizeForList(SOne_Environment $env)
    {
        $node = new FVISNode('SONE_OBJECT_BLOG_LISTITEM', 0, $env->getVIS());
        $data = $this->pool;
        $parentPath = preg_replace('#/[^/]+$#', '', $this->path);

        unset($data['comments']);

        if (preg_match('#<!--\s*pagebreak\s*-->#', $data['content'])) {
            $data['content'] = preg_replace('#(<\w+>)*<!--\s*pagebreak\s*-->.*$#Ds', '', $data['content']);
            $data['content'] = F()->Parser->XMLCheck($data['content'], true);
            $data['showReadMore'] = 1;
        }

        $node->addDataArray($data + array(
            'canEdit'    => $this->isActionAllowed('edit', $env->getUser()) ? 1 : null,
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
            $tagsNode = new FVISNode('SONE_OBJECT_BLOG_TAG', FVISNode::VISNODE_ARRAY, $env->getVIS());
            $tagsNode->addDataArray($tags);
            $node->appendChild('tags', $tagsNode, true);
        }

        $user = SOne_Repository_User::getInstance($env->getDb())->get($this->ownerId);
        $node->addNode('SONE_OBJECT_BLOG_USERTAG', 'userTag', $user->toArray() + array(
            'parentPath' => $parentPath,
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
     * @param SOne_Environment $env
     * @param bool $updated
     */
    protected function saveAction(SOne_Environment $env, &$updated = false)
    {
        parent::saveAction($env, $updated);
        $this->pool['tags'] = array_unique(array_map('trim', explode(',', $env->request->getString('tags', K3_Request::POST, FStr::LINE))));
        /** @var $user SOne_Model_User */
        $user = $env->getUser();

        $imageSrc = '';
        $dom = new K3_DOM('4.0', F::INTERNAL_ENCODING);
        if ($dom->loadHTML(mb_convert_encoding($this->content, 'HTML-ENTITIES', F::INTERNAL_ENCODING))) {
            $dom->encoding = F::INTERNAL_ENCODING;
            if (!$user->adminLevel) {
                $dom->stripXSSVulnerableCode();
            }
            // $dom->fixFullUrls();
            $images = $dom->getElementsByTagName('img');
            if ($images->length) {
                /** @var $thumbImage DOMElement */
                $thumbImage = $images->item(0);
                $imageSrc = $thumbImage->getAttribute('src');
            }
            $content = $dom->saveXML($dom->getElementsByTagName('body')->item(0));
            $this->content = str_replace(array('<body>', '</body>', '&#13;'), '', $content);
        } else {
            $this->content = '';
        }

        if (!$this->id || $this->createTime > time()) {
            $pubTime = $env->request->getString('pubTime', K3_Request::POST, FStr::LINE);
            $this->pool['createTime'] = max(strtotime($pubTime), time());
        }

        $this->pool['thumbnailImage'] = (string) $imageSrc;
    }

    /**
     * @param SOne_Environment $env
     * @param bool $updated
     */
    protected function deleteAction(SOne_Environment $env, &$updated = false)
    {
        $db = $env->getDb();
        $objects = SOne_Repository_Object::getInstance($db);
        $objects->delete($this->id);
        $updated = false;
    }

    /**
     * @param array $data
     * @return SOne_Model_Object_BlogItem
     */
    public function setData(array $data)
    {
        parent::setData($data);
        $this->pool['data']['thumbnailImage'] = isset($data['thumbnailImage'])
            ? (string) $data['thumbnailImage']
            : '';

        $this->pool['thumbnailImage'] =& $this->pool['data']['thumbnailImage'];

        return $this;
    }

    /**
     * @param FDataBase $db
     */
    public function loadExtraData(FDataBase $db)
    {
        parent::loadExtraData($db);

        $this->_tagsRepo = SOne_Repository_Tag::getInstance($db);
        $this->setTags($this->_tagsRepo->getObjectTags($this->id));
    }

    /**
     * @param FDataBase $db
     */
    public function saveExtraData(FDataBase $db)
    {
        parent::saveExtraData($db);

        $this->_tagsRepo = SOne_Repository_Tag::getInstance($db);
        $this->_tagsRepo->setObjectTags($this->id, $this->tags);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->content;
    }

    /**
     * @return string[]
     */
    public function getCategories()
    {
        return $this->tags;
    }

    public function getEnclosures()
    {
        if ($this->thumbnailImage) {
            return array(
                new K3_RSS_Item_Enclosure(array(
                    'type' => F()->Mime->getMime($this->thumbnailImage, true),
                    'url' => $this->thumbnailImage,
                    'length' => null,
                ))
            );
        }

        return array();
    }

    /**
     * @param SOne_Environment $env
     * @return SOne_Model_Object_BlogItem
     */
    public function fixFullUrls(SOne_Environment $env)
    {
        if ($this->thumbnailImage) {
            $this->pool['thumbnailImage'] = FStr::fullUrl($this->thumbnailImage, false, '', $env);
        }

        $this->pool['content'] = SOne_Tools::getInstance($env)->HTML_FullURLs($this->content);

        return $this;
    }
}
