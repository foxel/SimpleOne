<?php
/**
 * Copyright (C) 2012 - 2014 Andrey F. Kupreychik (Foxel)
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
 * @property-read string|string[] $authKey
 */
class RSSExport_Model_Object_BlogRSS extends SOne_Model_Object
    implements SOne_Interface_Object_Structured
{
    protected $_itemPerPage = 10;
    protected $_modeClasses = array(
        'yandex'  => 'RSSExport_RSS_Yandex',
        'rambler' => 'RSSExport_RSS_Rambler',
    );

    /**
     * @param  SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        $items = $this->_loadListItems($env, $this->_itemPerPage);
        foreach ($items as $item) {
            $item->fixFullUrls($env);
        }

        if ($this->authKey) {
            $authKey = $env->request->getString('authKey', K3_Request::ALL);
            if (!$authKey) {
                $authKey = $env->request->getString('authkey', K3_Request::ALL);
            }
            $correct = is_array($this->authKey)
                ? in_array($authKey, $this->authKey)
                : $authKey == $this->authKey;

            if (!$correct) {
                $env->getResponse()
                    ->setDoHTMLParse(false)
                    ->write('Forbidden')
                    ->setStatusCode(403)
                    ->sendBuffer(F::INTERNAL_ENCODING, array(
                        'contentType' => 'text/plain'
                    ));
                return;
            }
        }

        $env->getResponse()
            ->setDoHTMLParse(false)
            ->write($this->_prepareRSS($env, $items)->toXML())
            ->sendBuffer(F::INTERNAL_ENCODING, array(
                'contentType' => 'text/xml',
                'filename' => K3_Util_File::basename($this->path).'.xml',
            ));
    }

    /**
     * @param SOne_Environment $env
     * @param SOne_Model_Object_BlogItem[] $items
     * @return K3_RSS
     */
    protected function _prepareRSS(SOne_Environment $env, array $items)
    {
        $app = $env->getApp();
        $appConfig = $app->getConfig();

        $mode = $this->actionState;
        if (isset($this->_modeClasses[$mode])) {
            $class = $this->_modeClasses[$mode];
        } else {
            $class = 'RSSExport_RSS_FullText';
        }

        return new $class(array_merge((array) $this->data, array(
            'title'     => $this->caption,
            'link'      => K3_Util_Url::fullUrl($this->blogPath, $env),
            'feedLink'  => K3_Util_Url::fullUrl($this->path, $env),
            'siteUrl'   => K3_Util_Url::fullUrl('/', $env),
            'siteName'  => $appConfig->site ? (string) $appConfig->site->name : 'SimpleOne',
            'siteImage' => $this->imageUrl ? $this->imageUrl : '/static/images/sone.ico.png',
        )), $items, $env);
    }

    /**
     * @param SOne_Environment $env
     * @param int $perPage
     * @param int $pageOffset
     * @param null $totalItems
     * @return \SOne_Model_Object_BlogItem[]
     */
    protected function _loadListItems(SOne_Environment $env, $perPage = 10, $pageOffset = 0, &$totalItems = null)
    {
        if (!$this->blogPath) {
            return array();
        }

        /** @var $blogObject SOne_Model_Object_BlogRoot */
        $blogObject = SOne_Repository_Object::getInstance($env->db)->loadOne(array(
            'path=' => $this->blogPath,
        ));

        if (!$blogObject instanceof SOne_Model_Object_BlogRoot) {
            return array();
        }

        $items = $blogObject->loadListItems($env, $perPage, $pageOffset*$perPage, $totalItems);

        $userIds = array();
        foreach ($items as $item) {
            $userIds[] = $item->ownerId;
        }

        /** @var $usersRepo SOne_Repository_User */
        $usersRepo = SOne_Repository_User::getInstance($env->getDb());
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
            'imageUrl' => null,
            'authKey'  => null,
        );

        $this->pool['blogPath'] =& $this->pool['data']['blogPath'];
        $this->pool['imageUrl'] =& $this->pool['data']['imageUrl'];
        $this->pool['authKey']  =& $this->pool['data']['authKey'];

        return $this;
    }
}
