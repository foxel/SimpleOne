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

/**
 * Special SOne Request class
 * @property K3_Request request
 * @property string path
 * @property string|null action
 */
class SOne_Request extends FBaseClass
{
    /**
     * @param SOne_Environment $env
     * @param K3_Config $config
     */
    public function __construct(SOne_Environment $env, K3_Config $config)
    {
        list ($path) = explode('?', preg_replace('#^index\.php/?#i', '', $env->request->url), 2);
        $query = $env->getRequest()->getURLParams();
        $action = null;
        if (reset($query) === '') { // for queries like foo/bar?edit
            $action = K3_Util_String::filter(key($query), K3_Util_String::FILTER_WORD);
        } else {
            $action = $env->getRequest()->getString('action', K3_Request::POST, K3_Util_String::FILTER_WORD);
        }
        
        if (!$path) {
            $path = (string) $config->site->indexPath;
        } else {
            $path = rawurldecode($path);
        }

        $this->pool = array(
            'request' => $env->request,
            'path'    => K3_Util_String::filter($path, K3_Util_String::FILTER_PATH),
            'action'  => $action,
        );
    }
}
