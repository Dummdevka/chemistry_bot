<?php

session_start();

define( 'BASEDIR',  dirname(__DIR__, 1)  );
define( 'CHEM_APIKEY', 'AJ1teSjwFA8YmUArnJaK5vCJG7ZAxkNt' );

require BASEDIR . '/vendor/autoload.php';
use Slim\Factory\AppFactory;


//Initializing Slim
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
$app->setBasePath('/chemistry_bot');
//Routing
$routes = require_once BASEDIR . '/bootstrap/routes.php';

$app->run();