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

class RSSExport_RSS_Yandex extends RSSExport_RSS_FullText
{
    /**
     * @param array $params
     * @param array $items
     * @param SOne_Environment $env
     * @throws FException
     */
    public function __construct(array $params, array $items = array(), SOne_Environment $env = null)
    {
        if (!isset($params['siteUrl'], $params['siteName'], $params['siteImage'])) {
            throw new FException('siteUrl, siteName, siteImage are required to produce Yandex capable RSS feed');
        }

        parent::__construct($params, array(), $env);

        $this->_xml->documentElement->setAttribute('xmlns:yandex', 'http://news.yandex.ru');
        $this->_channel->appendChild($image = $this->_xml->createElement('image'));

        $image->appendChild($this->_xml->createElement('url', K3_Util_Url::fullUrl($params['siteImage'], $this->_env)));
        $image->appendChild($this->_xml->createElement('title', $params['siteName']));
        $image->appendChild($this->_xml->createElement('link', K3_Util_Url::fullUrl($params['siteUrl'], $this->_env)));

        if (!empty($items)) {
            $this->addItems($items);
        }
    }

    /**
     * @param array|I_K3_RSS_Item|object $itemData
     * @return K3_RSS|void
     */
    public function addItem($itemData)
    {
        parent::addItem($itemData);

        $this->_currentItem->appendChild($fullText = $this->_xml->createElement('yandex:full-text'));
        $fullText->appendChild($this->_xml->createCDATASection($itemData->content));

        // cleaning categories
        // @TODO: add replacing categories with single item
        $categories = $this->_currentItem->getElementsByTagName('category');
        $i = $categories->length;
        while($categoryElement = $categories->item(--$i)) {
            $this->_currentItem->removeChild($categoryElement);
        }
    }

}
