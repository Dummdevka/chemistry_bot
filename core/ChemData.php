<?php

namespace core;

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
    //POST METHOD
    public function getDataByElement( string $element ) : array {
        //Form the url
        $url = '/filter/element';
        $params = [
            'includeElements' => [trim($element)]
        ];

        //Get the data


        //returns array - void - nothing could be found
        //if false - there is an error
    }
    public function getDataByName( string $name) {

    }
    public function getDataByFormula( string $formula ) {

    }
    public function getData ( array $url_arr, array $params ) {
        //List of record ids
        $recordIds = $this->getRecords( $url_arr, $params );

        $recordId = $recordIds[1];
        $url = $this->base_url . 'records/batch';
        $params = [
            "fields" => [
            "CommonName",
            "SMILES",
            "InChI",
            "Formla",
            "MonoisotopicMass"]
        ];
        $this->setCurlOpt( $url, $params );

        $response = curl_exec( $this->ch );
        if( curl_errno( $this->ch )){
            return 'Connection error:' . curl_error($this->ch);
            //http_response_code( curl_getinfo( $this->ch, CURL_ER))
        }
        return $response['records'];

    }
    public function getRecords( array $url_arr, array $params ) {
        //Create request url
        $url = $this->base_url . implode( '/', $url_arr );
        //Setting the params of the request
        $this->setCurlOpt( $url, $params );
        //Get the queryID
        $queryId = curl_exec( $this->ch );
        $queryId = json_decode( $queryId, true );
        if( curl_errno( $this->ch )){
            //return curl_getinfo( $this->ch );
            return curl_error($this->ch);
            //http_response_code( curl_getinfo( $this->ch, CURL_ER))
        }
        //Check the query
        $check = $this->checkQuery( $queryId['queryId'] );
        if( !$check ){
            exit ( $check );
        }
        //Get record ids
        $recordIds = $this->getRecordId( $queryId['queryId'] );
        return $recordIds;
        
    }
    public function checkQuery( string $queryId ) {
        //Setting URL
        $url = $this->base_url . "/filter/$queryId/status";

        $this->setCurlOpt( $url );
        //Request url
        $response = curl_exec( $this->ch );
        //Error handling
        if( curl_errno( $this->ch )){
            return curl_error( $this->ch );
        }
        if( strcmp( $response['status'], 'Complete' ) === 0) {
            return true;
        } else {
            return 'Status ' . $response['status'] . ' ' . $response['message'];
        }

    }
    public function getRecordId( string $queryId ) {
        $url = $this->base_url . "filter/$queryId/results";
        curl_setopt( $this->ch, CURLOPT_CUSTOMREQUEST, 'GET' );
        $this->setCurlOpt( $url );
        $response = curl_exec( $this->ch );

        //Error handling
        if( curl_errno( $this->ch )){
            return curl_error( $this->ch );
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
        if( !empty( $params )) {
            curl_setopt( $this->ch, CURLOPT_POSTFIELDS, json_encode($params) );
        }
    }
}