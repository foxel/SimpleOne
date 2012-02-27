<?php

F()->Autoloader->registerClassPath(dirname(__FILE__));
F()->Autoloader->registerClassPath(F_SITE_ROOT.DIRECTORY_SEPARATOR.'plugins');
FCache::clear();

