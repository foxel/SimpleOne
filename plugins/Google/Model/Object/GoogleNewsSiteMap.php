<?php
/**
 * Copyright (C) 2013 Andrey F. Kupreychik (Foxel)
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
 * @property-read int $limit
 */
class Google_Model_Object_GoogleNewsSiteMap extends SOne_Model_Object_SiteMap implements SOne_Interface_Object_Structured
{
    const NEWS_SITEMAP_NAMESPACE = 'http://www.google.com/schemas/sitemap-news/0.9';

    /**
     * @param  SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        $app = $env->getApp();
        $db = $env->getDb();

        $blogObject = SOne_Repository_Object::getInstance($db)->loadOne(array('path=' => $this->blogPath, 'class=' => 'BlogRoot'));
        $filter  = array(
            'parentId='    => $blogObject->id,
            'class='       => 'BlogItem',
            'published='   => true,
            'createTime>=' => time() - 3600*48,
        );

        /** @var $objects SOne_Model_Object_BlogItem[] */
        $objects = SOne_Repository_Object::getInstance($db)->loadAll($filter, false, $this->limit);

        $xml = new SimpleXMLElement('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:news="'.self::NEWS_SITEMAP_NAMESPACE.'" />');
        foreach ($objects as $item) {
            $urlNode = $xml->addChild('url');
            $urlNode->addChild('loc', FStr::fullUrl($item->path, true));

            $newsNode = $urlNode->addChild('news', null, self::NEWS_SITEMAP_NAMESPACE);

            $pubNode = $newsNode->addChild('publication', null, self::NEWS_SITEMAP_NAMESPACE);
            $pubNode->addChild('name', $app->getConfig()->site->name, self::NEWS_SITEMAP_NAMESPACE);
            $pubNode->addChild('language', $app->getConfig()->site->language?:'en', self::NEWS_SITEMAP_NAMESPACE);

            if ($item->accessLevel>0) {
                $newsNode->addChild('access', 'Registration', self::NEWS_SITEMAP_NAMESPACE);
            }
            $newsNode->addChild('publication_date', date('c', $item->createTime), self::NEWS_SITEMAP_NAMESPACE);
            $newsNode->addChild('title', $item->caption, self::NEWS_SITEMAP_NAMESPACE);
            //$newsNode->addChild('keywords', implode(', ', $item->getCategories()), self::NEWS_SITEMAP_NAMESPACE);
        }

        $env->response->write($xml->asXML())->sendBuffer(F::INTERNAL_ENCODING, array(
            'contentType' => 'text/xml'
        ));
    }

    /**
     * @param array $data
     * @return static
     */
    public function setData(array $data)
    {
        $this->pool['data'] = $data + array(
            'limit' => 100,
            'blogPath' => '',
        );
        $this->pool['limit']    =& $this->pool['data']['limit'];
        $this->pool['blogPath'] =& $this->pool['data']['blogPath'];
        return $this;
    }
}
