<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Overview Summary</title>

<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />

</head>

<?php

use TANIOS\Airtable\Airtable;

// displays all errors
//ini_set('display_errors', 1);


?>

<div class="container">
  <div class="row">
    <div class="col-sm-12">
   

      <?php

      // Adobe Analytics

      $time = microtime(true);
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

    // Get Date for AA

    $iso = 'Y-m-d\TH:i:s.v';

    $previousWeekStart =  strtotime("last sunday midnight", strtotime("-2 week +1 day") );
    $previousWeekEnd = strtotime("next sunday", $previousWeekStart);
    $previousWeekStart = date( $iso , $previousWeekStart);
    $previousWeekEnd = date( $iso , $previousWeekEnd);

    $weekStart =  strtotime("last sunday midnight", strtotime("-1 week +1 day") );
    $weekEnd = strtotime("next sunday", $weekStart);
    $weekStart = date( $iso , $weekStart);
    $weekEnd = date( $iso , $weekEnd);

    $monthStart = ( new DateTime("first day of last month midnight") )->format($iso);
    $monthEnd = ( new DateTime("first day of this month midnight") )->format($iso);
    
    $previousMonthStart = ( new DateTime("first day of -2 month midnight") )->format($iso);
    $previousMonthEnd = $monthStart;

    // Get date for GSC

      $iso = 'Y-m-d';

      $startLastGSC = ( new DateTime ( $previousWeekStart ) )->format( $iso );
      $endLastGSC = ( new DateTime ( $previousWeekEnd ) )->modify( '-1 days' )->format( $iso );
      $startGSC = ( new DateTime ( $weekStart ) )->format( $iso );
      $endGSC = ( new DateTime ( $weekEnd ) )->modify( '-1 days' )->format( $iso );

      $dates = [ [ $startLastGSC, $endLastGSC ], [ $startGSC, $endGSC ] ];

    ?>

      <h1>Overview - Summary</h1>
      <p><strong>Date range:</strong> <?=$dates[1][0]?> to <?=$dates[1][1]?></p>
      <p><strong>Compared to:</strong> <?=$dates[0][0]?> to <?=$dates[0][1]?></p>
    </div>
  </div>
</div>

<?php

  	$urls = "";

  	if (substr($url, 0, 8) == "https://") {
          $urls = substr($url, 8, strlen($url));
    } else {
    	$urls = $url;
    }

    $r = new ApiClient($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token']);

    $temp = [ 'aa-ovrvw-smmry-metrics', 'aa-ovrvw-smmry-fwylf', 'aa-ovrvw-smmry-trnd', 'aa-ovrvw-smmry-tsks' ]; //, 'fwylf' ];
    $result = array();
    $j = array();

    foreach ( $temp as $t ) {

      $json = $data[$t];
      $json = sprintf($json, $urls);

      $json = str_replace( 
                        array( "*previousMonthStart*", "*previousMonthEnd*", "*monthStart*", "*monthEnd*", "*previousWeekStart*", "*previousWeekEnd*", "*weekStart*", "*weekEnd*" ), 
                        array( $previousMonthStart, $previousMonthEnd, $monthStart, $monthEnd, $previousWeekStart, $previousWeekEnd, $weekStart, $weekEnd ),
                        $json );
      //$result = api_post($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token'], $api);
      
      $result[] = $r->requestEntity( $json );
      $j[] = $json;

    }

    //echo var_dump($result[0]);

    foreach ( $result as $r ) {

    }

    $res = json_decode( $result[0], true );
    $metrics = $res["summaryData"]["filteredTotals"];

    $res2 = json_decode( $result[1], true );
    $metrics2 = $res2["summaryData"]["filteredTotals"];

    $aaResultTrend = json_decode( $result[2], true );
    $aaMetricsTrend = $aaResultTrend["rows"];

    $aaTrendWeeks = array_slice($aaMetricsTrend, -14);
    $aaTrendLastWeek = array_slice($aaTrendWeeks, 0, 7);
    $aaTrendWeek = array_slice($aaTrendWeeks, -7);

    $aaTasks = json_decode( $result[3], true );
    $aaTasksStats = $aaTasks["rows"];

    $taskArray = array();
    foreach ( $aaTasksStats as $task ) {
      $taskArray[] = $task['value'];
    }

    $fwylfYes = 0;
    $fwylfNo = 4;
    $pv = 8;
    $visitors = 12;
    $visits = 16;


    function differ ( $old, $new ) {
      return (($new - $old) / $old); //(1 - $old / $new) * 100; // ( ( $new - $orig ) / $orig ) * 100;
    }

    function posOrNeg ( $num ) {
      if ( $num > 0 ) return 'fa-arrow-up:green';
      else return 'fa-arrow-down:red';
    }

    function percent( $num ){
        return round ( $num * 100, 0 ) . '%';
    }



    $diff = differ ( $metrics[ $visitors + 2 ], $metrics[ $visitors + 3 ]  );
    $pos = posOrNeg($diff);
    $pieces = explode(":", $pos);

    $diff = abs($diff);


    $fwylfICantFindTheInfo = 0;
    $fwylfOtherReason = 4;
    $fwylfInfoHardToUnderstand = 8;
    $fwylfError = 12;
    ?>

    <div class="container">

<div class="row">
  <div class="col-sm-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Unique Visitors</h5>
        <p class="card-text"><?=number_format($metrics[ $visitors + 3 ])?></p>
        <p><i class="fas <?=$pieces[0]?>" style="color: <?=$pieces[1]?>;"></i> <?=percent($diff)?> compared to previous 7 days</p>
      </div>
    </div>
  </div>

  <?php
  $diff = differ ( $metrics[ $visits + 2 ], $metrics[ $visits + 3 ]  );
    $pos = posOrNeg($diff);
    $pieces = explode(":", $pos);

    $diff = abs($diff);
    ?>

  <div class="col-sm-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Visits to all CRA pages</h5>
        <p class="card-text"><?=number_format($metrics[ $visits + 3 ])?></p>
        <p><i class="fas <?=$pieces[0]?>" style="color: <?=$pieces[1]?>;"></i> <?=percent($diff)?> compared to previous 7 days</p>
      </div>
    </div>
  </div>

  <?php
  $diff = differ ( $metrics[ $pv + 2 ], $metrics[ $pv + 3 ]  );
    $pos = posOrNeg($diff);
    $pieces = explode(":", $pos);

    $diff = abs($diff);
    ?>

  <div class="col-sm-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Page Views</h5>
        <p class="card-text"><?=number_format($metrics[ $pv + 3 ])?></p>
        <p><i class="fas <?=$pieces[0]?>" style="color: <?=$pieces[1]?>;"></i> <?=percent($diff)?> compared to previous 7 days</p>
      </div>
    </div>
  </div>

</div>

</div>

<div class="container">
  <div class="row">
    <div class="col-sm-6">
    <div class="table-responsive">
    <table class="table">
      <caption>Last Week</caption>
      <thead>
        <th>Date</th>
        <th>Value</th>
      </thead>
      <tbody>

        <?php
        foreach ( $aaTrendLastWeek as $trend ) {

          ?>

        <tr>
          <td><?=$trend['value']?></td>
          <td><?=number_format($trend['data'][1])?></td>
        </tr>

        <?php

      }

        ?>
        

      </tbody>
    </table>
  </div>
  </div>

  <div class="col-sm-6">
    <div class="table-responsive">
    <table class="table">
      <caption>Week</caption>
      <thead>
        <th>Date</th>
        <th>Value</th>
      </thead>
      <tbody>

        <?php
        foreach ( $aaTrendWeek as $trend ) {

          ?>

        <tr>
          <td><?=$trend['value']?></td>
          <td><?=number_format($trend['data'][1])?></td>
        </tr>

        <?php

      }

        ?>
        

      </tbody>
    </table>
  </div>
  </div>
</div>
</div>

        <?php

      // GSC

      require 'vendor/autoload.php';

      $data = include ('./php/data-gsc.php');

      $type = [ 'ovrvw-smmry-totals', 'ovrvw-smmry-qryAll' ];

      $results = 5;

      $gscArr = array();
      $gscResp = array();

      $start2 = microtime(true);

      foreach ( $type as $t ) {

        foreach ($dates as $d) {

          $analytics = initializeAnalytics();
          $response = getReport( $d[0], $d[1], $results, $url, $t );
          $u = printResults($analytics, $response, $t);
          $u = json_decode( $u, true );

          $gscArr[] = $u;
          $gscResp[] = $response;
        }
    }


        $time_elapsed_secs = microtime(true) - $start2;

      //totals

      $gscTotals = $gscArr[0];

      $lastClicks = $gscTotals['rows'][0]['clicks'];
      $lastCtr = $gscTotals['rows'][0]['ctr'];
      $lastImp = $gscTotals['rows'][0]['impressions'];
      $lastPos = $gscTotals['rows'][0]['position'];

      $gscTotals = $gscArr[1];

      $clicks = $gscTotals['rows'][0]['clicks'];
      $ctr = $gscTotals['rows'][0]['ctr'];
      $imp = $gscTotals['rows'][0]['impressions'];
      $pos = $gscTotals['rows'][0]['position'];

      $diff = differ ( $lastImp,  $imp );
    $posi = posOrNeg($diff);
    $pieces = explode(":", $posi);

    $diff = abs($diff);

      ?>


<div class="container">

      <div class="row">

      <div class="col-sm-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Total impressions from Google</h5>
        <p class="card-text"><?=number_format($imp)?></p>
        <p><i class="fas <?=$pieces[0]?>" style="color: <?=$pieces[1]?>;"></i> <?=percent($diff)?> compared to previous 7 days</p>
      </div>
    </div>
  </div>

  <?php
  $diff = differ ( $lastCtr,  $ctr );
    $posi = posOrNeg($diff);
    $pieces = explode(":", $posi);

    $diff = abs($diff);
    ?>

  <div class="col-sm-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Click through rate from Google</h5>
        <p class="card-text"><?=percent($ctr)?></p>
        <p><i class="fas <?=$pieces[0]?>" style="color: <?=$pieces[1]?>;"></i> <?=percent($diff)?> compared to previous 7 days</p>
      </div>
    </div>
  </div>

  <?php
  $diff = differ ( $lastPos,  $pos );
    $posi = posOrNeg($diff);
    $pieces = explode(":", $posi);

    $diff = abs($diff);
    ?>

  <div class="col-sm-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Average rank on Google</h5>
        <p class="card-text"><?=number_format($pos)?></p>
        <p><i class="fas <?=$pieces[0]?>" style="color: <?=$pieces[1]?>;"></i> <?=percent($diff)?> compared to previous 7 days</p>
      </div>
    </div>
</div>

</div>
</div>

<div class="container">

  <div class="row">
<div class="col-sm-12">
          <?php

      //query
      $lastClicks = $gscArr[2]['rows'][0]['clicks'];
      $clicks = $gscArr[3]['rows'][0]['clicks'];

      $ctr = $gscArr[3]['rows'][0]['ctr'];
      $imp = $gscArr[3]['rows'][0]['impressions'];
      $pos = $gscArr[3]['rows'][0]['position'];
      $term = $gscArr[3]['rows'][0]['keys'][0];

      $diff = differ ( $lastImp,  $imp );
    $posi = posOrNeg($diff);
    $pieces = explode(":", $posi);

    $diff = abs($diff);

/*
        function implode_recursive(string $separator, array $array): string
{
    $string = '';
    foreach ($array as $i => $a) {
        if (is_array($a)) {
            $string .= implode_recursive($separator, $a);
        } else {
            $string .= $a;
            if ($i < count($array) - 1) {
                $string .= $separator;
            }
        }
    }

    return $string;
}
*/

function implode_recursive($g, $p) {
    return is_array($p) ?
           implode($g, array_map(__FUNCTION__, array_fill(0, count($p), $g), $p)) : 
           $p;
}


      echo "<h3>Top 5 search terms from Google</h3>";

      $qry = $gscArr[3]['rows'];

      if (count($qry) > 0): ?>
        <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th><?php echo implode('</th><th>', array_keys(current($qry))); ?></th>
        </tr>
      </thead>
      <tbody>
    <?php foreach ($qry as $row): array_map('htmlentities', null, $row); ?>
        <tr>
          <td><?=implode_recursive('</td><td>', $row);?></td>
        </tr>
    <?php endforeach; ?>
      </tbody>
    </table>
  </div>
    <?php endif;

    ?>

  </div>
</div>
</div>

<div class="container">
  <div class="row">
    <div class="col-sm-12">

      <h3>Top 10 tasks</h3>

      <?php

    $qry = $aaTasksStats;
    //var_dump($qry);

      if (count($qry) > 0): ?>
        <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Task</th>
          <th>Task Success Survey Completed</th>
        </tr>
      </thead>
      <tbody>
    <?php foreach ($qry as $row): array_map('htmlentities', null, $row); ?>
        <tr>
          <td><?=$row['value'];?></td>
          <td><?=$row['data'][3]?></td>
        </tr>
    <?php endforeach; ?>
      </tbody>
    </table>
  </div>
    <?php endif;

    ?>
  </div>
</div>
</div>

<?php

$config = include('./php/config-at.php');

$start2 = microtime(true);

$airtable = new Airtable( $config );

//var_dump($taskArray);

// Tasks in AirTable

$params =  array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
//print_r($params);
$table = 'Top Task Survey (PP)';

$request = getContentRecursive( $airtable, $table, $params );
$lo = [ 'fields', [ 'Task', 'Tasks' ] ];

$con = parseJSON2( $request, $lo );

//echo "<br /><br /> Connection Main: ";
//var_dump ( $con );

// Enquiry Lines in AirTable

$params = array( ); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
//print_r($params);
$table = 'Weekly Calls (2021)';

$fullArray = [];
$request =  $airtable->getContent( $table, $params);
do {
    $response = $request->getResponse();
    $fullArray = array_merge($fullArray , ( $response->records ) );
}
while( $request = $response->next() );


//var_dump($fullArray);

$m = [ 'fields', 'Equiry Line' ];
$l = [ 'fields', 'Total Calls' ];

$con1 = parseJSON( $fullArray, $l );
//var_dump($con1);
$con2 = parseJSON( $fullArray, $m );
//var_dump($con2);

$arrFinal = array();
for ($i = 0; $i < count($con1) - 1; $i++) {
  if(isset( $arrFinal[ ( $con2[$i] ) ] ))
      $arrFinal[ ( $con2[$i] ) ] +=  $con1[$i];
   else
      $arrFinal += array($con2[$i] => $con1[$i]);
}
//var_dump($arrFinal);

      if (count($arrFinal) > 0): ?>
        <div class="container">
  <div class="row">
    <div class="col-sm-12">

      <h3>Total calls by inquiry line</h3>
        <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Inquiry Line</th>
          <th>Number of calls</th>
        </tr>
      </thead>
      <tbody>
    <?php foreach ($arrFinal as $key => $value) { 
      if ( $key !== '' ) {
      ?>
        <tr>
          <td><?=$key;?></td>
          <td><?=number_format($value);?></td>
        </tr>
    <?php } 
      }
  ?>
      </tbody>
    </table>
  </div>
</div>
</div>
</div>
    <?php endif;

$params = array( ); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
//print_r($params);
$table = 'User Testing';

$fullArray = [];
$request =  $airtable->getContent( $table, $params);
do {
    $response = $request->getResponse();
    $fullArray = array_merge($fullArray , ( $response->records ) );
}
while( $request = $response->next() );


//var_dump($fullArray);

$m = [ 'fields', '# of Users' ];
$l = [ 'fields', 'Success Rate' ];

$con1 = parseJSON( $fullArray, $m );
$con2 = parseJSON( $fullArray, $l );

$totalTasks = number_format( count($fullArray) );
$avgSuccessRate = percent( array_sum( $con2 ) / $totalTasks );
$sumNumUsers = number_format( array_sum( $con1 ) );

//echo 'total tasks: ' . $totalTasks . "<br /><br />avg success rate: " . $avgSuccessUsers . '<br />br />sum of users: ' . $sumNumUsers;



?>

        <div class="container">
  <div class="row">
    <div class="col-sm-6">

<div class="table-responsive">
    <table class="table">
      <thead>
        <th>Metrics</th>
        <th>Previous Month</th>
        <th>Month</th>
        <th>Previous Week</th>
        <th>Week</th>
      </thead>
      <tbody>

        <tr>
          <td>FWYLF - Yes</td>
          <td><?=number_format($metrics[ $fwylfYes + 0 ])?></td>
          <td><?=number_format($metrics[ $fwylfYes + 1 ])?></td>
          <td><?=number_format($metrics[ $fwylfYes + 2 ])?></td>
          <td><?=number_format($metrics[ $fwylfYes + 3 ])?></td>
        </tr>

        <tr>
          <td>FWYLF - No</td>
          <td><?=number_format($metrics[ $fwylfNo + 0 ])?></td>
          <td><?=number_format($metrics[ $fwylfNo + 1 ])?></td>
          <td><?=number_format($metrics[ $fwylfNo + 2 ])?></td>
          <td><?=number_format($metrics[ $fwylfNo + 3 ])?></td>
        </tr>        

      </tbody>
    </table>
  </div>
</div>
<div class="col-sm-6">

<div class="table-responsive">
    <table class="table">
      <thead>
        <th>Metrics</th>
        <th>Previous Month</th>
        <th>Month</th>
        <th>Previous Week</th>
        <th>Week</th>
      </thead>
      <tbody>
   <tr>
          <td>FWYLF - I can't find the information</td>
          <td><?=number_format($metrics2[ $fwylfICantFindTheInfo + 0 ])?></td>
          <td><?=number_format($metrics2[ $fwylfICantFindTheInfo + 1 ])?></td>
          <td><?=number_format($metrics2[ $fwylfICantFindTheInfo + 2 ])?></td>
          <td><?=number_format($metrics2[ $fwylfICantFindTheInfo + 3 ])?></td>
        </tr>

        <tr>
          <td>FWYLF - Other reason</td>
          <td><?=number_format($metrics2[ $fwylfOtherReason + 0 ])?></td>
          <td><?=number_format($metrics2[ $fwylfOtherReason + 1 ])?></td>
          <td><?=number_format($metrics2[ $fwylfOtherReason + 2 ])?></td>
          <td><?=number_format($metrics2[ $fwylfOtherReason + 3 ])?></td>
        </tr>

        <tr>
          <td>FWYLF - Information hard to understand</td>
          <td><?=number_format($metrics2[ $fwylfInfoHardToUnderstand + 0 ])?></td>
          <td><?=number_format($metrics2[ $fwylfInfoHardToUnderstand + 1 ])?></td>
          <td><?=number_format($metrics2[ $fwylfInfoHardToUnderstand + 2 ])?></td>
          <td><?=number_format($metrics2[ $fwylfInfoHardToUnderstand + 3 ])?></td>
        </tr>

        <tr>
          <td>FWYLF - Error/something didn't work</td>
          <td><?=number_format($metrics2[ $fwylfError + 0 ])?></td>
          <td><?=number_format($metrics2[ $fwylfError + 1 ])?></td>
          <td><?=number_format($metrics2[ $fwylfError + 2 ])?></td>
          <td><?=number_format($metrics2[ $fwylfError + 3 ])?></td>
        </tr>

      </tbody>
    </table>

  </div>
</div>
</div>

<div class="container">

      <div class="row">

      <div class="col-sm-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Total tasks</h5>
        <p class="card-text"><?=$totalTasks;?></p>
      </div>
    </div>
  </div>

  <div class="col-sm-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Total users</h5>
        <p class="card-text"><?=$sumNumUsers;?></p>
      </div>
    </div>
  </div>

  <div class="col-sm-4">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Average success rate</h5>
        <p class="card-text"><?=$avgSuccessRate;?></p>
      </div>
    </div>
</div>

</div>
</div>




<?php

//for if at line 55 : if ( $succ === 1 ) {

} else {
	echo "<p>No data</p>";
}

?>

</div>

<?php


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
  if ( $t == "ovrvw-smmry-totals" || $t == "ovrvw-smmry-qryAll" ) {
    $json = sprintf($json, $start, $end, $results);
  } else {
    $json = sprintf($json, $start, $end, $url, $results);
  }
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

<?php


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

  //echo '<BR /><br />CONTENT: <br /><br />';
  //var_dump( $content );

	foreach ($content as $key => $val) {

    //var_dump("VALUE: " . $val);
		if ( $cnt == 1 ) {
			$a = $val->{$array[0]}; 
		} else if ( $cnt == 2 ) {
			$a = $val->{$array[0]}->{$array[1]}; 
		} else if ( $cnt == 3 ) {
			$a = $val->{$array[0]}->{$array[1]}->{$array[2]};
		}

    if ( is_array($a) ) {
        $a = implode($a, '');
      }

		array_push($records, $a);
	}

	return $records;
}

function parseJSON2 ( $content, $array ) {
  $cnt = count($array);
  $records = [];

  //echo '<BR /><br />CONTENT: <br /><br />';
  //var_dump( $content );

  if ( is_array( $array[1] ) ) {

  foreach ($content as $key => $val) {
    $temp = [];
      foreach ( $array[1] as $arr ) {
        $a = $val->{$array[0]}->{$arr};
        $temp[$arr] = $a;
      }
      $records[] = $temp;
    }
  } /* else {
    //var_dump("VALUE: " . $val);
    if ( $cnt == 1 ) {
      $a = $val->{$array[0]}; 
    } else if ( $cnt == 2 ) {
      $a = $val->{$array[0]}->{$array[1]}; 
    } else if ( $cnt == 3 ) {
      $a = $val->{$array[0]}->{$array[1]}->{$array[2]};
    }

    if ( is_array($a) ) {
        $a = implode($a, '');
      }

      array_push($records, $a);
    }
  }
  */

  return $records;
}
?>



<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>

</body>
</html>