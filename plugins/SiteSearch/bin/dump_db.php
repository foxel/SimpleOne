#!/usr/bin/php
<?php
/**
 * Copyright (C) 2015 Andrey F. Kupreychik (Foxel)
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

define('F_SITE_ROOT', realpath(__DIR__.'/../../..'));
set_include_path(F_SITE_ROOT.PATH_SEPARATOR.get_include_path());

require_once 'core/bootstrap.php';

/**
 * Class SiteSearch_Dump
 */
class SiteSearch_Dump extends SOne_Application
{
    public function run()
    {
        $this->requirePlugins(array('SiteSearch'));

        $offset = 0;

        while ($objects = $this->getObjects()->loadAll(array(), 'id', 1000, $offset)) {
            $offset += count($objects);
            set_time_limit(20);
            foreach($objects as $object) {
                SiteSearch_Bootstrap::getPluginInstance()->updateIndex($object);
            }
            printf('%d items done'.PHP_EOL, $offset);
        }

        printf('Finished in %f seconds.'.PHP_EOL, $this->_env->clock->timeSpent);
    }
}

$app = new SiteSearch_Dump(F()->appEnv);

$app->bootstrap()
    ->run();
