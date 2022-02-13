<?php
use Slim\Psr7\Request;
use Slim\Psr7\Response;

// declare (strict_types = 1);

$app->get( '/', function( Request $request, Response $response, $args ) {
    $response->getBody()->write( 'Hello' );
    return $response;
});