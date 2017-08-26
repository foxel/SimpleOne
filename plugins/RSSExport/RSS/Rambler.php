<?php
/**
 * Copyright (C) 2012 - 2013, 2017 Andrey F. Kupreychik (Foxel)
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

class RSSExport_RSS_Rambler extends K3_RSS
{
    /**
     * @param array $params
     * @param array $items
     * @param SOne_Environment $env
     * @throws FException
     */
    public function __construct(array $params, array $items = array(), SOne_Environment $env = null)
    {
        parent::__construct($params, array(), $env);

        $this->_xml->documentElement->setAttribute('xmlns:rambler', 'http://news.rambler.ru');

        if (!empty($items)) {
            $this->addItems($items);
        }
    }

    /**
     * @param array|I_K3_RSS_Item|object $itemData
     * @return $this
     */
    public function addItem($itemData)
    {
        parent::addItem($itemData);

        if ($itemData instanceof SOne_Model_Object_PlainPage) {
            $this->_currentItem->appendChild($fullText = $this->_xml->createElement('rambler:fulltext'));
            $fullText->appendChild($this->_xml->createCDATASection(strip_tags(preg_replace('#(</(p|div)>)\s*#i', '$1'.PHP_EOL.PHP_EOL, $itemData->content))));
        }

        return $this;
    }

}
