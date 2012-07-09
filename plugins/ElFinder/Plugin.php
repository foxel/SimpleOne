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

class ElFinder_Plugin
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
        if ($this->_config->connectorUrl) {
            $this->_app->addEventHandler(SOne_Application::EVENT_PAGE_RENDER, array($this, 'addAppVisData'));
        }
    }

    /**
     * @param FVISNode $pageNode
     */
    public function addAppVisData(FVISNode $pageNode)
    {
        $pageNode
            ->addData('META', '<link rel="stylesheet" href="'.FStr::fullUrl('static/css/elfinder.min.css', true).'" type="text/css" media="screen" charset="utf-8">')
            ->addData('JS_BLOCKS', implode(FStr::ENDL, array(
                '<script type="text/javascript" src="'.FStr::fullUrl('static/jscripts/elfinder.min.js', true).'"></script>',
                '<script type="text/javascript" src="'.FStr::fullUrl('static/jscripts/i18n/elfinder.ru.js', true).'" charset="utf-8"></script>',
            )))
            ->addNode('ELFINDER_JS_BLOCK', 'JS_BLOCKS', array(
                'connectorUrl' => $this->_config->connectorUrl,
            ));
    }
}