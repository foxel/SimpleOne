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

class Google_Bootstrap implements SOne_Interface_PluginBootstrap
{
    /** @var Google_Plugin */
    protected static $_pluginInstance;

    /**
     * @return \Google_Plugin
     */
    public static function getPluginInstance()
    {
        return self::$_pluginInstance;
    }

    public static function bootstrap(SOne_Application $app, K3_Config $config)
    {
        self::$_pluginInstance = new Google_Plugin($app, $config);
        SOne_Model_Widget::addNamespace('Google_Model_Widget');
        SOne_Model_Object::addNamespace('Google_Model_Object');
    }

}
