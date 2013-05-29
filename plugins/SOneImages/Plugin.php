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

class SOneImages_Plugin
{
    /** @var SOne_Application */
    protected $_app;
    /** @var K3_Config */
    protected $_config;

    /**
     * @param SOne_Application $app
     * @param K3_Config $config
     */
    public function __construct(SOne_Application $app, K3_Config $config)
    {
        $this->_app    = $app;
        $this->_config = $config;

        $this->_app->getEnv()->getResponse()
            ->addEventHandler('HTML_parse', array($this, 'HTML_Images'));

        if ($this->_config->lazyload) {
            $this->_app->addEventHandler(SOne_Application::EVENT_PAGE_RENDERED, array($this, 'addAppVisData'));
        }
    }

    /**
     * @param FVISNode $pageNode
     */
    public function addAppVisData(FVISNode $pageNode)
    {
        $pageNode
            ->addData('CSS', 'img[data-lazyload-src] { display: none; }')
            ->addData('BOTT_JS_BLOCKS', '<script type="text/javascript">//<!--
                require(["jquery", "sone.lazyload"], function ($) {
                    $("img[data-lazyload-src]").show().lazyload();
                });
            //--></script>');
    }


    /**
     * @param string $buffer
     * @return string
     */
    public function HTML_Images(&$buffer)
    {
        $buffer = preg_replace_callback(
            '#<(img\s+[^>]*?)src\s*=\s*(\"[^\"]*\"|\'[^\']*\'|[^\s<>]+)([^>]*?)/?>#i',
            array($this, '_IMG_Callback'),
            $buffer
        );
        return $buffer;
    }

    /**
     * @param $vars
     * @return bool|string
     */
    public function _IMG_Callback($vars)
    {
        if (!is_array($vars)) {
            return false;
        }

        $env = $this->_app->getEnv();

        $t = '<%1$ssrc="%2$s"%3$s />';

        $url = &$vars[2];
        $url = trim($url, '\'"');
        if ($this->_config->scale && (strpos($url, $env->server->rootUrl) === 0 || FStr::isUrl($url) == 2)) {
            $scaleParams = array();
            if (preg_match('#width\s*[:=]\s*"?(\d+)(px|"|\s)#i', $vars[0], $matches)) {
                $scaleParams['w'] = (int) $matches[1];
            }
            if (preg_match('#height\s*[:=]\s*"?(\d+)(px|"|\s)#i', $vars[0], $matches)) {
                $scaleParams['h'] = (int) $matches[1];
            }
            if ($scaleParams) {
                $url.= '?scale&amp;'.http_build_query($scaleParams, null, '&amp;');
            }
        }

        if ($this->_config->lazyload && strpos($vars[0], 'width') && strpos($vars[0], 'height')) {
            $t = '<%1$ssrc="'.FStr::fullUrl('/static/images/pixel.gif', true, '', $env).'" data-lazyload-src="%2$s"%3$s /><noscript>'.$t.'</noscript>';
        }

        return vsprintf($t, array_slice($vars, 1));
    }
}