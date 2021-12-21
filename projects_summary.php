
<?php include "./includes/upd_header.php"; ?>
<?php include "./includes/upd_sidebar.php"; ?>
<?php include "./includes/date-ranges.php"; ?>
<?php include "./includes/functions.php"; ?>
<?php ini_set('display_errors', 0);
 ?>

<!--Translation Code start-->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
<script src="./assets/i18n/js/CLDRPluralRuleParser.js"></script>
<script src="./assets/i18n/js/jquery.i18n.js"></script>
<script src="./assets/i18n/js/jquery.i18n.messagestore.js"></script>
<script src="./assets/i18n/js/jquery.i18n.fallbacks.js"></script>
<script src="./assets/i18n/js/jquery.i18n.language.js"></script>
<script src="./assets/i18n/js/jquery.i18n.parser.js"></script>
<script src="./assets/i18n/js/jquery.i18n.emitter.js"></script>
<script src="./assets/i18n/js/jquery.i18n.emitter.bidi.js"></script>
<script src="./assets/i18n/js/global.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.13.0/d3.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/d3-legend/2.25.6/d3-legend.min.js"></script>

<!--Main content start-->

<?php


//-----------------------------
// FUNCTIONS
// we need to add these in functions.php and remove them from every other page
//-----------------------------

function differ($old, $new)
{
    return (($new - $old) / $old);
}

function numDiffer($old, $new)
{
    return ($new - $old);
}

function posOrNeg($num)
{
    if ($num > 0) return 'text-success:arrow_upward';
    else if ($num == 0) return 'text-warning:horizontal_rule';
    else return 'text-danger:arrow_downward';
}

function posOrNeg2($num)
{
    if ($num > 0) return 'text-success:+';
    else if ($num == 0) return 'text-warning:';
    else return 'text-danger:-';
}

function percent($num)
{
    return round($num * 100, 0) . '%';
}

function metKPI($num, $old)
{
    if (($num > 0.8) || (abs($old-$num)>0.2))  return 'text-success:check_circle:Met';
    else return 'text-danger:warning:Did not meet';
}


//$url = $_REQUEST['url'];

//echo $url;

if (isset($_GET['prj'])) {
$prj = $_GET['prj'];
//echo $prj;
}
else {
//$url = "https://www.canada.ca/en/revenue-agency/services/benefits/recovery-benefit/crb-how-apply.html";
//$prj = "Task Performance Indicators - (May 2021)";
$prj = "CEWS Spreadsheet ";
//$prj = "CRB - Post-launch test ";

}

if (isset($_GET['dr'])) {
$dr = $_GET['dr'];
}
else {
$dr = "week";
}

if (isset($_GET['lang'])) {
$lang = $_GET['lang'];
}
else {
$lang = "en";
}

//$start = microtime(true);
// function getSiteTitle( $url ){
//     $doc = new DOMDocument();
//     @$doc->loadHTML(file_get_contents($url));
//     //$res['title'] = $doc->getElementsByTagName('title')->item(0)->nodeValue;
//     $title = $doc->getElementsByTagName('title')->item(0)->nodeValue;
//
//     $pageTitle = trim(substr($title, 0, -12));
//     // foreach ($doc->getElementsByTagName('meta') as $m){
//     //     $tag = $m->getAttribute('name') ?: $m->getAttribute('property');
//     //     if(in_array($tag,['description','keywords']) || strpos($tag,'og:')===0) $res[str_replace('og:','',$tag)] = $m->getAttribute('content');
//     // }
//     return $pageTitle;
// }

// echo "<pre>";
// print_r(getSiteOG($url));
// echo "</pre>";

// $time_elapsed_secs = microtime(true) - $start;
// echo "<p>Time taken: " . number_format($time_elapsed_secs, 2) . " seconds</p>";
require 'vendor/autoload.php';
use TANIOS\Airtable\Airtable;

$config = include ('./php/config-at.php');

$airtable = new Airtable(array(
     'api_key'   => $config['tasks']['api_key'],
     'base'      => $config['tasks']['base'],
));

//echo $url;
//$sUrl = substr($url, 8, strlen($url));
//echo $sUrl;

//echo $prj;

$params =  array( "filterByFormula" => "( {UX Research Project Title} = '$prj' )" );
$table = "User Testing";

$fullArray = [];


$request = $airtable->getContent($table, $params);
do
{
    $response = $request->getResponse();
    $fullArray = array_merge($fullArray, ($response->records));
}
while ($request = $response->next());

$weeklyRe = ( json_decode(json_encode($fullArray), true));//['records'];


// if there's data (record exist)
if ( count( $weeklyRe ) > 0 ) {
  //do things here

}
//get data with API
$fullArray = $weeklyRe;
// echo "<pre>";
// print_r(array_column_recursive($weeklyRe,"fields"));
// echo "</pre>";


// echo "<pre>";
// print_r($fullArray);
// echo "</pre>";

// ----------------------------------------------------------------------
// WE NEED TO FIND ANOTHER WAY TO CALCULATE THE STATUS OF A Project
// BECAUSE IT CAN HAVE MORE TASKS WITH DIFFERENT STATUS
//$prjStatus = $fullArray[0]['fields']['Status'];//['records'];
// $prjTasks = array_column_recursive($weeklyRe,"Lookup_Tasks");
// $prjPages = array_column_recursive($weeklyRe,"Lookup_Pages");

// // use array_values to re-index the array
$prjTasks = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Lookup_Tasks"))));
$prjPages = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Lookup_Pages"))));
$prjStatus = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Status"))));
// $relatedTasks = $fullArray[0]['fields']['Lookup_Tasks'];//['records'];
// $relatedProjects = $fullArray[0]['fields']['Projects'];

// echo "<pre>";
// print_r($prjTasks);
// echo "</pre>";
// echo "-------------<br/>";
// echo "<pre>";
// print_r($prjPages);
// echo "</pre>";



$prjData = array_column_recursive($fullArray,"fields");

// echo "<pre>";
// print_r($prjData);
// echo "</pre>";

//sort the array by Date
usort($prjData, function($b, $a) {
   return new DateTime($a['Date']) <=> new DateTime($b['Date']);
 });

$prjByGroupType = group_by('Test Type', $prjData);

//-------------------------------------------------------
// there are 2 ways to get the latest two UX testings per project
//-------------------------------------------------------
// 1. sort the array by DATE and group it by the Test Type and get the last two UX test types
// 2. get the unique dates for all tests (as an array), sort the array and then with foreach loops get the last two tests
//-------------------------------------------------------


// 1.------------------------------------------------------

$prjDatesUnique = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Date"))));
// echo "<pre>";
// print_r($prjDatesUnique);
// echo "</pre>";


//get the array of the latest tests
// $latestTestDate = $prjData[0]['Date'];
// $compareTestDate = $prjData[1]['Date'];

$latestTestDate = $prjDatesUnique[0];
$compareTestDate = $prjDatesUnique[1];


// echo "<pre>";
// print_r($latestTestDate);
// echo "</pre>";
// echo "<br>";
// echo "<pre>";
// print_r($compareTestDate);
// echo "</pre>";

if (count($prjDatesUnique)>1) {
  foreach ($prjData as $item) {
    if ($item['Date'] == $latestTestDate) {
      $latestTest[] = $item;
    }
    if ($item['Date'] == $compareTestDate) {
      $compareTest[] = $item;
    }
    // code...
  }

  $avgTaskSuccess = (array_sum(array_column_recursive($latestTest, "Success Rate")))/(count($latestTest));
  $avgCmpTaskSuccess = (array_sum(array_column_recursive($compareTest, "Success Rate")))/(count($compareTest));

}
else {
  foreach ($prjData as $item) {
    if ($item['Date'] == $latestTestDate) {
      $latestTest[] = $item;
    }
    // code...
  }

  $avgTaskSuccess = (array_sum(array_column_recursive($latestTest, "Success Rate")))/(count($latestTest));
  $avgCmpTaskSuccess = $avgTaskSuccess;

}

?>

<h1 class="visually-hidden">Usability Performance Dashboard</h1>
    <div class="back_link"><span class="material-icons align-top">west</span> <a href="./projects_home.php" alt="Back to Projects home page">Projects</a></div>

    <h2 class="h3 pt-2 pb-2" data-i18n=""><?=$prj?> <span class="badge rounded-pill bg-primary" style="margin-left:30px; font-weight:lighter"><?=$prjStatus[0];?></span></h2>

    <div class="tabs sticky">
      <ul>
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./projects_webtraffic.php?prj=<?=$prj?>" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="./projects_searchanalytics.php?prj=<?=$prj?>" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="./projects_pagefeedback.php?prj=<?=$prj?>" data-i18n="tab-pagefeedback">Page feedback</a></li>
        <li <?php if ($tab=="calldrivers") {echo "class='is-active'";} ?>><a href="./projects_calldrivers.php?prj=<?=$prj?>" data-i18n="tab-calldrivers">Call drivers</a></li>
        <li <?php if ($tab=="uxtests") {echo "class='is-active'";} ?>><a href="./projects_uxtests.php?prj=<?=$prj?>" data-i18n="tab-uxtests">UX tests</a></li>
        <li <?php if ($tab=="details") {echo "class='is-active'";} ?>><a href="./projects_details.php?prj=<?=$prj?>" data-i18n="tab-details">Details</a></li>
      </ul>
    </div>

 <?php

// require 'vendor/autoload.php';
// use TANIOS\Airtable\Airtable;


// ADOBE ANALYTICS API QUERIES PROCESSING
//---------------------------------------------------------------------------------------------------


// Adobe Analytics
$time = microtime(true);
$succ = 0;

if (!isset($_SESSION['CREATED']))
{
    $_SESSION['CREATED'] = time();
    require_once ('./php/getToken.php');
    $succ = 1;
}
else if (time() - $_SESSION['CREATED'] > 86400)
{
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
    require_once ('./php/getToken.php');
    $succ = 1;
}
if (isset($_SESSION["token"]))
{
    $succ = 1;
}

if ($succ === 1)
{

    require_once ('./php/api_post.php');
    $config = include ('./php/config-aa.php');
    $data = include ('./php/data-aa.php');

    // Get Date for AA
    $iso = 'Y-m-d\TH:i:s.v';

    $previousWeekStart = strtotime("last sunday midnight", strtotime("-2 week +1 day"));
    $previousWeekEnd = strtotime("next sunday", $previousWeekStart);
    $previousWeekStart = date($iso, $previousWeekStart);
    $previousWeekEnd = date($iso, $previousWeekEnd);

    $weekStart = strtotime("last sunday midnight", strtotime("-1 week +1 day"));
    $weekEnd = strtotime("next sunday", $weekStart);
    $weekStart = date($iso, $weekStart);
    $weekEnd = date($iso, $weekEnd);

    $monthStart = (new DateTime("first day of last month midnight"))->format($iso);
    $monthEnd = (new DateTime("first day of this month midnight"))->format($iso);

    $previousMonthStart = (new DateTime("first day of -2 month midnight"))->format($iso);
    $previousMonthEnd = $monthStart;

    // Get date for GSC
    $iso = 'Y-m-d';

    $startLastGSC = (new DateTime($previousWeekStart))->format($iso);
    $endLastGSC = (new DateTime($previousWeekEnd))->modify('-1 days')
        ->format($iso);
    $startGSC = (new DateTime($weekStart))->format($iso);
    $endGSC = (new DateTime($weekEnd))->modify('-1 days')
        ->format($iso);

    $dates = [[$startLastGSC, $endLastGSC], [$startGSC, $endGSC]];

    // echo "<pre>";
    // print_r($dates);
    // echo "</pre>";

    // Get date for header
    $iso = 'M d';

    $startLastHeader = (new DateTime($previousWeekStart))->format($iso);
    $endLastHeader = (new DateTime($previousWeekEnd))->modify('-1 days')
        ->format($iso);
    $startHeader = (new DateTime($weekStart))->format($iso);
    $endHeader = (new DateTime($weekEnd))->modify('-1 days')
        ->format($iso);
    // Weekly date ranges for the Header
    $datesHeader = [[$startLastHeader, $endLastHeader], [$startHeader, $endHeader]];

    // Added by Kole - Monthly date ranges for the Header
    $monthStartHeader = (new DateTime("first day of last month midnight"))->format($iso);
    $monthEndHeader = (new DateTime("last day of last month midnight"))->format($iso);

    $previousMonthStartHeader = (new DateTime("first day of -2 month midnight"))->format($iso);
    $previousMonthEndHeader = (new DateTime("last day of -2 month midnight"))->format($iso);

    // Monthly date ranges for the Header
    $datesHeaderMonth = [[$previousMonthStartHeader, $previousMonthEndHeader], [$monthStartHeader, $monthEndHeader]];

}


//$urls = "";
//$url = "";
// if (substr($url, 0, 8) == "https://")
// {
//     $urls = substr($url, 8, strlen($url));
// }
// else
// {
//     $urls = $url;
// }

//echo "URLS is ".$urls;

$r = new ApiClient($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token']);

//$temp = ['aa-pages-smmry-metrics', 'aa-pages-smmry-fwylf', 'aa-ovrvw-smmry-trnd', 'aa-ovrvw-smmry-tsks']; //, 'fwylf' ];
$temp = ['aa-pages-smmry-metrics', 'aa-pages-smmry-fwylf']; //, 'fwylf' ];
//$temp = ['aa-pages-smmry-metrics']; //, 'fwylf' ];
$result = array();
$j = array();
$allAPI = array();
$allj = array();

//echo count($prjPages);

foreach ($temp as $t)
{

  foreach ($prjPages as $page)
  {

          $json = $data[$t];

          // echo $urls;
          // echo $page;
          // echo "----------";
          $json = sprintf($json, $page);

          $json = str_replace(array(
              "*previousMonthStart*",
              "*previousMonthEnd*",
              "*monthStart*",
              "*monthEnd*",
              "*previousWeekStart*",
              "*previousWeekEnd*",
              "*weekStart*",
              "*weekEnd*"
          ) , array(
              $previousMonthStart,
              $previousMonthEnd,
              $monthStart,
              $monthEnd,
              $previousWeekStart,
              $previousWeekEnd,
              $weekStart,
              $weekEnd
          ) , $json);
          //$result = api_post($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token'], $api);
          //$result[$page] = $r->requestEntity($json);
          $response = $r->requestEntity($json);
          $result[$page] = json_decode($response,true);
          $j[] = $json;

  }
  // delay the API calls for 2 seconds fr every query
  //sleep(1);

  $allAPI[] = $result;
  $allj[$t] = $j;

}

// //CALCULATE WUERY TIME EXECUTION
// $time_elapsed_secs = microtime(true) - $time;
// echo "<p>Time taken for the queries - ".count($prjPages)." pages: " . number_format($time_elapsed_secs, 2) . " seconds</p>";


// echo "<pre>";
// print_r($allAPI);
// echo "</pre>";


$result = $allAPI;

// $rst = json_decode($allAPI[0]["www.canada.ca/en/revenue-agency/services/subsidy/temporary-wage-subsidy.html"], true);
// $m = $rst["summaryData"]["filteredTotals"];
// echo "<pre>";
// //print_r($allAPI[0]["www.canada.ca/en/revenue-agency/services/subsidy/temporary-wage-subsidy.html"]["summaryData"]["filteredTotals"][8]);
// print_r($m[9]);
// echo "</pre>";


foreach ($result as $r)
{

}

// -----------------------------------------------------------------------
// METRICS query (Visit metrics and DYFWYWLF- Yes and No answers)
// -----------------------------------------------------------------------
//$res = json_decode($result[0], true);
//$metrics = $res["summaryData"]["filteredTotals"];

// echo "<pre>";
// print_r($result);
// echo "</pre>";


$metrics = array_column_recursive($result[0], "filteredTotals");

$sum_metrics=array();
//to address the "undefined offset notice" ,tried to use array_fill
//$sum_metrics = array_fill(0, count($metrics), 0);

for ($i = 0; $i < count($metrics); $i++) {

    for ($k = 0; $k < count($metrics[$i]); $k++) {

        $sum_metrics[$k]+=$metrics[$i][$k];
        //$sum_metrics[$k]+=isset($metrics[$i][$k]) ? $metrics[$i][$k] : null;

      }
}
//
// echo "Metrics 1";
// echo "<pre>";
// print_r($sum_metrics);
// echo "</pre>";

$tmp = array_filter(array_slice($sum_metrics, 0, 8));

$totalVisitsW = $sum_metrics[19];
$totalCompVisitsPW = $sum_metrics[18];
$totalVisitsM = $sum_metrics[17];
$totalCompVisitsPM = $sum_metrics[16];
//$totalVisits = $sum_metrics[$visits+3];
//echo $totalVisits;



// FOR TESTING ONLY - add values to $tmp to be able to show the charts
// REMOVE after finish TESTING
//---------------------------------------------------------------------------
          // $adjusted_metrics = $sum_metrics;
          //
          // for ($d = 0; $d < 8; $d++) {
          //     $adjusted_metrics[$d]+=$d;
          // }
          // // echo "<pre>";
          // // print_r($adjusted_metrics);
          // // echo "</pre>";
          //
          // $tmp = array_filter(array_slice($adjusted_metrics, 0, 8));
          //
          // $sum_metrics = $adjusted_metrics;

// ---End of testing ----------------------------------------------------------



// -----------------------------------------------------------------------
// DYFWYWLF query (What went wrong answers)
// -----------------------------------------------------------------------


$metrics2 = array_column_recursive($result[1], "filteredTotals");

$sum_metrics2=array();
//$sum_metrics2 = array_fill(0, count($metrics2), 0);

for ($i = 0; $i < count($metrics2); $i++) {

    for ($k = 0; $k < count($metrics2[$i]); $k++) {

        $sum_metrics2[$k]+=$metrics2[$i][$k];
        //$sum_metrics2[$k]+=isset($metrics2[$i][$k]) ? $metrics2[$i][$k] : null;
        $sum_metrics2[$k]+=$k;

      }
}

// echo "Metrics 2";
// echo "<pre>";
// print_r($sum_metrics2);
// echo "</pre>";


//DOES THIS PAGE HAS PAGE FEEDBACK TOOL OR NOT
if (empty($tmp)) {
  $pageFeedbackOnPages = 0;
  //echo "None of the pages for this Project have a Page feedback tool!";
}
else {
        $pageFeedbackOnPages = 1;
        //REMOVE AFTER Testing
        $metrics = $sum_metrics;
        $metrics2 = $sum_metrics2;

        // echo "<pre>";
        // print_r($metrics);
        // echo "</pre>";

        $fwylfYes = 0;
        $fwylfNo = 4;
        $pv = 8;
        $visitors = 12;
        $visits = 16;


        $fwylfICantFindTheInfo = 0;
        $fwylfOtherReason = 4;
        $fwylfInfoHardToUnderstand = 8;
        $fwylfError = 12;
        ?>



        <?php
        // AIRTABLE

        $iso = 'Y-m-d\TH:i:s.v';

        $previousWeekStart = strtotime("last sunday midnight", strtotime("-2 week +1 day"));
        $previousWeekEnd = strtotime("next sunday", $previousWeekStart);
        $previousWeekStart = date($iso, $previousWeekStart);
        $previousWeekEnd = date($iso, $previousWeekEnd);

        $weekStart = strtotime("last sunday midnight", strtotime("-1 week +1 day"));
        $weekEnd = strtotime("next sunday", $weekStart);
        $weekStart = date($iso, $weekStart);
        $weekEnd = date($iso, $weekEnd);

        $monthStart = (new DateTime("first day of last month midnight"))->format($iso);
        $monthEnd = (new DateTime("first day of this month midnight"))->format($iso);

        $previousMonthStart = (new DateTime("first day of -2 month midnight"))->format($iso);
        $previousMonthEnd = $monthStart;

        // echo $monthStart."<br>";
        // echo $monthEnd."<br>";
        // echo $previousMonthStart."<br>";
        // echo $previousMonthEnd."<br>";

        // Get date for GSC
        $iso = 'Y-m-d';

        $startLastGSC = (new DateTime($previousWeekStart))->format($iso);
        $endLastGSC = (new DateTime($previousWeekEnd))->modify('-1 days')
            ->format($iso);
        $startGSC = (new DateTime($weekStart))->format($iso);
        $endGSC = (new DateTime($weekEnd))->modify('-1 days')
            ->format($iso);

        $dates = [[$startLastGSC, $endLastGSC], [$startGSC, $endGSC]];
        // echo "<pre>";
        // print_r($dates);
        // echo "</pre>";

        // Get date for header
        $iso = 'M d';

        $startLastHeader = (new DateTime($previousWeekStart))->format($iso);
        $endLastHeader = (new DateTime($previousWeekEnd))->modify('-1 days')
            ->format($iso);
        $startHeader = (new DateTime($weekStart))->format($iso);
        $endHeader = (new DateTime($weekEnd))->modify('-1 days')
            ->format($iso);

        // Weekly date ranges for the Header
        $datesHeader = [[$startLastHeader, $endLastHeader], [$startHeader, $endHeader]];


        $monthStartHeader = (new DateTime("first day of last month midnight"))->format($iso);
        $monthEndHeader = (new DateTime("last day of last month midnight"))->format($iso);

        $previousMonthStartHeader = (new DateTime("first day of -2 month midnight"))->format($iso);
        $previousMonthEndHeader = (new DateTime("last day of -2 month midnight"))->format($iso);

        // Monthly date ranges for the Header
        $datesHeaderMonth = [[$previousMonthStartHeader, $previousMonthEndHeader], [$monthStartHeader, $monthEndHeader]];

    } //if empty($tmp)

        // // AIRTABLE CONNECTION - SETUP REUQEST AND PARSE RESPONSE
        // //--------------------------------------------------------------
        // $s = $startLastGSC;
        // $e = $endLastGSC;
        // $s1 = $startGSC;
        // $e1 = $endGSC;
        //
        //
        // $config = include ('./php/config-at.php');
        // $airtable = new Airtable($config['feedback']);
        //
        // // -----------------------------------------------------------------------------------------------
        // // GET DATA FROM "Page Feedback" (CRA view) table filtered by date range - last two weekStart
        // // -----------------------------------------------------------------------------------------------
        //
        // foreach ($prjPages as $page) {
        //   $listOfPages[] = "(URL = 'https://$page')";
        // }
        // // echo count($listOfPages);
        // // var_dump(implode(",", $listOfPages));
        //
        // $paramPages = implode(",", $listOfPages);
        // //$paramPages = "(URL = 'https://www.canada.ca/fr/agence-revenu/services/prestations/prestation-relance-economique.html'),(URL = 'https://www.canada.ca/en/revenue-agency/services/benefits/recovery-benefit/crb-periods-apply.html')";
        //
        // //echo $url;
        // $params = array(
        //     //"filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')))",
        //     //"filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')), (URL = 'https://www.canada.ca/en/revenue-agency/services/benefits/recovery-benefit/crb-how-apply.html'))",
        //     //"filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')), (URL = '$url'))",
        //     // for get multiple url's or Projects from Airtable listOfPages
        //     //"filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')), OR((URL = '$url'),(URL = 'https://www.canada.ca/en/revenue-agency/services/benefits/recovery-benefit/crb-how-much.html')))",
        //     "filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')), OR($paramPages))",
        //     "view" => "CRA"
        // );
        // $table = 'Page feedback';
        //
        // $fullArray = [];
        // $request = $airtable->getContent($table, $params);
        // do
        // {
        //     $response = $request->getResponse();
        //     $fullArray = array_merge($fullArray, ($response->records));
        // }
        // while ($request = $response->next());
        //
        // $allData = ( json_decode(json_encode($fullArray), true));//['records'];
        //
        // // echo "ALL DATA<pre>";
        // // print_r($allData);
        // // echo "</pre>";
        //
        // // if there's data (record exist)
        // if ( count( $allData ) > 0 ) {
        //       // do things here
        //       // echo "total pagefedbacks for this page: ";
        //       // echo count($allData);
        //       $re = $allData;
        //
        //
        //       //weekly data range
        //       $rangeStartW = strtotime($s1);
        //       $rangeEndW = strtotime($e1);
        //       //previous week range
        //       $rangeStartPW = strtotime($s);
        //       $rangeEndPW = strtotime($e);
        //
        //       //filter array by date ranges
        //       $WeeklyData = array_filter( $re, function($var) use ($rangeStartW, $rangeEndW) {
        //          $utime = strtotime($var['fields']['Date']);
        //          return $utime <= $rangeEndW && $utime >= $rangeStartW;
        //       });
        //
        //       $PWeeklyData = array_filter( $re, function($var) use ($rangeStartPW, $rangeEndPW) {
        //          $utime = strtotime($var['fields']['Date']);
        //          return $utime <= $rangeEndPW && $utime >= $rangeStartPW;
        //       });
        //
        //
        //       if (( count( $WeeklyData ) > 0 ) && ( count( $PWeeklyData ) > 0 )) {
        //
        //             // Get just the ['fields'] array of each record -  as a separate array - $all_fields
        //             $all_fields = array_column_recursive($WeeklyData, 'fields');
        //             $all_fieldsPW = array_column_recursive($PWeeklyData, 'fields');
        //
        //             //we are grouping the pages by URL instead of Page Title, cause some pages might not have titles listes in the table
        //             //stil, the main idea is to group the pages by some unique page element
        //
        //             foreach ( $all_fields as &$item ) {
        //               $item["Tag"] = implode($item['Lookup_tags']);
        //             }
        //
        //             foreach ( $all_fieldsPW as &$item ) {
        //               $item["Tag"] = implode($item['Lookup_tags']);
        //             }
        //
        //             // echo count($all_fields);
        //             // echo "<br><br><pre>";
        //             // print_r($all_fields);
        //             // echo "</pre><br></br>";
        //
        //
        //             $fieldsByGroupTag = group_by('Tag', $all_fields);
        //             $fieldsByGroupTagPW = group_by('Tag', $all_fieldsPW);
        //
        //             //
        //
        //             foreach ( $fieldsByGroupTagPW as &$item ) {
        //               $item["Total tag comments"] = count($item);
        //             }
        //             foreach ( $fieldsByGroupTag as &$item ) {
        //               $item["Total tag comments"] = count($item);
        //             }
        //
        //             $d3TotalFeedbackByPageSuccess = 1;
        //
        //       } //if (( count( $WeeklyData ) > 0 ) && ( count( $PWeeklyData ) > 0 ))
        //
        //
        // } //if count($allData) > 0
        //
        // else {
        //   $d3TotalFeedbackByPageSuccess = 0;
        // }


// end of ADOBE ANALYTICS API QUERIES PROCESSING
//---------------------------------------------------------------------------------------------------

?>

    <div class="row mb-4 mt-1">
      <div class="dropdown">
        <button type="button" class="btn bg-white border border-1 dropdown-toggle" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> <span data-i18n="dr-lastweek">Last week</span></button>
            <span class="text-secondary ps-2 text-nowrap dates-header-week"><?=$datesHeader[1][0] ?> - <?=$datesHeader[1][1] ?></span>
            <span class="text-secondary ps-2 text-nowrap dates-header-week" data-i18n="compared_to"> compared to </span>
            <span class="text-secondary ps-2 text-nowrap dates-header-week"><?=$datesHeader[0][0] ?> - <?=$datesHeader[0][1] ?></span>

        <ul class="dropdown-menu" aria-labelledby="range-button" style="">
          <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
          <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
        </ul>

      </div>
    </div>


    <?php
$diff = differ($avgCmpTaskSuccess, $avgTaskSuccess);
$pos = posOrNeg($diff);
$pieces = explode(":", $pos);
//
$diff = abs($diff);
$kpi_pos = metKPI($avgTaskSuccess, $avgCmpTaskSuccess);
$kpi_pieces = explode(":", $kpi_pos);
?>

    <div class="row mb-2 gx-2">
      <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="card">
          <div class="card-body card-pad pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-i18n="">Average task success from last UX test</span></h3>
              <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=percent($avgTaskSuccess); ?></span><span class="small"><?//=number_format($metrics[$visitors + 2]) ?></span></div>
                <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?php if (count($prjDatesUnique)>1) {echo percent($diff);}  ?></span></div>
              </div>
              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12"><span class="<?=$kpi_pieces[0] ?> text-nowrap"><span class="material-icons"><?=$kpi_pieces[1] ?></span></span><span class="text-nowrap"> <?=$kpi_pieces[2]?> objectve of 80% task success or 20 point increase</span></div>
              </div>
          </div>
        </div>
      </div>


<?php

// $diff = differ($metrics[$pv + 2], $metrics[$pv + 3]);
// $pos = posOrNeg($diff);
// $pieces = explode(":", $pos);
//
// $diff = abs($diff);
?>
      <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="card">
          <div class="card-body card-pad pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-i18n="">Call volume</span></h3>
              <div class="row">
                <div class="col-md-8 col-sm-6"><span class="h3 text-nowrap"><?//=number_format($totalVisits) ?></span><span class="small"><?//=number_format($metrics[$visits + 2]) ?></span></div>
                <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?//=$pieces[0] ?> text-nowrap"><span class="material-icons"><?//=$pieces[1] ?></span> <?//=percent($diff) ?></span></div>
              </div>
              <div class="row">
                <div class="col-lg-12 col-md-12 col-sm-12"><span class="<?//=$pieces[0] ?> text-nowrap"><span class="material-icons"><?//=$pieces[1] ?></span></span><span class="text-nowrap"><!--Met objectve of 5 point decrease--></span></div>
              </div>
          </div>
        </div>
      </div>

    </div>

      <?php

      $diff = differ($totalCompVisitsPW, $totalVisitsW);
      $pos = posOrNeg($diff);
      $pieces = explode(":", $pos);
      //
      $diff = abs($diff);
    ?>

    <div class="row mb-2 gx-2">
      <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="card">
          <div class="card-body card-pad pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-i18n="">Total visits from all pages</span></h3>
              <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=number_format($totalVisitsW) ?></span><span class="small"><?//=number_format($totalCompVisits) ?></span></div>
                <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?=percent($diff) ?></span></div>
              </div>
          </div>
        </div>
      </div>
    </div>


    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Participant tasks</span></h3>
            <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

               <?php

              $qry = $prjTasks;

                if (count($qry) > 0) { ?>
                  <div class="table-responsive">
                    <table class="table table-striped dataTable no-footer">
                      <!-- <thead>
                        <tr>
                          <th data-i18n="task">Task</th>
                          <th data-i18n="change">>Change</th>
                          <th data-i18n="">Task Success Survey Completed</th>
                        </tr>
                      </thead> -->
                      <tbody>
                    <?php foreach ($qry as $row) { ?>
                        <tr>
                          <td><?=$row;?></td>
                        </tr>
                    <?php } ?>
                      </tbody>
                    </table>
                </div>
              <?php }
              else { ?>
                <p>No tasks associated with this project.</p>
              <?php
                  }
              ?>

            </div></div><div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div></div>
          </div>
        </div>
      </div>
    </div>


    <div class="row mb-4">
      <div class="col-lg-6 col-md-6">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Responses to  'Did you find what you were looking for?' on pages with page feedback question" data-bs-original-title="" title="" data-i18n="d3-dyfwywlf">Did you find what you were looking for?</span></h3>
              <div class="card-body pt-2" id="d3_dyfwywlf_barchart"></div>
                <!-- Did you find what you werel looking - D3 100% Stacked Bar chart -->
                <?php

                if ($pageFeedbackOnPages) {

                        $d3Data_DYFWYWLF_DateRanges = array($datesHeaderMonth[0][0].'-'.$datesHeaderMonth[0][1],$datesHeaderMonth[1][0].'-'.$datesHeaderMonth[1][1],$datesHeader[0][0].'-'.$datesHeader[0][1],$datesHeader[1][0].'-'.$datesHeader[1][1]); // previous $a1
                        $d3Data_DYFWYWLF_subgroups =  array("Yes","Yes","Yes","Yes","No","No","No","No"); // previous $b1
                        $d3Data_DYFWYWLF_data = array_slice($metrics, 0, 8); // previous $c1

                        for ($i = 0; $i < 4; ++$i) {
                          $final_array["dateRange"] = $d3Data_DYFWYWLF_DateRanges[$i];
                          $final_array["Yes"] = $d3Data_DYFWYWLF_data[$i];
                          $final_array["No"] = $d3Data_DYFWYWLF_data[$i+4];
                          $new_array[]=$final_array;
                        }
                        //$mydata = json_encode($new_array);
                        //just present the Weekly date range data - index 2 and 3 from new_array
                        $mydata = json_encode(array_slice($new_array, 2)); ;

                        $subgroups = json_encode(array("Yes", "No"));

                        //$groups = json_encode(array_unique($d3Data_DYFWYWLF_DateRanges));
                        //just present the Weekly date ranges
                        $groups = json_encode(array($d3Data_DYFWYWLF_DateRanges[2],$d3Data_DYFWYWLF_DateRanges[3]));

                        ?>
                        <script>

                        // set the dimensions and margins of the graph
                        width = parseInt(d3.select('#d3_dyfwywlf_barchart').style('width'), 10)
                        height = width / 1.5;
                        //alert("hellp");
                        var margin = {top: 10, right: 30, bottom: 30, left: 30},
                            width = width - margin.left - margin.right,
                            height = height - margin.top - margin.bottom,
                            legendHeight = 40;

                        // append the svg object to the body of the page
                        var svg_new = d3.select("#d3_dyfwywlf_barchart")
                          .append("svg")
                            .attr("width", width + margin.left + margin.right)
                            .attr("height", height + margin.top + margin.bottom + legendHeight)
                          .append("g")
                            .attr("transform",
                                  "translate(" + margin.left + "," + margin.top + ")");

                        // Parse the Data
                          var data = <?=$mydata?>;

                          //console.log(data)
                          //console.log(typeof data)
                          // List of subgroups = header of the csv files = soil condition here
                          //var subgroups = data.columns.slice(1)
                          var subgroups = <?=$subgroups?>;
                          //console.log(subgroups)
                          //console.log(typeof subgroups)

                          // List of groups = species here = value of the first column called group -> I show them on the X axis
                          //var groups = d3.map(data, function(d){return(d.group)}).keys()
                          var groups = <?=$groups?>;
                          //console.log(groups)
                          //console.log(typeof groups)

                          // Add X axis
                          var x = d3.scaleBand()
                              .domain(groups)
                              .range([0, width])
                              .padding([0.5]);
                          svg_new.append("g")
                            .attr("transform", "translate(0," + height + ")")
                            .call(d3.axisBottom(x).tickSizeOuter(0));

                          // Add Y axis
                          var y = d3.scaleLinear()
                            .domain([0, 100])
                            .range([ height, 0 ]);

                          // grid lines on Y axis
                          var yGrid = d3.axisLeft(y).tickSize(-width).tickFormat('').ticks(5);

                          //create  yGrid
                          svg_new.append('g')
                            .attr('class', 'axis-grid')
                            .call(yGrid);

                          //create Y axis
                          svg_new.append("g")
                            .call(d3.axisLeft(y).ticks(5));

                          // color palette = one color per subgroup
                          var color = d3.scaleOrdinal()
                            .domain(subgroups)
                            .range(['#345EA5','#F17F2B']);

                          // Normalize the data -> sum of each group must be 100!
                          dataNormalized = [];
                          data.forEach(function(d){
                            // Compute the total
                            tot = 0
                            for (i in subgroups){ name=subgroups[i] ; tot += +d[name]; }
                            // Now normalize
                            for (i in subgroups){ name=subgroups[i] ; d[name] = d[name] / tot * 100; }
                          });

                          //stack the data? --> stack per subgroup
                          var stackedData = d3.stack()
                            .keys(subgroups)
                            (data);
                          //console.log(stackedData)
                          // Show the bars
                          svg_new.append("g")
                            .selectAll("g")
                            // Enter in the stack data = loop key per key = group per group
                            .data(stackedData)
                            .enter().append("g")
                              .attr("fill", function(d) { return color(d.key); })
                              .selectAll("rect")
                              // enter a second time = loop subgroup per subgroup to add all rectangles
                              .data(function(d) { return d; })
                              .enter().append("rect")
                                .attr("x", function(d) { return x(d.data.dateRange); })
                                .attr("y", function(d) { return y(d[1]); })
                                .attr("height", function(d) { return y(d[0]) - y(d[1]); })
                                .attr("width",x.bandwidth());

                          svg_new.selectAll(".tick text")
                               .style("font-size","14px")
                               .style("fill","#666");

                          // D3 legend
                          //color.domain(d3.keys(data[0]).filter(function(key) { return key !== "dateRange"; }));
                          svg_new.append("g")
                             .attr("class", "legendOrdinal")
                             .attr("transform", "translate(0,"+(height+45)+")");

                          var legendOrdinal = d3.legendColor()
                           .shape("rect")
                           .shapePadding(100)
                           .orient('horizontal')
                           .labelAlign("start")
                           .scale(color);

                          svg_new.select(".legendOrdinal")
                             .call(legendOrdinal);

                        </script>



                        <details class="details-chart">
                          <summary data-i18n="view-data-table">View table data</summary>
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
                                  <td><?=number_format($metrics[$fwylfYes + 0]) ?></td>
                                  <td><?=number_format($metrics[$fwylfYes + 1]) ?></td>
                                  <td><?=number_format($metrics[$fwylfYes + 2]) ?></td>
                                  <td><?=number_format($metrics[$fwylfYes + 3]) ?></td>
                                </tr>

                                <tr>
                                  <td>FWYLF - No</td>
                                  <td><?=number_format($metrics[$fwylfNo + 0]) ?></td>
                                  <td><?=number_format($metrics[$fwylfNo + 1]) ?></td>
                                  <td><?=number_format($metrics[$fwylfNo + 2]) ?></td>
                                  <td><?=number_format($metrics[$fwylfNo + 3]) ?></td>
                                </tr>

                              </tbody>
                            </table>
                          </div>
                        </details>
                <?php  }

                else {
                  echo "None of the pages for this Project have a Page feedback tool!";
                }
                ?>
          </div>
        </div>
      </div>
      <div class="col-lg-6 col-md-6">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Reponses to  'What was wrong?' question after visitors clicked 'No' on the 'Did you find what you were looking for?' question." data-bs-original-title="" title="" data-i18n="d3-www">What was wrong?</span></h3>
              <div class="card-body pt-2" id="d3_www_barchart"></div>
                <div id="d3_www_legend"></div>
                <!-- Did you find what you werel looking - WHAT WAS WRONG D3 100% Stacked Bar chart -->
                <?php

                    if ($pageFeedbackOnPages) {

                          $d3Data_DYFWYWLF_DateRanges = array($datesHeaderMonth[0][0].'-'.$datesHeaderMonth[0][1],$datesHeaderMonth[1][0].'-'.$datesHeaderMonth[1][1],$datesHeader[0][0].'-'.$datesHeader[0][1],$datesHeader[1][0].'-'.$datesHeader[1][1]); // previous $a1
                          $d3Data_WWW_subgroups =  array("Yes","Yes","Yes","Yes","No","No","No","No"); // previous $b1
                          $d3Data_WWW_data = $metrics2; // previous $c1

                          for ($i = 0; $i < 4; ++$i) {
                            $final_www_array["dateRange"] = $d3Data_DYFWYWLF_DateRanges[$i];
                            $final_www_array["I can't find the info"] = $d3Data_WWW_data[$i];
                            $final_www_array["Other reason"] = $d3Data_WWW_data[$i+4];
                            $final_www_array["Info is hard to understand"] = $d3Data_WWW_data[$i+8];
                            $final_www_array["Error/something didn't work"] = $d3Data_WWW_data[$i+12];
                            $new_www_array[]=$final_www_array;

                          }
                          //$my_www_data = json_encode($new_www_array);
                          //just present the Weekly date range data - index 2 and 3 from new_array
                          $my_www_data = json_encode(array_slice($new_www_array, 2));

                          $subgroups_www = json_encode(array("I can't find the info", "Other reason","Info is hard to understand","Error/something didn't work"));

                          //$groups_www = json_encode(array_unique($d3Data_DYFWYWLF_DateRanges));
                          //just present the Weekly date ranges
                          $groups_www = json_encode(array($d3Data_DYFWYWLF_DateRanges[2],$d3Data_DYFWYWLF_DateRanges[3]));

                          ?>
                          <script>

                          // set the dimensions and margins of the graph
                          width = parseInt(d3.select('#d3_www_barchart').style('width'), 10)
                          height = width / 1.5;
                          //alert("hellp");
                          var margin = {top: 10, right: 30, bottom: 30, left: 30},
                              width = width - margin.left - margin.right,
                              height = height - margin.top - margin.bottom,
                              //legendHeight = 40;
                              //legeng height on WWW legend
                              legendHeight = 0;

                          // append the svg object to the body of the page
                          var svg = d3.select("#d3_www_barchart")
                            .append("svg")
                              .attr("width", width + margin.left + margin.right)
                              .attr("height", height + margin.top + margin.bottom + legendHeight)
                            .append("g")
                              .attr("transform",
                                    "translate(" + margin.left + "," + margin.top + ")");


                            var data = <?=$my_www_data?>;

                            console.log("www data:")
                            console.log(data)
                            console.log(typeof data)
                            // List of subgroups = header of the csv files = soil condition here
                            //var subgroups = data.columns.slice(1)
                            //var subgroups = data.columns.slice(1)
                            var subgroups = <?=$subgroups_www?>;
                            console.log("www subgroups:")
                            console.log(subgroups)
                            console.log(typeof subgroups)

                            // List of groups = species here = value of the first column called group -> I show them on the X axis
                            //var groups = d3.map(data, function(d){return(d.group)}).keys()
                            var groups = <?=$groups_www?>;
                            console.log("www groups:")
                            console.log(groups)
                            console.log(typeof groups)

                            // Add X axis
                            var x = d3.scaleBand()
                                .domain(groups)
                                .range([0, width])
                                .padding([0.5])
                            svg.append("g")
                              //.attr("class", "axis_labels")
                              .attr("transform", "translate(0," + height + ")")
                              .call(d3.axisBottom(x).tickSizeOuter(0));

                            // Add Y axis
                            var y = d3.scaleLinear()
                              .domain([0, 100])
                              .range([ height, 0 ]);

                            // grid lines on Y axis
                            var yGrid = d3.axisLeft(y).tickSize(-width).tickFormat('').ticks(5);

                            //create  yGrid
                            svg.append('g')
                              .attr('class', 'axis-grid')
                              //.attr('transform', 'translate(0,' + height + ')')
                              .call(yGrid);

                            // create Y axis
                            svg.append("g")
                              //.attr("class", "axis_labels")
                              .call(d3.axisLeft(y).ticks(5));



                            // color palette = one color per subgroup
                            var color = d3.scaleOrdinal()
                              .domain(subgroups)
                              .range(['#345EA5','#6CB5F3','#36A69A','#F8C040'])

                            // Normalize the data -> sum of each group must be 100!

                            dataNormalized = []
                            data.forEach(function(d){
                              // Compute the total
                              tot = 0
                              for (i in subgroups){ name=subgroups[i] ; tot += +d[name]; }
                              // Now normalize
                              for (i in subgroups){ name=subgroups[i] ; d[name] = d[name] / tot * 100; }
                            })

                            //stack the data? --> stack per subgroup
                            var stackedData = d3.stack()
                              .keys(subgroups)
                              (data)
                            //console.log(stackedData)
                            // Show the bars
                            svg.append("g")
                              .selectAll("g")
                              // Enter in the stack data = loop key per key = group per group
                              .data(stackedData)
                              .enter().append("g")
                                .attr("fill", function(d) { return color(d.key); })
                                .selectAll("rect")
                                // enter a second time = loop subgroup per subgroup to add all rectangles
                                .data(function(d) { return d; })
                                .enter().append("rect")
                                  .attr("x", function(d) { return x(d.data.dateRange); })
                                  .attr("y", function(d) { return y(d[1]); })
                                  .attr("height", function(d) { return y(d[0]) - y(d[1]); })
                                  .attr("width",x.bandwidth())

                            svg.selectAll(".tick text")
                             //.attr("class","axis_labels")
                             .style("font-size","14px")
                             .style("fill","#666");

                              // New D3 legend (if the legend labels are long and wont fit in a single line)
                              var legend = d3.select('#d3_www_legend').selectAll("legend")
                                  .data(subgroups);

                              var legend_cells = legend.enter().append("div")
                                .attr("class","legend");

                              var p1 = legend_cells.append("p").attr("class","legend_field");
                              p1.append("span").attr("class","legend_color").style("background",function(d,i) { return color(i) } );
                              p1.insert("text").text(function(d,i) { return d } );


                          </script>

                      <details class="details-chart">
                        <summary data-i18n="view-data-table">View table data</summary>
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
                                    <td><?=number_format($metrics2[$fwylfICantFindTheInfo + 0]) ?></td>
                                    <td><?=number_format($metrics2[$fwylfICantFindTheInfo + 1]) ?></td>
                                    <td><?=number_format($metrics2[$fwylfICantFindTheInfo + 2]) ?></td>
                                    <td><?=number_format($metrics2[$fwylfICantFindTheInfo + 3]) ?></td>
                                  </tr>

                                  <tr>
                                    <td>FWYLF - Other reason</td>
                                    <td><?=number_format($metrics2[$fwylfOtherReason + 0]) ?></td>
                                    <td><?=number_format($metrics2[$fwylfOtherReason + 1]) ?></td>
                                    <td><?=number_format($metrics2[$fwylfOtherReason + 2]) ?></td>
                                    <td><?=number_format($metrics2[$fwylfOtherReason + 3]) ?></td>
                                  </tr>

                                  <tr>
                                    <td>FWYLF - Information hard to understand</td>
                                    <td><?=number_format($metrics2[$fwylfInfoHardToUnderstand + 0]) ?></td>
                                    <td><?=number_format($metrics2[$fwylfInfoHardToUnderstand + 1]) ?></td>
                                    <td><?=number_format($metrics2[$fwylfInfoHardToUnderstand + 2]) ?></td>
                                    <td><?=number_format($metrics2[$fwylfInfoHardToUnderstand + 3]) ?></td>
                                  </tr>

                                  <tr>
                                    <td>FWYLF - Error/something didn't work</td>
                                    <td><?=number_format($metrics2[$fwylfError + 0]) ?></td>
                                    <td><?=number_format($metrics2[$fwylfError + 1]) ?></td>
                                    <td><?=number_format($metrics2[$fwylfError + 2]) ?></td>
                                    <td><?=number_format($metrics2[$fwylfError + 3]) ?></td>
                                  </tr>

                                </tbody>
                              </table>

                        </div>
                      </details>

                    <?php  }

                    else {
                      echo "None of the pages for this Project have a Page feedback tool!";
                    }
                    ?>
          </div>
        </div>
      </div>
    </div>



      <?php  //} //if (empty($tmp))  ?>
<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
