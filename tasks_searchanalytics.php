
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
require 'vendor/autoload.php';
require_once ('./php/get_aa_data.php');
use TANIOS\Airtable\Airtable;

include_once "php/lib/sqlite/DataInterface.php";
include_once 'php/Utils/Date.php';

use Utils\DateUtils;

//$startTime = microtime(true);

$taskId = $_GET['taskId'] ?? "recXkhR8zOWnR83TU";

$dr = $_GET['dr'] ?? "week";

$lang = $_GET['lang'] ?? "en";

$db = new DataInterface();
$taskData = $db->getTaskById($taskId)[0];

$taskPages = $db->getPagesByTaskId($taskId, ['Url']);
$taskPages = array_column($taskPages, 'Url');

$taskProjects = $db->getProjectsByTaskId($taskId, ['title']);

$relatedProjects = array_column($taskProjects, 'title');

$dateUtils = new DateUtils();

$weeklyDatesHeader = $dateUtils->getWeeklyDates('header');
?>

<h1 class="visually-hidden">Usability Performance Dashboard</h1>
<div class="back_link"><span class="material-icons align-top">west</span> <a href="./tasks_home.php" alt="Back to Tasks home page">Tasks</a></div>

<h2 class="h3 pt-2 pb-2" data-i18n=""><?=$taskData['Task']?></h2>

<div class="page_header back_link">
        <span id="page_project">
              <?php
              if (count($taskProjects) > 0) {
                  echo '<span class="material-icons align-top">folder</span>';
              }

              echo implode(", ", array_map(function($project) {
                  return '<a href="./projects_pagefeedback.php?projectId='.$project['id'].'" alt="Project: '.$project['title'].'" target="_blank">' . $project['title'] . '</a>';
                  //SWITCH TO THIS line after the summary page is done
                  //return '<a href="./projects_summary.php?prj='.$project.'" alt="Project: '.$project.'">' . $project . '</a>';
              }, $taskProjects));
              ?>
         </span>
</div>

<div class="tabs sticky">
    <ul>
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="./tasks_summary.php?taskId=<?=$taskId?>" data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./tasks_webtraffic.php?taskId=<?=$taskId?>" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="./tasks_pagefeedback.php?taskId=<?=$taskId?>" data-i18n="tab-pagefeedback">Page feedback</a></li>
        <li <?php if ($tab=="calldrivers") {echo "class='is-active'";} ?>><a href="./tasks_calldrivers.php?taskId=<?=$taskId?>" data-i18n="tab-calldrivers">Call drivers</a></li>
        <li <?php if ($tab=="uxtests") {echo "class='is-active'";} ?>><a href="./tasks_uxtests.php?taskId=<?=$taskId?>" data-i18n="tab-uxtests">UX tests</a></li>
    </ul>
</div>

<?php

// Adobe Analytics

if (!isset($_SESSION['CREATED']))
{
    $_SESSION['CREATED'] = time();
    require_once ('./php/getToken.php');
}
else if (time() - $_SESSION['CREATED'] > 86400)
{
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
    require_once ('./php/getToken.php');
}
if (isset($_SESSION["token"]))
{
    require_once ('./php/api_post.php');
    $config = include ('./php/config-aa.php');
    $data = include ('./php/data-aa.php');
}

?>

<div class="row mb-4 mt-1">
    <div class="dropdown">
        <button type="button" class="btn bg-white border border-1 dropdown-toggle" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> <span data-i18n="dr-lastweek">Last week</span></button>
        <span class="text-secondary ps-2 text-nowrap dates-header-week"><?=$weeklyDatesHeader['current']['start']?> - <?=$weeklyDatesHeader['current']['end']?></span>
        <span class="text-secondary ps-2 text-nowrap dates-header-week" data-i18n="compared_to"> compared to </span>
        <span class="text-secondary ps-2 text-nowrap dates-header-week"><?=$weeklyDatesHeader['previous']['start']?> - <?=$weeklyDatesHeader['previous']['end']?></span>

        <ul class="dropdown-menu" aria-labelledby="range-button" style="">
            <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
            <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
        </ul>

    </div>
</div>

<?php
//
// $r = new ApiClient($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token']);
//
// $temp = ['aa-pages-smmry-metrics', 'aa-pages-smmry-fwylf'];
// $result = array();
// $j = array();
// $allAPI = array();
// $allj = array();
//
// $weeklyDatesAA = $dateUtils->getWeeklyDates('aa');
// $monthlyDatesAA = $dateUtils->getMonthlyDates('aa');
//
// foreach ($temp as $t)
// {
//
//     foreach ($taskPages as $page)
//     {
//         $json = $data[$t];
//
//         $json = sprintf($json, $page);
//
//         $json = str_replace(array(
//             "*previousMonthStart*",
//             "*previousMonthEnd*",
//             "*monthStart*",
//             "*monthEnd*",
//             "*previousWeekStart*",
//             "*previousWeekEnd*",
//             "*weekStart*",
//             "*weekEnd*"
//         ) , array(
//             $monthlyDatesAA['previous']['start'],
//             $monthlyDatesAA['previous']['end'],
//             $monthlyDatesAA['current']['start'],
//             $monthlyDatesAA['current']['end'],
//             $weeklyDatesAA['previous']['start'],
//             $weeklyDatesAA['previous']['end'],
//             $weeklyDatesAA['current']['start'],
//             $weeklyDatesAA['current']['end'],
//         ) , $json);
//
//         $response = get_aa_data($json, $r);
//         $result[$page] = json_decode($response,true);
//         $j[] = $json;
//
//     }
//
//     $allAPI[] = $result;
//     $allj[$t] = $j;
//
// }
//
// $result = $allAPI;
//
//
// // -----------------------------------------------------------------------
// // METRICS query (Visit metrics and DYFWYWLF- Yes and No answers)
// // -----------------------------------------------------------------------
//
// $metrics = array_column_recursive($result[0], "filteredTotals");
//
// $sum_metrics = array_reduce($metrics, function($sums, $row) {
//     for ($i = 0; $i < count($row); $i++) {
//         $row[$i] = $row[$i] + $sums[$i];
//     }
//
//     return $row;
// }, array_map(fn($item) => 0, $metrics[0] ?? []));
//
// $tmp = array_slice($sum_metrics, 0, 8);
//
// // -----------------------------------------------------------------------
// // DYFWYWLF query (What went wrong answers)
// // -----------------------------------------------------------------------
//
// $metrics2 = array_column_recursive($result[1], "filteredTotals");
//
// $sum_metrics2 = array_reduce($metrics2, function($sums, $row) {
//     for ($i = 0; $i < count($row); $i++) {
//         $row[$i] = $row[$i] + $sums[$i];
//     }
//
//     return $row;
// }, array_map(fn($item) => 0, $metrics2[0] ?? []));
//
//
// //DOES THIS PAGE HAS PAGE FEEDBACK TOOL OR NOT
// if (empty($tmp)) {
//     echo "None of the pages for this Task have a Page feedback tool!";
// }
// else {
//     $metrics = $sum_metrics;
//     $metrics2 = $sum_metrics2;
//
//     $fwylfYes = 0;
//     $fwylfNo = 4;
//     $pv = 8;
//     $visitors = 12;
//     $visits = 16;
//
//     function differ($old, $new)
//     {
//         return (($new - $old) / $old);
//     }
//
//     function numDiffer($old, $new)
//     {
//         return ($new - $old);
//     }
//
//     function posOrNeg($num)
//     {
//         if ($num > 0) return 'text-success:arrow_upward';
//         else if ($num == 0) return 'text-warning:horizontal_rule';
//         else return 'text-danger:arrow_downward';
//     }
//
//     function posOrNeg2($num)
//     {
//         if ($num > 0) return 'text-success:+';
//         else if ($num == 0) return 'text-warning:';
//         else return 'text-danger:-';
//     }
//
//     function percent($num)
//     {
//         return round($num * 100, 0) . '%';
//     }
//
//     $fwylfICantFindTheInfo = 0;
//     $fwylfOtherReason = 4;
//     $fwylfInfoHardToUnderstand = 8;
//     $fwylfError = 12;
//     ?>

     <?php
//     // AIRTABLE
//
//     $iso = 'Y-m-d\TH:i:s.v';
//
//     $previousWeekStart = strtotime("last sunday midnight", strtotime("-2 week +1 day"));
//     $previousWeekEnd = strtotime("next sunday", $previousWeekStart);
//     $previousWeekStart = date($iso, $previousWeekStart);
//     $previousWeekEnd = date($iso, $previousWeekEnd);
//
//     $weekStart = strtotime("last sunday midnight", strtotime("-1 week +1 day"));
//     $weekEnd = strtotime("next sunday", $weekStart);
//     $weekStart = date($iso, $weekStart);
//     $weekEnd = date($iso, $weekEnd);
//
//     $monthStart = (new DateTime("first day of last month midnight"))->format($iso);
//     $monthEnd = (new DateTime("first day of this month midnight"))->format($iso);
//
//     $previousMonthStart = (new DateTime("first day of -2 month midnight"))->format($iso);
//     $previousMonthEnd = $monthStart;
//
//
//     // Get date for GSC
//     $iso = 'Y-m-d';
//
//     $startLastGSC = (new DateTime($previousWeekStart))->format($iso);
//     $endLastGSC = (new DateTime($previousWeekEnd))->modify('-1 days')
//         ->format($iso);
//     $startGSC = (new DateTime($weekStart))->format($iso);
//     $endGSC = (new DateTime($weekEnd))->modify('-1 days')
//         ->format($iso);
//
//     $dates = [[$startLastGSC, $endLastGSC], [$startGSC, $endGSC]];
//
//     // Get date for header
//     $iso = 'M d';
//
//     $startLastHeader = (new DateTime($previousWeekStart))->format($iso);
//     $endLastHeader = (new DateTime($previousWeekEnd))->modify('-1 days')
//         ->format($iso);
//     $startHeader = (new DateTime($weekStart))->format($iso);
//     $endHeader = (new DateTime($weekEnd))->modify('-1 days')
//         ->format($iso);
//
//     // Weekly date ranges for the Header
//     $datesHeader = [[$startLastHeader, $endLastHeader], [$startHeader, $endHeader]];
//
//
//     $monthStartHeader = (new DateTime("first day of last month midnight"))->format($iso);
//     $monthEndHeader = (new DateTime("last day of last month midnight"))->format($iso);
//
//     $previousMonthStartHeader = (new DateTime("first day of -2 month midnight"))->format($iso);
//     $previousMonthEndHeader = (new DateTime("last day of -2 month midnight"))->format($iso);
//
//     // Monthly date ranges for the Header
//     $datesHeaderMonth = [[$previousMonthStartHeader, $previousMonthEndHeader], [$monthStartHeader, $monthEndHeader]];
//
//
//     // AIRTABLE CONNECTION - SETUP REUQEST AND PARSE RESPONSE
//     //--------------------------------------------------------------
//     $s = $startLastGSC;
//     $e = $endLastGSC;
//     $s1 = $startGSC;
//     $e1 = $endGSC;
//
//     $config = include ('./php/config-at.php');
//     $airtable = new Airtable($config['feedback']);
//
//     // -----------------------------------------------------------------------------------------------
//     // GET DATA FROM "Page Feedback" (CRA view) table filtered by date range - last two weekStart
//     // -----------------------------------------------------------------------------------------------
//
//     $listOfPages = array_map(fn($url) => "(URL = 'https://$url')", $taskPages);
//
//     $paramPages = implode(",", $listOfPages);
//
//     //echo $url;
//     $params = array(
//         // for get multiple url's or Projects from Airtable listOfPages
//         "filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')), OR($paramPages))",
//         "view" => "CRA"
//     );
//     $table = 'Page feedback';
//
//     $fullArray = [];
//     $request = $airtable->getContent($table, $params);
//     do
//     {
//         $response = $request->getResponse();
//         $fullArray = array_merge($fullArray, ($response->records));
//     }
//     while ($request = $response->next());
//
//     $allData = ( json_decode(json_encode($fullArray), true));
//
//     $all_fields = array();
//
//     // if there's data (record exist)
//     if ( count( $allData ) > 0 ) {
//         $re = $allData;
//
//         //weekly data range
//         $rangeStartW = strtotime($s1);
//         $rangeEndW = strtotime($e1);
//         //previous week range
//         $rangeStartPW = strtotime($s);
//         $rangeEndPW = strtotime($e);
//
//         //filter array by date ranges
//         $WeeklyData = array_filter( $re, function($var) use ($rangeStartW, $rangeEndW) {
//             $utime = strtotime($var['fields']['Date']);
//             return $utime <= $rangeEndW && $utime >= $rangeStartW;
//         });
//
//         $PWeeklyData = array_filter( $re, function($var) use ($rangeStartPW, $rangeEndPW) {
//             $utime = strtotime($var['fields']['Date']);
//             return $utime <= $rangeEndPW && $utime >= $rangeStartPW;
//         });
//
//         if (( count( $WeeklyData ) > 0 ) && ( count( $PWeeklyData ) > 0 )) {
//
//             // Get just the ['fields'] array of each record -  as a separate array - $all_fields
//             $all_fields = array_column_recursive($WeeklyData, 'fields');
//             $all_fieldsPW = array_column_recursive($PWeeklyData, 'fields');
//
//             //we are grouping the pages by URL instead of Page Title, cause some pages might not have titles listes in the table
//             //stil, the main idea is to group the pages by some unique page element
//
//             foreach ( $all_fields as &$item ) {
//                 $item["Tag"] = implode($item['Lookup_tags']);
//             }
//
//             foreach ( $all_fieldsPW as &$item ) {
//                 $item["Tag"] = implode($item['Lookup_tags']);
//             }
//
//             $fieldsByGroupTag = group_by('Tag', $all_fields);
//             $fieldsByGroupTagPW = group_by('Tag', $all_fieldsPW);
//
//             foreach ( $fieldsByGroupTagPW as &$item ) {
//                 $item["Total tag comments"] = count($item);
//             }
//             foreach ( $fieldsByGroupTag as &$item ) {
//                 $item["Total tag comments"] = count($item);
//             }
//
//             $d3TotalFeedbackByPageSuccess = 1;
//
//         }
//     } else {
//         $d3TotalFeedbackByPageSuccess = 0;
//     }

    ?>


<?php //} ?>
<?php
//$endTime = microtime(true);

//$timeElapsed = round($endTime - $startTime, 2);

//echo "Page loaded in: $timeElapsed seconds";
?>
<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
