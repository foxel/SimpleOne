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

class SOne_Tools extends K3_Environment_Element
{
    /**
     * @param SOne_Environment $env
     * @return SOne_Tools
     */
    public static function getInstance(SOne_Environment $env)
    {
        $instance = $env->get('tools');
        if (!$instance instanceof static) {
            $instance = new static($env);
            $env->put('tools', $instance);
        }

        return $instance;
    }

    /**
     * @param string $buffer
     * @return string
     */
    public function HTML_FullURLs(&$buffer)
    {
        $buffer = preg_replace_callback(
            '#(<(a|form|img|link|script)\s+[^>]*?)(href|action|src)\s*=\s*(\"([^\"]*)\"|\'([^\']*)\'|[^\s<>]+)#i',
            array($this, '_FullURLs_Parse_Callback'),
            $buffer
        );

        return $buffer;
    }

    /**
     * @param $vars
     * @return bool|string
     */
    public function _FullURLs_Parse_Callback($vars)
    {
        if (!is_array($vars)) {
            return false;
        }

        if (isset($vars[6])) {
            $url = $vars[6];
            $bounds = '\'';
        } elseif (isset($vars[5])) {
            $url = $vars[5];
            $bounds = '"';
        } else { $url = $vars[4];
            $bounds = '';
        }

        if (K3_String::isUrl($url) == 2) {
            $url = K3_Util_Url::fullUrl($url, $this->env);
        }

        return $vars[1].$vars[3].'='.$bounds.$url.$bounds;

    }
}
