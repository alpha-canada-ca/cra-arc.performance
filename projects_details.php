
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

//$url = $_REQUEST['url'];

//echo $url;

if (isset($_GET['prj'])) {
$prj = $_GET['prj'];
}
else {
//$url = "https://www.canada.ca/en/revenue-agency/services/benefits/recovery-benefit/crb-how-apply.html";
//$prj = "Task Performance Indicators - (May 2021)";
//$prj = "CEWS Spreadsheet ";
$prj = "CRB - Post-launch test ";

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


// echo "AT url for tasks<pre>";
// print_r($fullArray[0]['fields']);
// echo "</pre>";

// $prjStatus = $fullArray[0]['fields']['Status'];//['records'];
// $prjTasks = array_column_recursive($weeklyRe,"Lookup_Tasks");
// $prjPages = array_column_recursive($weeklyRe,"Lookup_Pages");

// // use array_values to re-index the array
$prjTasks = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Lookup_Tasks"))));
$prjPages = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Lookup_Pages"))));
$prjLeads = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Project Lead"))));
$prjStartDate = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Date"))));
$prjLaunchDate = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Launch Date"))));
$prjStatus = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Status"))));
// $relatedTasks = $fullArray[0]['fields']['Lookup_Tasks'];//['records'];
// $relatedProjects = $fullArray[0]['fields']['Projects'];

// echo "Leads<pre>";
// print_r($prjLeads);
// echo "</pre>";
// echo "<br/>Start<pre>";
// print_r($prjStartDate);
// echo "</pre>";
// echo "<br/>Launch<pre>";
// print_r($prjLaunchDate);
// echo "</pre>";

// echo "All Tasks<pre>";
// print_r($prjTasks);
// echo "</pre>";
//
// echo "All Pages<pre>";
// print_r($prjPages);
// echo "</pre>";
// //
// echo "FLATTEN - All Pages<pre>";
// print_r(array_flatten($prjPages));
// echo "</pre>";
//
// echo "FLATTEN UNIQUE - All Pages <pre>";

// print_r(array_values(array_unique(array_flatten($prjPages))));
// echo "</pre>";



// echo "Projects<pre>";
// print_r($relatedProjects);
// echo "</pre>";

// if ($relatedProjects == null) {echo "null projects";}
// else {echo $relatedProjects; }


?>

<h1 class="visually-hidden">Usability Performance Dashboard</h1>
    <div class="back_link"><span class="material-icons align-top">west</span> <a href="./projects_home.php" alt="Back to Projects home page">Projects</a></div>

    <h2 class="h3 pt-2 pb-2" data-i18n=""><?=$prj?> <span class="badge rounded-pill bg-primary" style="margin-left:30px; font-weight:lighter"><?=$prjStatus[0];?></span></h2>

    <div class="tabs sticky">
      <ul>
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="./projects_summary.php?prj=<?=$prj?>" data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./projects_webtraffic.php?prj=<?=$prj?>" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="./projects_searchanalytics.php?prj=<?=$prj?>" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="./projects_pagefeedback.php?prj=<?=$prj?>" data-i18n="tab-pagefeedback">Page feedback</a></li>
        <li <?php if ($tab=="calldrivers") {echo "class='is-active'";} ?>><a href="./projects_calldrivers.php?prj=<?=$prj?>" data-i18n="tab-calldrivers">Call drivers</a></li>
        <li <?php if ($tab=="uxtests") {echo "class='is-active'";} ?>><a href="./projects_uxtests.php?prj=<?=$prj?>" data-i18n="tab-uxtests">UX tests</a></li>
        <li <?php if ($tab=="details") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-details">Details</a></li>
      </ul>
    </div>

 <?php


// if ($url == null) {
// 	$url = "https://www.canada.ca/en/revenue-agency/services/benefits/recovery-benefit/crb-how-apply.html";
// }

//$url_components = parse_url($url);
// echo "<pre>";
// print_r($url_components);
// echo "</pre>";
// echo $url;
// echo "<br>".$dr;
// echo "<br>".$lang;

 // Use parse_str() function to parse the string passed via URL
//parse_str($url_components['query'], $params);

// Display result
// echo "<pre>";
// print_r($params);
// echo "</pre>";

// require 'vendor/autoload.php';
// use TANIOS\Airtable\Airtable;

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
                // // AIRTABLE
                //
                // $iso = 'Y-m-d\TH:i:s.v';
                //
                // $previousWeekStart = strtotime("last sunday midnight", strtotime("-2 week +1 day"));
                // $previousWeekEnd = strtotime("next sunday", $previousWeekStart);
                // $previousWeekStart = date($iso, $previousWeekStart);
                // $previousWeekEnd = date($iso, $previousWeekEnd);
                //
                // $weekStart = strtotime("last sunday midnight", strtotime("-1 week +1 day"));
                // $weekEnd = strtotime("next sunday", $weekStart);
                // $weekStart = date($iso, $weekStart);
                // $weekEnd = date($iso, $weekEnd);
                //
                // $monthStart = (new DateTime("first day of last month midnight"))->format($iso);
                // $monthEnd = (new DateTime("first day of this month midnight"))->format($iso);
                //
                // $previousMonthStart = (new DateTime("first day of -2 month midnight"))->format($iso);
                // $previousMonthEnd = $monthStart;
                //
                // // echo $monthStart."<br>";
                // // echo $monthEnd."<br>";
                // // echo $previousMonthStart."<br>";
                // // echo $previousMonthEnd."<br>";
                //
                // // Get date for GSC
                // $iso = 'Y-m-d';
                //
                // $startLastGSC = (new DateTime($previousWeekStart))->format($iso);
                // $endLastGSC = (new DateTime($previousWeekEnd))->modify('-1 days')
                //     ->format($iso);
                // $startGSC = (new DateTime($weekStart))->format($iso);
                // $endGSC = (new DateTime($weekEnd))->modify('-1 days')
                //     ->format($iso);
                //
                // $dates = [[$startLastGSC, $endLastGSC], [$startGSC, $endGSC]];
                // // echo "<pre>";
                // // print_r($dates);
                // // echo "</pre>";
                //
                // // Get date for header
                // $iso = 'M d';
                //
                // $startLastHeader = (new DateTime($previousWeekStart))->format($iso);
                // $endLastHeader = (new DateTime($previousWeekEnd))->modify('-1 days')
                //     ->format($iso);
                // $startHeader = (new DateTime($weekStart))->format($iso);
                // $endHeader = (new DateTime($weekEnd))->modify('-1 days')
                //     ->format($iso);
                //
                // // Weekly date ranges for the Header
                // $datesHeader = [[$startLastHeader, $endLastHeader], [$startHeader, $endHeader]];
                //
                //
                // $monthStartHeader = (new DateTime("first day of last month midnight"))->format($iso);
                // $monthEndHeader = (new DateTime("last day of last month midnight"))->format($iso);
                //
                // $previousMonthStartHeader = (new DateTime("first day of -2 month midnight"))->format($iso);
                // $previousMonthEndHeader = (new DateTime("last day of -2 month midnight"))->format($iso);
                //
                // // Monthly date ranges for the Header
                // $datesHeaderMonth = [[$previousMonthStartHeader, $previousMonthEndHeader], [$monthStartHeader, $monthEndHeader]];
                //


                // AIRTABLE CONNECTION - SETUP REUQEST AND PARSE RESPONSE
                //--------------------------------------------------------------
                $s = $startLastGSC;
                $e = $endLastGSC;
                $s1 = $startGSC;
                $e1 = $endGSC;


                $config = include ('./php/config-at.php');
                $airtable = new Airtable($config['feedback']);

                // -----------------------------------------------------------------------------------------------
                // GET DATA FROM "Page Feedback" (CRA view) table filtered by date range - last two weekStart
                // -----------------------------------------------------------------------------------------------

                foreach ($prjPages as $page) {
                  $listOfPages[] = "(URL = 'https://$page')";
                }
                // echo count($listOfPages);
                // var_dump(implode(",", $listOfPages));

                $paramPages = implode(",", $listOfPages);
                //$paramPages = "(URL = 'https://www.canada.ca/fr/agence-revenu/services/prestations/prestation-relance-economique.html'),(URL = 'https://www.canada.ca/en/revenue-agency/services/benefits/recovery-benefit/crb-periods-apply.html')";

                //echo $url;
                $params = array(
                    //"filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')))",
                    //"filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')), (URL = 'https://www.canada.ca/en/revenue-agency/services/benefits/recovery-benefit/crb-how-apply.html'))",
                    //"filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')), (URL = '$url'))",
                    // for get multiple url's or Projects from Airtable listOfPages
                    //"filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')), OR((URL = '$url'),(URL = 'https://www.canada.ca/en/revenue-agency/services/benefits/recovery-benefit/crb-how-much.html')))",
                    "filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')), OR($paramPages))",
                    "view" => "CRA"
                );
                $table = 'Page feedback';

                $fullArray = [];
                $request = $airtable->getContent($table, $params);
                do
                {
                    $response = $request->getResponse();
                    $fullArray = array_merge($fullArray, ($response->records));
                }
                while ($request = $response->next());

                $allData = ( json_decode(json_encode($fullArray), true));//['records'];

                // echo "<pre>";
                // print_r($paramPages);
                // echo "</pre>";
                // echo "ALL DATA<pre>";
                // print_r($allData);
                // echo "</pre>";

                // if there's data (record exist)
                if ( count( $allData ) > 0 ) {
                      // do things here
                      // echo "total pagefedbacks for this page: ";
                      // echo count($allData);
                      $re = $allData;
                      // echo "<pre>";
                      // print_r($re);
                      // echo "</pre>";

                      //weekly data range
                      $rangeStartW = strtotime($s1);
                      $rangeEndW = strtotime($e1);
                      //previous week range
                      $rangeStartPW = strtotime($s);
                      $rangeEndPW = strtotime($e);

                      //filter array by date ranges
                      $WeeklyData = array_filter( $re, function($var) use ($rangeStartW, $rangeEndW) {
                         $utime = strtotime($var['fields']['Date']);
                         return $utime <= $rangeEndW && $utime >= $rangeStartW;
                      });

                      $PWeeklyData = array_filter( $re, function($var) use ($rangeStartPW, $rangeEndPW) {
                         $utime = strtotime($var['fields']['Date']);
                         return $utime <= $rangeEndPW && $utime >= $rangeStartPW;
                      });


                      if (( count( $WeeklyData ) > 0 ) && ( count( $PWeeklyData ) > 0 )) {

                            // Get just the ['fields'] array of each record -  as a separate array - $all_fields
                            $all_fields = array_column_recursive($WeeklyData, 'fields');
                            $all_fieldsPW = array_column_recursive($PWeeklyData, 'fields');

                            //we are grouping the pages by URL instead of Page Title, cause some pages might not have titles listes in the table
                            //stil, the main idea is to group the pages by some unique page element

                            foreach ( $all_fields as &$item ) {
                              $item["Tag"] = implode($item['Lookup_tags']);
                            }

                            foreach ( $all_fieldsPW as &$item ) {
                              $item["Tag"] = implode($item['Lookup_tags']);
                            }

                            // echo count($all_fields);
                            // echo "<br><br><pre>";
                            // print_r($all_fields);
                            // echo "</pre><br></br>";


                            $fieldsByGroupTag = group_by('Tag', $all_fields);
                            $fieldsByGroupTagPW = group_by('Tag', $all_fieldsPW);

                            //

                            foreach ( $fieldsByGroupTagPW as &$item ) {
                              $item["Total tag comments"] = count($item);
                            }
                            foreach ( $fieldsByGroupTag as &$item ) {
                              $item["Total tag comments"] = count($item);
                            }

                            // echo count($fieldsByGroupTag);
                            // echo "<br><br><pre>";
                            // print_r($fieldsByGroupTag);
                            // echo "</pre><br></br>";
                            $d3TotalFeedbackByPageSuccess = 1;

                      } //if (( count( $WeeklyData ) > 0 ) && ( count( $PWeeklyData ) > 0 ))


                } //if count($allData) > 0

                else {
                  $d3TotalFeedbackByPageSuccess = 0;
                }



                ?>

            <div class="row mb-4">
              <div class="col-lg-12 col-md-12">
                <div class="card">
                  <div class="card-body pt-2">
                    <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Project description</span></h3>
                        <p></p>
                  </div>
                </div>
              </div>
            </div>

            <div class="row mb-4">
              <div class="col-lg-12 col-md-12">
                <div class="card">
                  <div class="card-body pt-2">
                    <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Members</span></h3>

                    <div class="table-responsive">
                      <table class="table table-striped dataTable no-footer">
                        <thead>
                          <tr>
                            <th data-i18n="">Role</th>
                            <th data-i18n="">Name</th>
                            <th data-i18n="">Product</th>
                          </tr>
                        </thead>
                        <tbody>

                          <tr>
                            <td>Project Lead</td>
                            <td><?=$prjLeads[0];?></td>
                            <td></td>
                          </tr>
                        </tbody>
                      </table>
                    </div>

                    </div></div>

                    <div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div>
                  </div>
                </div>

            <div class="row mb-4">
              <div class="col-lg-12 col-md-12">
                <div class="card">
                  <div class="card-body pt-2">
                    <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Timeline</span></h3>
                        <div>
                            <p>Start Date: <?=date("M d, Y", strtotime($prjStartDate[0]))?></p>
                            <p>Launch Date: <?=date("M d, Y", strtotime($prjLaunchDate[0]))?></p>
                            <p>Completed:</p>
                            <p>Year review:</p>
                        </div>
                    </div></div><div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div>
                  </div>
                </div>
              <!-- </div>
            </div> -->

      <?php  //} //if (empty($tmp))  ?>
<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
