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

class Yandex_RSS extends K3_RSS
{
    /**
     * @param array $params
     * @param array $items
     * @param K3_Environment $env
     * @throws FException
     */
    public function __construct(array $params, array $items = array(), K3_Environment $env = null)
    {
        if (!isset($params['siteUrl'], $params['siteName'], $params['siteImage'])) {
            throw new FException('siteUrl, siteName, siteImage are required to produce Yandex capable RSS feed');
        }

        parent::__construct($params, array(), $env);

        $this->_xml->documentElement->setAttribute('xmlns:yandex', 'http://news.yandex.ru');
        $this->_channel->appendChild($image = $this->_xml->createElement('image'));

        $image->appendChild($this->_xml->createElement('url', FStr::fullUrl($params['siteImage'], false, '', $this->_env)));
        $image->appendChild($this->_xml->createElement('title', $params['siteName']));
        $image->appendChild($this->_xml->createElement('link', FStr::fullUrl($params['siteUrl'], false, '', $this->_env)));

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

        $this->_currentItem->appendChild($description = $this->_xml->createElement('yandex:full-text'));
        $description->appendChild($this->_xml->createCDATASection($itemData->getDescription()));

        // cleaning categories
        // @TODO: add replacing categories with single item
        $categories = $this->_currentItem->getElementsByTagName('category');
        $i = $categories->length;
        while($categoryElement = $categories->item(--$i)) {
            $this->_currentItem->removeChild($categoryElement);
        }
    }

}