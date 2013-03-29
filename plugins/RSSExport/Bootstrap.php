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

class RSSEXport_Bootstrap implements SOne_Interface_PluginBootstrap
{
    /** @var K3_Config */
    protected static $_config;

    /**
     * @return \K3_Config
     */
    public static function getConfig()
    {
        return self::$_config;
    }

    /**
     * @param SOne_Application $app
     * @param K3_Config $config
     */
    public static function bootstrap(SOne_Application $app, K3_Config $config)
    {
        self::$_config = $config;
        SOne_Model_Object::addNamespace('RSSExport_Model_Object');
    }
}
