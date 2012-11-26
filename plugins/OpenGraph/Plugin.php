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

class OpenGraph_Plugin
{
    /** @var SOne_Application */
    protected $_app;
    /** @var K3_Config */
    protected $_config;
    /** @var SOne_Model_Object */
    protected $_pageObject;

    /**
     * @param SOne_Application $app
     * @param K3_Config $config
     */
    public function __construct(SOne_Application $app, K3_Config $config)
    {
        $this->_app    = $app;
        $this->_config = $config;
        $this->_app->addEventHandler(SOne_Application::EVENT_PAGE_OBJECT_ROUTED, array($this, 'grabPageObject'));
        $this->_app->addEventHandler(SOne_Application::EVENT_PAGE_RENDERED, array($this, 'addAppVisData'));
    }

    /**
     * @param SOne_Model_Object $pageObject
     */
    public function grabPageObject(SOne_Model_Object $pageObject)
    {
        $this->_pageObject = $pageObject;
    }

    /**
     * @param FVISNode $pageNode
     */
    public function addAppVisData(FVISNode $pageNode)
    {
        $pageNode
            ->addData('META', sprintf('<meta property="og:title" content="%s"/>', FStr::htmlschars($this->_pageObject->caption)))
            ->addData('META', sprintf('<meta property="og:type" content="%s"/>', 'article'))
            ->addData('META', sprintf('<meta property="og:url" content="%s"/>', FStr::htmlschars(FStr::fullUrl($this->_pageObject->path))))
            ->addData('META', sprintf('<meta property="og:image" content="%s"/>', !empty($this->_pageObject->thumbnailImage)
                ? FStr::htmlschars(FStr::fullUrl($this->_pageObject->thumbnailImage, false, '', $this->_app->getEnv()))
                : ''))
            ;
    }
}
