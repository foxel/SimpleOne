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
 * @property-read string $blogPath
 * @property-read string $imageUrl
 */
class Yandex_Model_Object_YandexRSS extends SOne_Model_Object
    implements SOne_Interface_Object_Structured
{
    /** @var FDataBase */
    protected $_db;
    protected $_itemPerPage = 10;

    /**
     * @param  K3_Environment $env
     * @return FVISNode
     */
    public function visualize(K3_Environment $env)
    {
        $this->_db = $env->get('db');
        /** @var $app SOne_Application */
        $app = $env->get('app');
        $appConfig = $app->getConfig();
        $yandexConfig = Yandex_Bootstrap::getConfig();

        $items   = $this->_loadListItems($env, $this->_itemPerPage);
        foreach ($items as $item) {
            $item->fixFullUrls($env);
        }

        $rss = new Yandex_RSS(array(
            'title'     => $this->caption,
            'link'      => FStr::fullUrl($this->path, false, '', $env),
            'feedLink'  => FStr::fullUrl($this->path.'?rss', false, '', $env),
            'siteUrl'   => FStr::fullUrl('/', false, '', $env),
            'siteName'  => $appConfig->site ? (string) $appConfig->site->name : 'SimpleOne',
            'siteImage' => $this->imageUrl ? $this->imageUrl : '/static/images/sone.ico.png',
        ), $items, $env);

        $env->getResponse()
            ->setDoHTMLParse(false)
            ->write($rss->toXML())
            ->sendBuffer(F::INTERNAL_ENCODING, array(
                'contentType' => 'text/xml',
                'filename' => FStr::basename($this->path).'.xml',
            ));
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
        if (!$this->blogPath) {
            return array();
        }

        /** @var $repo SOne_Repository_Object */
        $repo = SOne_Repository_Object::getInstance($this->_db);
        $filter = array(
            'parentId=' => $repo->loadIds(array('path=' => $this->blogPath), true),
            'class='    => 'BlogItem',
        );

        $items = $repo->loadAll($filter, false, $perPage, $pageOffset * $perPage, $totalItems);

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
     * @param array $data
     * @return static
     */
    public function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'blogPath' => '',
            'imageUrl' => null
        );

        $this->pool['blogPath'] =& $this->pool['data']['blogPath'];
        $this->pool['imageUrl'] =& $this->pool['data']['imageUrl'];

        return $this;
    }
}
