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
     * @param  SOne_Environment $env
     * @return FVISNode
     */
    public function visualize(SOne_Environment $env)
    {
        $app = $env->getApp();

        $paths = $app->getObjects()->loadPaths(array('accessLevel=' => 0));

        $xml = new SimpleXMLElement('<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" />');
        foreach ($paths as $item) {
            $urlNode = $xml->addChild('url');
            $urlNode->addChild('loc', FStr::fullUrl($item, true));
        }

        $env->response->write($xml->asXML())->sendBuffer(F::INTERNAL_ENCODING, array(
            'contentType' => 'text/xml'
        ));
    }
}
