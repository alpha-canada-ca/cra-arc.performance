<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Demo - API Sample</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" />

</head>

<?php

// displays all errors
//ini_set('display_errors', 1);

//If no URL is given, set the following page as a default
$url = $_REQUEST['url'];

if ($url == null) {
	$url = "https://www.canada.ca/en/revenue-agency/services/e-services/e-services-individuals/account-individuals.html";
}

?>

<div class="container">
  <div class="row">
    <div class="col-sm-12">
    	<h1>Sample API Demo</h1>
    	<p>In this demo, you can apply a ?url= tag at the end of the URL (inluding https://), for the page you want to see statistics for
    	<p><strong>Page URL</strong>: <?=$url?></p>
      <p><strong>Start date:</strong> 2021-05-01</p>
      <p><strong>End date:</strong> 2021-05-31</p>
    </div>
  </div>
</div>

<div class="container">
  <div class="row">
    <div class="col-sm-4">
      <h2>Adobe Analytics</h2>
      <?php

      // Adobe Analytics

      $start = microtime(true);
      $succ = 0;

if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
    require_once('./php/getToken.php');
    $succ = 1;
}
else if (time() - $_SESSION['CREATED'] > 86400) {
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
    require_once('./php/getToken.php');
    $succ = 1;
} 
if ( isset($_SESSION["token"]) ) {
	$succ = 1;
}

if ( $succ === 1 ) {

    require_once('./php/api_post.php');
    $config = include('./php/config-aa.php');
  	$data = include ('./php/data-aa.php');

  	$urls = "";

  	if (substr($url, 0, 8) == "https://") {
          $urls = substr($url, 8, strlen($url));
    } else {
    	$urls = $url;
    }

    $r = new ApiClient($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token']);

    $temp = [ 'metrics' ]; //, 'fwylf' ];
    $result = array();

    foreach ( $temp as $t ) {

      $json = $data[$t];
      $json = sprintf($json, $urls);
      //$result = api_post($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token'], $api);
      
      $result[] = $r->requestEntity( $json );

    }

    //echo var_dump($result[0]);

    $res = json_decode( $result[0], true );
    $metrics = $res["summaryData"]["filteredTotals"];
    ?>

    <h3>Traffic</h3>

    <p><strong>Visits</strong>: <?=number_format($metrics[0])?></p>
    <p><strong>Unique visitors</strong>: <?=number_format($metrics[1])?></p>
    <p><strong>Page views</strong>: <?=number_format($metrics[2])?></p>

    <h3>Devices used</h3>

    <p><strong>Mobile phone</strong>: <?=number_format($metrics[3])?></p>
    <p><strong>Desktop</strong>: <?=number_format($metrics[4])?></p>
    <p><strong>Tablet</strong>: <?=number_format($metrics[6])?></p>
    <p><strong>All other visits</strong>: <?=number_format($metrics[5])?></p>

    <p><a class="btn btn-primary" data-toggle="collapse" href="#AAJSON" role="button" aria-expanded="false" aria-controls="AAJSON">JSON Request</a></p>

  <div class="collapse" id="AAJSON">
  <div class="card card-body">
    <?=var_dump($json)?>
  </div>
</div>

<p><a class="btn btn-primary" data-toggle="collapse" href="#AAResponse" role="button" aria-expanded="false" aria-controls="AAResponse">Response</a></p>

<div class="collapse" id="AAResponse">
  <div class="card card-body">
    <?=var_dump($result[0])?>
  </div>
</div>

<?php

$time_elapsed_secs = microtime(true) - $start;

echo "<p>Time taken: " . number_format($time_elapsed_secs, 2) . " seconds</p>";

} else {
	echo "<p>No data</p>";
}

?>

</div>
    <div class="col-sm-4">
      <h2>Google Search Console</h2>

      <?php

      // GSC

      require 'vendor/autoload.php';

      $data = include ('./php/data-gsc.php');

      $type = [ 'totals', 'qryAll' ];

      $start = '2021-05-01';
      $end = '2021-05-31';
      $results = 10;

      $arr = [];
      $resp = [];

      $start2 = microtime(true);

      foreach ( $type as $t ) {

		    $analytics = initializeAnalytics();
	      $response = getReport( $start, $end, $results, $url, $t );
	      $u = printResults($analytics, $response, $t);
	      $u = json_decode( $u, true );


	      $arr[] = [ $u ];
        $resp[] = [ $response ];
	  }


        $time_elapsed_secs = microtime(true) - $start2;

      //totals

      $clicks = $arr[0][0]['rows'][0]['clicks'];
      $ctr = $arr[0][0]['rows'][0]['ctr'];
      $imp = $arr[0][0]['rows'][0]['impressions'];
      $pos = $arr[0][0]['rows'][0]['position'];

      ?>

      <h3>Totals for page</h3>
      <p><strong>Total clicks</strong>: <?=number_format($clicks)?></p>
      <p><strong>Total impressions</strong>: <?=number_format($imp)?></p>
      <p><strong>Average click through rate (CTR)</strong>: <?=number_format($ctr)?></p>
      <p><strong>Average position</strong>: <?=number_format($pos)?></p>

       <p><a class="btn btn-primary" data-toggle="collapse" href="#GSCJSON1" role="button" aria-expanded="false" aria-controls="GSCJSON1">JSON Request</a></p>

  <div class="collapse" id="GSCJSON1">
  <div class="card card-body">
    <?=var_dump(json_encode($resp[0], true))?>
  </div>
</div>

<p><a class="btn btn-primary" data-toggle="collapse" href="#GSCResponse1" role="button" aria-expanded="false" aria-controls="GSCResponse1">Response</a></p>

<div class="collapse" id="GSCResponse1">
  <div class="card card-body">
    <?=var_dump($arr[0])?>
  </div>
</div>

      <?php

      //query
      $clicks = $arr[1][0]['rows'][0]['clicks'];
      $ctr = $arr[1][0]['rows'][0]['ctr'];
      $imp = $arr[1][0]['rows'][0]['impressions'];
      $pos = $arr[1][0]['rows'][0]['position'];
      $term = $arr[1][0]['rows'][0]['keys'][0];

      echo "<h3>Top 10 Queries</h3>";

      $qry = $arr[1][0]['rows'];

      if (count($qry) > 0): ?>
      	<div class="table-responsive">
		<table class="table">
		  <thead>
		    <tr>
		      <th><?php echo implode('</th><th>', array_keys(current($qry))); ?></th>
		    </tr>
		  </thead>
		  <tbody>
		<?php foreach ($qry as $row): array_map('htmlentities', $row); ?>
		    <tr>
		      <td><?=implode('</td><td>', $row); ?></td>
		    </tr>
		<?php endforeach; ?>
		  </tbody>
		</table>
	</div>
		<?php endif;

    ?>

    <p><a class="btn btn-primary" data-toggle="collapse" href="#GSCJSON2" role="button" aria-expanded="false" aria-controls="GSCJSON2">JSON Request</a></p>

  <div class="collapse" id="GSCJSON2">
  <div class="card card-body">
    <?=var_dump(json_encode($resp[1], true))?>
  </div>
</div>

<p><a class="btn btn-primary" data-toggle="collapse" href="#GSCResponse2" role="button" aria-expanded="false" aria-controls="GSCResponse2">Response</a></p>

<div class="collapse" id="GSCResponse2">
  <div class="card card-body">
    <?=var_dump($arr[1])?>
  </div>
</div>

<?php

    echo "<p>Total time taken for GSC: " . number_format($time_elapsed_secs, 2) . " seconds</p>";

	  //$res = json_decode( $arr[0] );
/*
      $res = json_encode( (array)$arr[0]) ;//["summaryData"]);

	  echo var_dump( $res );

      $res = json_decode( $res, true );

      echo var_dump( $res );

      $array = $res['rows'];//['clicks'];

	  echo $array;

      /**
 * Initializes an Analytics Reporting API V4 service object.
 *
 * @return An authorized Analytics Reporting API V4 service object.
 */
function initializeAnalytics()
{

  // Use the developers console and download your service account
  // credentials in JSON format. Place them in this directory or
  // change the key file location if necessary.
  $KEY_FILE_LOCATION = __DIR__ . '/php/service-account-credentials.json';

  // Create and configure a new client object.
  $client = new Google_Client();
  $client->setAuthConfig($KEY_FILE_LOCATION);
  $client->setScopes(['https://www.googleapis.com/auth/webmasters.readonly']);

  return $client;
}


/**
 * Queries the Analytics Reporting API V4.
 *
 * @param service An authorized Analytics Reporting API V4 service object.
 * @return The Analytics Reporting API V4 response.
 */
function getReport( $start, $end, $results, $url, $t ) {

  global $data;
  $json = $data[$t];
  $json = sprintf($json, $start, $end, $url, $results);
  $array = json_decode( $json, true);
  
  return new Google_Service_Webmasters_SearchAnalyticsQueryRequest( $array );

}


/**
 * Parses and prints the Analytics Reporting API V4 response.
 *
 * @param An Analytics Reporting API V4 response.
 */
function printResults($client, $q, $t) {

  try {

       $service = new Google_Service_Webmasters($client);
       $u = $service->searchanalytics->query('https://www.canada.ca/', $q);

       return json_encode($u);

     } catch(\Exception $e ) {
        echo $e->getMessage();
     }

}

      ?>
    </div>
    <div class="col-sm-4">
      <h2>AirTable</h2>
      <?php
      // AirTable API

use TANIOS\Airtable\Airtable;

$config = include('./php/config-at.php');

$start2 = microtime(true);

$airtable = new Airtable( $config );

$params =  array( "filterByFormula" => "( URL = '$url' )" );
$table = 'Pages';

$request = $airtable->getContent( $table, $params );
$response = $request->getResponse();
$r = ( json_decode( $response, true ) )['records'];

if ( count( $r ) > 0 ) {

	//var_dump($r);

	echo "<p><strong>List of Tasks for page:</strong></p>";
	//Grab Tasks
	$array = $r[0]['fields']['|Tasks|'];
	$table = 'Tasks';
	$params = array( 'filterByFormula' => 'SEARCH(RECORD_ID(), "'.implode($array, ',').'") != ""');
	$l = [ 'fields', 'Task' ];
	$m = [ 'fields', 'Pages' ];

	$con = getContentRecursive( $airtable, $table, $params );
	$con1 = parseJSON( $con, $l );
	$con2 = parseJSON( $con, $m );

	echo implode($con1, ', ');

	echo "<p><strong>List of Pages that contain first task $con1[0] :</strong></p>";

	$array = $r[0]['fields']['|Tasks|'];
	$table = 'Pages';
	$params = array( 'filterByFormula' => 'SEARCH(RECORD_ID(), "'.implode($con2[0], ',').'") != ""');
	$l = [ 'fields', 'Page' ];

	$con = getContentRecursive( $airtable, $table, $params );
	$con = parseJSON( $con, $l );

	echo implode($con, ', ');

	echo "<p><strong>List of Sub-Topics for Weekly Calls for page:</strong></p>";

	$array = $r[0]['fields']['Weekly Calls (2021) (from |Tasks|)'];
	$table = 'Weekly Calls (2021)';
	$params = array( 'filterByFormula' => 'SEARCH(RECORD_ID(), "'.implode($array, ',').'") != ""');
	$l = [ 'fields', 'Sub-Topic' ];

	$con = getContentRecursive( $airtable, $table, $params );
	$con = parseJSON( $con, $l );

	echo implode($con, ', ');


$time_elapsed_secs = microtime(true) - $start2;

}


// Recursive version of Airtable PHP client's getContent() method
// Including some built-in friendly debugging
function getContentRecursive($db_inventory, $table_name, $filters = []) {

	// Fetch the first response
	$response = $db_inventory->getContent($table_name, $filters)->getResponse();
  
	// If there's an error, show it and return an empty array.
	if ($response->error) {
	  var_dump($response->error->type.': '.$response->error->message);
	  return [];
	}

	$content = $response->records;
	
	return $content;
}

function parseJSON ( $content, $array ) {
	$cnt = count($array);
	$records = [];

	foreach ($content as $key => $val) {
		if ( $cnt == 1 ) {
			$a = $val->{$array[0]}; 
		} else if ( $cnt == 2 ) {
			$a = $val->{$array[0]}->{$array[1]}; 
		} else if ( $cnt == 3 ) {
			$a = $val->{$array[0]}->{$array[1]}->{$array[2]}; 
		}

		if ($a != "") {
			array_push($records, $a);
		}
	}

	return $records;
}
?>

<p><a class="btn btn-primary" data-toggle="collapse" href="#ATResponse" role="button" aria-expanded="false" aria-controls="ATResponse">Response</a></p>

<div class="collapse" id="ATResponse">
  <div class="card card-body">
    <?=var_dump($r)?>
  </div>
</div>

<p>Total time taken: <?=number_format($time_elapsed_secs, 2)?> seconds</p>

    </div>
  </div>
</div>


<?php





?>




<?php







?>

<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>
</html>