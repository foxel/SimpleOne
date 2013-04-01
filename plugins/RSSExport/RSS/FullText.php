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

class RSSExport_RSS_FullText extends K3_RSS
{

    /**
     * @param array|I_K3_RSS_Item|object $itemData
     * @return K3_RSS|void
     */
    public function addItem($itemData)
    {
        parent::addItem($itemData);

        $description = $this->_currentItem->getElementsByTagName('description')->item(0);
        while ($child = $description->firstChild) {
            $description->removeChild($description->firstChild);
        }
        $description->appendChild($this->_xml->createCDATASection($itemData->content));
    }

}
