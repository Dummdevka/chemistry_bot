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
        $response->body()->write('You can search by element/name/formula/');
        return $response;
    });
    $group->get( '/element/{element}', core\ChemData::class . ':getDataByElement' );

    //Get info about a chemical element by its name
    $group->get( '/name/{name}', core\ChemData::class . ':getDataByName' );

    //Get info about a chemical element
    $group->get( '/formula/{formula}', core\ChemData::class . ':getDataByFormula' );
});
