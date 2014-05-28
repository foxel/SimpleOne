<?php
/**
 * Copyright (C) 2012, 2014 Andrey F. Kupreychik (Foxel)
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

define ('STARTED', true);
define ('F_DEBUG', (boolean) getenv('F_DEBUG'));
define ('F_PROFILE', (boolean) getenv('F_PROFILE'));

if (file_exists('kernel3.phar')) {
    require_once 'phar://kernel3.phar';
} else {
    require_once 'kernel3/kernel3.php';
}

F()->Autoloader->registerClassPath(dirname(__FILE__).DIRECTORY_SEPARATOR.'SOne', 'SOne');
//F()->Autoloader->registerClassPath(F_SITE_ROOT.DIRECTORY_SEPARATOR.'plugins');

if (F_DEBUG) {
    FCache::clear();
}

F()->Timer->logEvent('Bootstrap complete');
