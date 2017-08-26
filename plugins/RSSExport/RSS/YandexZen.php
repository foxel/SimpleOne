<?php
/**
 * Copyright (C) 2017 Andrey F. Kupreychik (Foxel)
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

class RSSExport_RSS_YandexZen extends K3_RSS
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


        $this->_xml->documentElement->setAttribute('xmlns:content', 'http://purl.org/rss/1.0/modules/content/');

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
            $this->_currentItem->appendChild($fullText = $this->_xml->createElement('content:encoded'));

            $parser = xml_parser_create(F::INTERNAL_ENCODING);
            xml_parse_into_struct($parser, $itemData->content, $xmlData, $xmlIndex);
            xml_parser_free($parser);

            $figures = Array();
            if (isset($xmlIndex['IMG'])) {
                foreach ($xmlIndex['IMG'] as $imageNodeIndex) {
                    $attributes = $xmlData[$imageNodeIndex]['attributes'];
                    $src        = $attributes['SRC'];
                    $caption    = !empty($attributes['TITLE'])
                        ? $attributes['TITLE']
                        : '';

                    $figures[] = sprintf('<figure>
<img src="%s" width="400" />
<figcaption>%s</figcaption>
</figure>', K3_Util_String::escapeXML($src), K3_Util_String::escapeXML($caption));
                }
            }

            $cleanParagraphs  = explode(PHP_EOL, strip_tags(preg_replace('#(</(p|div)>)\s*#i', PHP_EOL, $itemData->content)));
            $taggedParagraphs = array_map(function ($string) {
                return '<p>'.$string.'</p>';
            }, $cleanParagraphs);

            for ($i = 0; $i < count($figures); $i++) {
                array_splice($taggedParagraphs, $i*2 + 1, 0, Array($figures[$i]));
            }

            $fullText->appendChild($this->_xml->createCDATASection(implode(PHP_EOL, $taggedParagraphs)));
        }

        if ($itemData instanceof SOne_Model_Object_BlogItem) {
            $description = $this->_currentItem->getElementsByTagName('description')->item(0);
            $description->nodeValue = $itemData->headline ?: $itemData->caption;
        }

        return $this;
    }


    public function __destruct()
    {
    }
}
