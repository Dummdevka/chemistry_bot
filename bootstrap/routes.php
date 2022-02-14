<?php

declare (strict_types = 1);

use Slim\Psr7\Request;
use Slim\Psr7\Response;
use Slim\Routing\RouteCollectorProxy;

$app->get('/', function( Request $request, Response $response, $args ) {
    $response->getBody()->write( 'Welcome!' );
    return $response;
});

//Get info about a chemical element
$app->group( '/data', function( RouteCollectorProxy $group ) {
    $group->get('', function( Request $request, Response $response, $args ) {
        $res = new core\ChemData();
        var_dump( $res->getRecords( ['filter', 'element'] , ['includeElements'=>['Na']]));
        return $response;
    });
    $group->get( '/element/{element}', function( Request $request, Response $response, $args ) {
        $response->getBody()->write( 'Element info' );
        return $response;
    });
    //Get info about a chemical element by its name
    $group->get( '/name/{name}', function( Request $request, Response $response, $args ) {
        $response->getBody()->write( 'Element info by name' );
        return $response;
    });
    //Get info about a chemical element
    $group->get( '/formula/{formula}', function( Request $request, Response $response, $args ) {
        $response->getBody()->write( 'Formula info' );
        return $response;
    });
});
