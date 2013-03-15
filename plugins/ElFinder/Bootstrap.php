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

define ('ELFINDER_PLUGIN_PATH', F_SITE_ROOT.DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'elFinder');

class ElFinder_Bootstrap implements SOne_Interface_PluginBootstrap
{
    /** @var ElFinder_Plugin */
    protected static $_pluginInstance;

    public static function bootstrap(SOne_Application $app, K3_Config $config)
    {
        SOne_Model_Object::addNamespace('ElFinder_Model_Object');
        F()->Autoloader
            ->registerClassFile('elFinder', ELFINDER_PLUGIN_PATH.DIRECTORY_SEPARATOR.'elFinder.class.php')
            ->registerClassFile('elFinderVolumeDriver', ELFINDER_PLUGIN_PATH.DIRECTORY_SEPARATOR.'elFinderVolumeDriver.class.php')
            ->registerClassFile('elFinderVolumeLocalFileSystem', ELFINDER_PLUGIN_PATH.DIRECTORY_SEPARATOR.'elFinderVolumeLocalFileSystem.class.php')
            ->registerClassFile('elFinderVolumeMySQL', ELFINDER_PLUGIN_PATH.DIRECTORY_SEPARATOR.'elFinderVolumeMySQL.class.php')
            ->registerClassFile('elFinderVolumeFTP', ELFINDER_PLUGIN_PATH.DIRECTORY_SEPARATOR.'elFinderVolumeFTP.class.php')
            ->registerClassFile('elFinderVolumeSOneFileSystem', dirname(__FILE__).DIRECTORY_SEPARATOR.'SOneVolumeDriver.php');

        self::$_pluginInstance = new ElFinder_Plugin($app, $config);
    }

}
