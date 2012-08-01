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

class SOne_Model_Object_SiteMap extends SOne_Model_Object
{
    /**
     * @param  array $init
     */
    public function __construct(array $init = array())
    {
        // SiteMap is always hidden
        $init['hideInTree'] = true;
        parent::__construct($init);
    }

    /**
     * @param  K3_Environment $env
     * @return FVISNode
     */
    public function visualize(K3_Environment $env)
    {
        /** @var $app SOne_Application */
        $app = $env->get('app');

        $tree = $app->getObjects()->loadObjectsTree(array('accessLevel=' => 0));

        $xml = new SimpleXMLElement('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" />');
        foreach ($tree as $item) {
            $urlNode = $xml->addChild('url');
            $locNode = $urlNode->addChild('loc', FStr::fullUrl($item->path, true));
        }

        $env->response->write($xml->asXML())->sendBuffer(F::INTERNAL_ENCODING, array(
            'contentType' => 'text/xml'
        ));
    }
}
