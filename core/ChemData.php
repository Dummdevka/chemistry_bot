<?php

namespace core;

use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class ChemData
{
    public $conn;
    public $options = [
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_AUTOREFERER => TRUE,
        CURLOPT_FAILONERROR => TRUE,
    ];
    public $base_url = 'https://api.rsc.org/compounds/v1/';
    protected $ch;

    public function __construct() {
        //Create a connection and return it to the function
        $this->ch = curl_init();
        //Options
        curl_setopt_array( $this->ch, $this->options );
    }

    public function getDataByElement( Request $request, Response $response, $args ) : Response {
        $element = ucfirst( $args['element'] );

        if( !$element ) {
            return $response->withStatus( 404, 'No element set' );
        }

        $url = [ 'filter', 'element' ];
        $params = ['includeElements' => ["$element"]]; //This format is required

        $result = $this->getData( $url, $params );

        $response->getBody()->write( $result );
        return $response->withHeader('Content-Type', 'application/json' )->withStatus( 200 );
    }

    public function getDataByName( Request $request, Response $response, $args ) : Response {
        $name = ucfirst( $args['name'] );

        if( !$name ) {
            return $response->withStatus( 404, 'No name set' )->withHeader('Content-Type', 'text/html')
                ->write('Page not found');;
        }

        $url = [ 'filter', 'name' ];
        $params = ['name' => "$name"];

        $result = $this->getData( $url, $params );

        $response->getBody()->write( $result );
        return $response->withHeader('Content-Type', 'application/json' )->withStatus( 200 );
    }
    public function getDataByFormula( Request $request, Response $response, $args ) : Response {
        $formula = strtoupper( trim( $args['formula'] ) );

        if( !$formula ) {
            return $response->withStatus( 404, 'No formula set' );
        }

        $url = [ 'filter', 'formula' ];
        $params = ['formula' => "$formula"];

        $result = $this->getData( $url, $params );

        $response->getBody()->write( $result );
        return $response->withHeader('Content-Type', 'application/json' )->withStatus( 200 );
    }

    //Retrieves data about the required element/formula
    //Takes in an array of url parts and $params which define the search type
    public function getData ( array $url_arr, array $params ) {
        //List of record ids
        $recordIds = $this->getRecords( $url_arr, $params );

        curl_reset( $this->ch );
        $recordId = $recordIds[0];
        $url = $this->base_url . 'records/batch';

        //What are we looking for?
        $search_params = [
            "fields" => [
                "CommonName",
                "SMILES",
                "InChI",
                "Formula"
            ],
            "recordIds" => [
                "$recordId"
            ]
        ];
        curl_setopt( $this->ch,CURLOPT_POST, 1 );
        $this->setCurlOpt( $url, $search_params );

        //Save the data because it outputs on execution
        ob_start();
        $q = curl_exec( $this->ch );
        $res = ob_get_contents();
        ob_end_clean();

        if( curl_errno( $this->ch )){
            die( curl_error( $this->ch ) );
        }

        return $res;
    }

    public function getRecords ( array $url_arr, array $params ) {
        //Create request url
        $url = $this->base_url . implode( '/', $url_arr );
        //Setting the params of the request
        $this->setCurlOpt( $url, $params );
        //Get the queryID
        $queryId = curl_exec( $this->ch );
        $queryId = json_decode( $queryId, true );

        if( curl_errno( $this->ch )){
            die( curl_error( $this->ch ) );
        }
        //Check the query
        $check = $this->checkQuery( $queryId['queryId'] );
        if( !$check ){
            exit ( $check );
        }
        //Get record ids
        $recordIds = $this->getRecordId( $queryId['queryId'] );

        return $recordIds['results'];
    }

    public function checkQuery ( string $queryId ) {
        //Setting URL
        $url = $this->base_url . "/filter/$queryId/status";

        $this->setCurlOpt( $url );
        //Request url
        $response = curl_exec( $this->ch );
        //Error handling
        if( curl_errno( $this->ch )){
            die( curl_error( $this->ch ) );
        }
        if( strcmp( $response['status'], 'Complete' ) === 0) {
            return true;
        } else {
            return 'Status ' . $response['status'] . ' ' . $response['message'];
        }
    }

    public function getRecordId ( string $queryId ) {
        $url = $this->base_url . "filter/$queryId/results?count=1";
        //This is a GET request
        curl_setopt( $this->ch, CURLOPT_CUSTOMREQUEST, 'GET' );
        $this->setCurlOpt( $url );

        $response = curl_exec( $this->ch );
        //Error handling
        if( curl_errno( $this->ch )){
            die( curl_error( $this->ch ) );
        }

        //Array if record ids
        return json_decode( $response, true );
    }

    public function setCurlOpt ( string $url, array $params = [] ) : void {
        $headers = array(
            "Content-Type: application/json",
            "Accept: application/json",
            "apikey:AJ1teSjwFA8YmUArnJaK5vCJG7ZAxkNt"
         );
        //Api key auth 
        curl_setopt( $this->ch, CURLOPT_HTTPHEADER, $headers );
        curl_setopt( $this->ch, CURLOPT_URL, $url );
        curl_setopt($this->ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($this->ch, CURLOPT_PROTOCOLS, CURLPROTO_HTTPS);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, true);
        if( !empty( $params )) {
            curl_setopt( $this->ch, CURLOPT_POSTFIELDS, json_encode($params) );
        }
    }
}