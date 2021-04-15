<?php
ini_set('display_errors', 1);

include_once __DIR__ . '/lib/airtable-php/vendor/autoload.php';
$config = include('config-at.php');

// Simple example to setup and retrieve all data from a table 

/* if not using composer, uncomment this
include('../src/Airtable.php');
include('../src/Request.php');
include('../src/Response.php');
*/

use TANIOS\Airtable\Airtable;


$airtable = new Airtable( $config );

$url = $_REQUEST['url'];
echo "Page URL: $url";

$params =  array(
                "filterByFormula" => "( URL = '$url' )" );

$request = $airtable->getContent( 'Pages' , $params );

$response = $request->getResponse();

$r = ( json_decode( $response, true ) )['records'];

echo "<br />List of Tasks for page:<br />";
if ( count( $r) > 0 ) {

	//Grab Call Drivers
	$cd = $r[0]['fields']['|Tasks|'];//['id'];//['URL'];
	//print_r( $cd );
	//echo count( $cd );
	foreach ($cd as $key => $val) {
	    //echo "a[$k] - $v\n\n";
	    $request = $airtable->getContent( 'Tasks/'. $val );
		$response = $request->getResponse();
        $a = $response->{'fields'}->{'Task'};
        
        if ($key === array_key_last($cd)) {
            echo $a;
        } else {
            echo $a . ", ";
        }
	}

} else {
	echo "not in Pages";
}

echo "<br />List of Call Drivers for page:<br />";

if ( count( $r) > 0 ) {

	//Grab Call Drivers
	$cd = $r[0]['fields']['Call drivers'];//['id'];//['URL'];
	//print_r( $cd );
	//echo count( $cd );
	foreach ($cd as $key => $val) {
	    //echo "a[$k] - $v\n\n";
	    $request = $airtable->getContent( '2019 Call drivers/'. $val );
		$response = $request->getResponse();
        $a = $response->{'fields'}->{'Sub-Topic'};
        
        if ($key === array_key_last($cd)) {
            echo $a;
        } else {
            echo $a . ", ";
        }
	}

} else {
	echo "not in Pages";
}



/*
//$request = $airtable->getContent( 'Tasks/recoqk9ooi9ZMhZcZ' );
$request = $airtable->getContent( 'Call drivers/rec0jq4a93vDgmITo' );

$response = $request->getResponse();
echo $response;
//$request = $airtable->getContent( "Tasks/recfTltNSj8uv1AQl");

/*
do {

    $response = $request->getResponse();

    var_dump( json_encode( $response[ 'records' ] ) );

}
while( $request = $response->next() );
*/
