<?php

require_once __DIR__ . '/../vendor/autoload.php';

$config = array('settings' => array('displayErrorDetails' => true));
$app = new Slim\App($config);

require_once __DIR__ . '/../config/services.php';
require_once __DIR__ . '/../config/routes.php';

$app->run();
