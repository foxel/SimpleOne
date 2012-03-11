<?php

define ('STARTED', true);
define ('F_DEBUG', (boolean) getenv('F_DEBUG'));

require_once 'kernel3/kernel3.php';
require_once 'core/bootstrap.php';

$app = new SOne_Application(F()->appEnv);

$app->bootstrap()
    ->run();

