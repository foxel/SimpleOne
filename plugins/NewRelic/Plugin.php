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

class NewRelic_Plugin
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
     * @throws FException
     */
    public function __construct(SOne_Application $app, K3_Config $config)
    {
        if (!extension_loaded('newrelic')) {
            throw new FException('NewRelic PHP extension required');
        }

        $this->_app    = $app;
        $this->_config = $config;
        $this->_app->addEventHandler(SOne_Application::EVENT_PAGE_OBJECT_ROUTED, array($this, 'soneObjectRoutedHandle'));
        $this->_app->addEventHandler(SOne_Application::EVENT_PAGE_RENDERED, array($this, 'addAppVisData'));
    }

    /**
     * @param SOne_Model_Object $object
     */
    public function soneObjectRoutedHandle(SOne_Model_Object $object)
    {
        $request = $this->_app->getRequest();

        /** @noinspection PhpUndefinedFunctionInspection */
        newrelic_name_transaction(ucfirst($object->class).($request->action ? '/'.$request->action : ''));
    }

    /**
     * @param FVISNode $pageNode
     */
    public function addAppVisData(FVISNode $pageNode)
    {
        /** @noinspection PhpUndefinedFunctionInspection */
        $pageNode
            ->addData('META', newrelic_get_browser_timing_header(true))
            ->addData('BOTT_JS', newrelic_get_browser_timing_footer(false))
            ;
    }
}
