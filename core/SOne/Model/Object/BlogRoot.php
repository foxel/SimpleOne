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

class SOne_Model_Object_BlogRoot extends SOne_Model_Object
        implements SOne_Interface_Object_WithExtraData, SOne_Interface_Object_WithSubRoute
{
    /**
     * @var array
     */
    protected $aclEditActionsList = array('edit', 'save', 'new');

    /** @var FDataBase */
    protected $_db;
    protected $_filterParams = null;
    protected $_itemPerPage  = 10;

    /**
     * @param  K3_Environment $env
     * @return FVISNode
     */
    public function visualize(K3_Environment $env)
    {
        if ($this->id && $this->actionState == 'rss')
        {
            $items   = $this->_loadListItems($env, $this->_itemPerPage);
            foreach ($items as $item) {
                $item->fixFullUrls($env);
            }

            $rss = new K3_RSS(array(
                'title' => $this->caption,
                'link'  => FStr::fullUrl($this->path),
                'feedLink' => FStr::fullUrl($this->path).'?rss',
            ), $items, $env);

            $env->getResponse()
                ->setDoHTMLParse(false)
                ->write($rss->toXML())
                ->sendBuffer(F::INTERNAL_ENCODING, array(
                    'contentType' => 'text/xml',
                    'filename' => FStr::basename($this->path).'.xml',
                ));
        }

        /** @var $vis FVISInterface */
        $vis = $env->get('VIS');

        $node = new FVISNode('SONE_OBJECT_BLOG_LIST', 0, $vis);

        $node->addDataArray($this->pool + array(
            'canAddItem' => $this->isActionAllowed('new', $env->get('user')) ? 1 : null,
        ));

        $currentPath = $this->path;
        if ($this->_filterParams) {
            foreach ($this->_filterParams as $key => $param) {
                $currentPath .= '/'.urlencode($key).'/'.urlencode($param);
                if ($key == 'author') {
                    $param = reset(SOne_Repository_User::getInstance($env->get('db'))->loadNames(array('id' => (int)$param)));
                }
                $node->addData('filter_'.$key, $param, true);
            }
        }

        if ($this->id && !in_array($this->actionState, array('new', 'edit'))) {
            $curPage = max((int) $env->request->getNumber('page'), 1);
            $items   = $this->_loadListItems($env, $this->_itemPerPage, $curPage - 1, $totalItems);
            $rootNode = $vis->getRootNode();
            $rootNode->addData('META', sprintf(
                '<link rel="alternate" type="application/rss+xml" title="%s" href="%s?rss" />',
                FStr::htmlschars($this->caption),
                FStr::fullUrl($this->path)
            ));

            foreach ($items as $item) {
                $node->appendChild('items', $item->visualizeForList($env));
            }
            $node->addData('totalItems', $totalItems, true)
                ->addData('itemsCount', count($items));

            $totalPages = ceil($totalItems/$this->_itemPerPage);
            if ($totalPages > 1) {
                $paginator = new SOne_VIS_Paginator(array(
                    'objectPath'  => $currentPath,
                    'totalPages'  => $totalPages,
                    'currentPage' => $curPage,
                    'actionState' => $this->actionState,
                ));
                $node->appendChild('paginator', $paginator->visualize($env));
            }
        }

        if ($this->actionState == 'new') {
            $node->addData('newPath', $this->path.'/'.FStr::shortUID());
            $allTags = SOne_Repository_Tag::getInstance($this->_db)->loadNames();
            $node->addData('allTagsJson', json_encode($allTags));
        }

        return $node;
    }

    /**
     * @param K3_Environment $env
     * @param int $perPage
     * @param int $pageOffset
     * @param null $totalItems
     * @return \SOne_Model_Object_BlogItem[]
     */
    protected function _loadListItems(K3_Environment $env, $perPage = 10, $pageOffset = 0, &$totalItems = null)
    {
        if (!$this->id) {
            return array();
        }

        $repo = SOne_Repository_Object::getInstance($this->_db);
        $filter = array(
            'parentId' => $this->id,
            'class'    => 'BlogItem',
        );
        /** @var $lang FLNGData */
        $lang = $env->get('lang');
        if ($this->_filterParams) {
            foreach ($this->_filterParams as $filterType => $filterValue) {
                switch ($filterType) {
                    case 'date':
                        if (preg_match('#^\d{4}(-\d{2}){0,2}$#', $filterValue)) {
                            $dateParts = explode('-', $filterValue);
                            if (count($dateParts) == 3) {
                                $filter['createTime>='] = gmmktime(0, 0, 0, $dateParts[1], $dateParts[2], $dateParts[0]) - $lang->timeZone*3600;
                                $filter['createTime<='] = gmmktime(23, 59, 59, $dateParts[1], $dateParts[2], $dateParts[0]) - $lang->timeZone*3600;
                            } elseif (count($dateParts) == 2) {
                                $filter['createTime>='] = gmmktime(0, 0, 0, $dateParts[1], 1, $dateParts[0]) - $lang->timeZone*3600;
                                $filter['createTime<='] = gmmktime(23, 59, 59, $dateParts[1]+1, 0, $dateParts[0]) - $lang->timeZone*3600;
                            } else {
                                $filter['createTime>='] = gmmktime(0, 0, 0, 1, 1, $dateParts[0]) - $lang->timeZone*3600;
                                $filter['createTime<='] = gmmktime(23, 59, 59, 12, 31, $dateParts[0]) - $lang->timeZone*3600;
                            }
                        }
                        break;
                    case 'tag':
                        $filter['id='] = SOne_Repository_Tag::getInstance($this->_db)->getObjectIdsByTags($filterValue, true);
                        break;
                    case 'author':
                        $filter['ownerId='] = (int)$filterValue;
                        break;
                }
            }
        }

        $items = $repo->loadAll($filter, false, $perPage, $pageOffset*$perPage, $totalItems);

        $userIds = array();
        foreach ($items as $item) {
            $userIds[] = $item->ownerId;
        }

        /** @var $usersRepo SOne_Repository_User */
        $usersRepo = SOne_Repository_User::getInstance($this->_db);
        $usersRepo->prepareFetch(array('id=' => array_unique($userIds)));

        return $items;
    }

    /**
     * @param FDataBase $db
     */
    public function loadExtraData(FDataBase $db)
    {
        // we'll grab db adapter for future
        $this->_db = $db;
    }

    /**
     * @param FDataBase $db
     */
    public function saveExtraData(FDataBase $db)
    {
        // we'll grab db adapter for future
        $this->_db = $db;
    }

    /**
     * @param string $subPath
     * @param SOne_Request $request
     * @param K3_Environment $env
     * @return SOne_Model_Object
     */
    public function routeSubPath($subPath, SOne_Request $request, K3_Environment $env)
    {
        if ($request->action == 'save' && preg_match('#^[0-9a-z]+$#', $subPath)) {
            $object = SOne_Model_Object::construct(array(
                'class'       => 'BlogItem',
                'parentId'    => $this->id,
                'accessLevel' => $this->accessLevel,
                'editLevel'   => $this->editLevel,
                'ownerId'     => $env->get('user')->id,
                'path'        => $this->path.'/'.$subPath,
                'hideInTree'  => true,
            ));
            return $object;
        } else {
            $this->pool['actionState'] = '';
        }

        $this->_filterParams = FStr::getZendStyleURLParams($subPath);
        return $this;
    }
}
