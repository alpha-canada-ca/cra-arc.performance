
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

<h1 class="visually-hidden">Usability Performance Dashboard</h1>

    <h2 class="h3 pt-2 pb-2" data-i18n="overview-title">Overview of CRA website</h2>

    <div class="tabs sticky">
      <ul>
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./overview_webtraffic.php" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="./overview_searchanalytics.php" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="./overview_pagefeedback.php" data-i18n="tab-pagefeedback">Page feedback</a></li>
        <li <?php if ($tab=="calldrivers") {echo "class='is-active'";} ?>><a href="./overview_calldrivers.php" data-i18n="tab-calldrivers">Call drivers</a></li>
        <li <?php if ($tab=="uxtests") {echo "class='is-active'";} ?>><a href="./overview_uxtests.php" data-i18n="tab-uxtests">UX tests</a></li>
      </ul>
    </div>

           <?php
require 'vendor/autoload.php';
use TANIOS\Airtable\Airtable;

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
        $urls = "";
        $url = "";
        if (substr($url, 0, 8) == "https://")
        {
            $urls = substr($url, 8, strlen($url));
        }
        else
        {
            $urls = $url;
        }

        $r = new ApiClient($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token']);

        $temp = ['aa-ovrvw-smmry-metrics', 'aa-ovrvw-smmry-fwylf', 'aa-ovrvw-smmry-trnd', 'aa-ovrvw-smmry-tsks']; //, 'fwylf' ];
        $result = array();
        $j = array();

        foreach ($temp as $t)
        {

            $json = $data[$t];
            $json = sprintf($json, $urls);

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
            $result[] = $r->requestEntity($json);
            $j[] = $json;

        }

        //echo var_dump($result[0]);
        foreach ($result as $r)
        {

        }

        $res = json_decode($result[0], true);
        $metrics = $res["summaryData"]["filteredTotals"];

        $res2 = json_decode($result[1], true);
        $metrics2 = $res2["summaryData"]["filteredTotals"];

        $aaResultTrend = json_decode($result[2], true);
        $aaMetricsTrend = $aaResultTrend["rows"];

        $weeks_index = date("M j, Y", strtotime($previousWeekStart));
        //echo $weeks_index;
        $value_date = array_column($aaMetricsTrend, 'value');
        $index_key = array_search($weeks_index, $value_date);

        //$aaTrendWeeks = array_slice($aaMetricsTrend, -14);
        $aaTrendWeeks = array_slice($aaMetricsTrend, $index_key, 14);
        $aaTrendLastWeek = array_slice($aaTrendWeeks, 0, 7);
        $aaTrendWeek = array_slice($aaTrendWeeks, -7);

        $aaTasks = json_decode($result[3], true);
        $aaTasksStats = $aaTasks["rows"];

        $taskArray = array();
        foreach ($aaTasksStats as $task)
        {
            $taskArray[] = $task['value'];
        }

        $fwylfYes = 0;
        $fwylfNo = 4;
        $pv = 8;
        $visitors = 12;
        $visits = 16;

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

        $diff = differ($metrics[$visitors + 2], $metrics[$visitors + 3]);
        $pos = posOrNeg($diff);
        $pieces = explode(":", $pos);

        $diff = abs($diff);

        $fwylfICantFindTheInfo = 0;
        $fwylfOtherReason = 4;
        $fwylfInfoHardToUnderstand = 8;
        $fwylfError = 12;
        ?>

        <div class="row mb-3 gx-3">
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card">
              <div class="card-body card-pad pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Visitors who visited the website at least once and is counted only once in the reporting time period" data-i18n="unique-visitors">Unique visitors</span></h3>
                  <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=number_format($metrics[$visitors + 3]) ?></span><span class="small"><?//=number_format($metrics[$visitors + 2]) ?></span></div>
                    <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?=percent($diff) ?></span></div>
                </div>
              </div>
            </div>
          </div>
          <?php
$diff = differ($metrics[$visits + 2], $metrics[$visits + 3]);
$pos = posOrNeg($diff);
$pieces = explode(":", $pos);

$diff = abs($diff);
?>
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card">
              <div class="card-body card-pad pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="The total number of times all CRA pages are visited in the reporting time period" data-i18n="visits-all">Visits to all CRA pages</span></h3>
                  <div class="row">
                    <div class="col-md-8 col-sm-6"><span class="h3 text-nowrap"><?=number_format($metrics[$visits + 3]) ?></span><span class="small"><?//=number_format($metrics[$visits + 2]) ?></span></div>
                    <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?=percent($diff) ?></span></div>
                </div>
              </div>
            </div>
          </div>
            <?php
$diff = differ($metrics[$pv + 2], $metrics[$pv + 3]);
$pos = posOrNeg($diff);
$pieces = explode(":", $pos);

$diff = abs($diff);
?>
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card">
              <div class="card-body card-pad pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="The total number of times CRA pages are viewed in the reporting time period" data-i18n="page-views">Page views</span></h3>
                  <div class="row">
                    <div class="col-sm-8"><span class="h3 text-nowrap"><?=number_format($metrics[$pv + 3]) ?></span><span class="small"><?//=number_format($metrics[$pv + 2]) ?></span></div>
                    <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?=percent($diff) ?></span></div>
                </div>
              </div>
            </div>
          </div>
        </div>
          <?php
                // ***************************************************************************
                // AIRTABLE API calls for Call drivers data for TOTAL CALLS BY INQURY LINE
                // ***************************************************************************


                $lastMonthAndYear = date('F Y', strtotime($monthStartHeader));
                $twoMonthsAgoAndYear = date('F Y', strtotime($previousMonthStartHeader));

                $s = $startLastGSC;
                $e = $endLastGSC;
                $s1 = $startGSC;
                $e1 = $endGSC;

                $weeklyMonthRanges = getMonthRanges($s, $e1);


                /* **************************************************************************************************** */
                // Set up config file and credentials for the Airtable API
                /* **************************************************************************************************** */
                //$config = include ('./php/config-at_cd.php');
                $config = include ('./php/config-at.php');


                //time the page load
                //$start2 = microtime(true);

                if (count($weeklyMonthRanges) > 1) {
                    //echo "data ranges spread in two months";
                    $curMonth1 = date('F Y', strtotime($weeklyMonthRanges[0]['start']));
                    $curMonth2 = date('F Y', strtotime($weeklyMonthRanges[1]['start']));

                    $curQuarter1 = ceil(date("n", strtotime($weeklyMonthRanges[0]['start'])) / 3);
                    $curQuarter2 = ceil(date("n", strtotime($weeklyMonthRanges[1]['start'])) / 3);

                    $curYear1 = date('Y', strtotime($weeklyMonthRanges[0]['start']));
                    $curYear2 = date('Y', strtotime($weeklyMonthRanges[1]['start']));

                    $base1 = "DCD-".$curYear1."-Q".$curQuarter1;
                    $base2 = "DCD-".$curYear2."-Q".$curQuarter2;

                    //$airtable = new Airtable($config);
                    $airtable1 = new Airtable(array(
                         'api_key'   => $config['call_data']['api_key'],
                         'base'      => $config['call_data']['base'][$base1],
                    ));

                    $airtable2 = new Airtable(array(
                         'api_key'   => $config['call_data']['api_key'],
                         'base'      => $config['call_data']['base'][$base2],
                    ));

                    $wMrS1 = $weeklyMonthRanges[0]['start'];
                    $wMrE1 = $weeklyMonthRanges[0]['end'];
                    $wMrS2 = $weeklyMonthRanges[1]['start'];
                    $wMrE2 = $weeklyMonthRanges[1]['end'];

                    // **************************************************************************************************************************
                    // I M P O R T A N T
                    // IS_AFTER() and IS_BEFORE() functions don't count the border dates when filtering.
                    // that's why we do DATEADD (one day) for is_BEFORE() ...
                    // for IS_AFTER() we don't substract a day for $params1, cause that start day is always Saturday and on Sundays there's no call centre data.
                    // As a correct coding practice, we need to subtact a day from $params1 (TO BE DONE)
                    // for $params2, instead of subtract a day, we use the $params1 end date (that is a one day less the the $params2 start date).
                    // **************************************************************************************************************************

                    $params1 = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$wMrS1'), IS_BEFORE({CALL_DATE}, DATEADD('$wMrE1',1,'days')))"); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
                    $params2 = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$wMrE1'), IS_BEFORE({CALL_DATE}, DATEADD('$wMrE2',1,'days')))"); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');

                    $table1 = $curMonth1;
                    $table2 = $curMonth2;


                    //API REQUST 1
                    $fullArray1 = [];

                    $request1 = $airtable1->getContent($table1, $params1);
                    do
                    {
                        $response1 = $request1->getResponse();
                        $fullArray1 = array_merge($fullArray1, ($response1->records));
                    }
                    while ($request1 = $response1->next());

                    $weeklyRe1 = ( json_decode(json_encode($fullArray1), true));//['records'];

                    // if there's data (record exist)
                    if ( count( $weeklyRe1 ) > 0 ) {
                      // do things here
                    }

                    // ***************** //

                      //API REQUST 2
                    $fullArray2 = [];

                    $request2 = $airtable2->getContent($table2, $params2);
                    do
                    {
                        $response2 = $request2->getResponse();
                        $fullArray2 = array_merge($fullArray2, ($response2->records));
                    }
                    while ($request2 = $response2->next());

                    $weeklyRe2 = ( json_decode(json_encode($fullArray2), true));//['records'];

                    // if there's data (record exist)
                    if ( count( $weeklyRe2 ) > 0 ) {
                      // do things here
                    }

                    // WE NEED TO MERGE $weeklyRe1 and $weeklyRe2
                    $fullArray = array_merge($fullArray1, $fullArray2);

                }

                if (count($weeklyMonthRanges) == 1) {

                    $curMonth = date('F Y', strtotime($e1));
                    $curQuarter = ceil(date("n", strtotime($e1)) / 3);
                    $curYear = date('Y', strtotime($e1));

                    $base = "DCD-".$curYear."-Q".$curQuarter;
                    //echo "base: ". $base;

                    $airtable = new Airtable(array(
                         'api_key'   => $config['call_data']['api_key'],
                         'base'      => $config['call_data']['base'][$base],
                    ));

                    $s = date("Y-m-d", strtotime($s));
                    $e1 = date("Y-m-d", strtotime($e1));

                    $params = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$s'), IS_BEFORE({CALL_DATE}, DATEADD('$e1',1,'days')))");

                    $table = $curMonth;

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

                }


                $re = ( json_decode(json_encode($fullArray), true));//['records'];

                //weekly data
                $rangeStartW = strtotime($s1);
                $rangeEndW = strtotime($e1);
                //previous week
                $rangeStartPW = strtotime($s);
                $rangeEndPW = strtotime($e);
                //filter array by date ranges

                $WeeklyData = array_filter( $re, function($var) use ($rangeStartW, $rangeEndW) {
                   $utime = strtotime($var['fields']['CALL_DATE']);
                   return $utime <= $rangeEndW && $utime >= $rangeStartW;
                });


                $PWeeklyData = array_filter( $re, function($var) use ($rangeStartPW, $rangeEndPW) {
                   $utime = strtotime($var['fields']['CALL_DATE']);
                   return $utime <= $rangeEndPW && $utime >= $rangeStartPW;
                });


                if (( count( $WeeklyData ) > 0 ) && ( count( $PWeeklyData ) > 0 )) {

                      // Get just the ['fields'] array of each record -  as a separate array - $all_fields
                      $all_fields = array_column_recursive($WeeklyData, 'fields');
                      $all_fieldsPW = array_column_recursive($PWeeklyData, 'fields');

                      //Sort all_fields array by Call_Date key in descending order
                      // if we need an ascernding order, swap the $a and $b variable as function arguments
                      usort($all_fields, function($a, $b) {
                         return new DateTime($a['CALL_DATE']) <=> new DateTime($b['CALL_DATE']);
                       });

                       usort($all_fieldsPW, function($a, $b) {
                          return new DateTime($a['CALL_DATE']) <=> new DateTime($b['CALL_DATE']);
                        });

                      //----------------------------------------------------------------------------------------
                      //GROUP elements by Topic and calculate total call per topic
                      //----------------------------------------------------------------------------------------
                      $fieldsByGroup = group_by('Topic', $all_fields);
                      $fieldsByGroupPW = group_by('Topic', $all_fieldsPW);


                      foreach ( $fieldsByGroupPW as &$item ) {
                        $item["Total topic calls"] = array_sum(array_column_recursive($item, "Calls"));
                      }
                      //

                      $i=1;
                      foreach ( $fieldsByGroup as &$item1 ) {
                        // Add Total Topic Calls and Change (in teh number of calls from previous week) keys
                        $item1["Total topic calls"] = array_sum(array_column_recursive($item1, "Calls"));

                        if (array_key_exists("Topic", $item1[0])) {
                          if (array_key_exists($item1[0]['Topic'], $fieldsByGroupPW)){
                                $item1["Change"] = differ( $fieldsByGroupPW[$item1[0]['Topic']]['Total topic calls'], $item1['Total topic calls'] );
                          }
                          else {
                              $item1["Change"] = 0;
                          }
                        }
                        else {
                          $item1["Change"] = 0;
                        }
                      }

                      //uasort -keeps the key associations
                      uasort($fieldsByGroup, function($b, $a) {
                           if ($a["Total topic calls"] == $b["Total topic calls"]) {
                               return 0;
                           }
                           return ($a["Total topic calls"] < $b["Total topic calls"]) ? -1 : 1;
                       });

                       $top5Topics = array_slice($fieldsByGroup, 0, 5);

                       //----------------------------------------------------------------------------------------
                       //GROUP elements by DATE and calculate total call per DAy
                       //----------------------------------------------------------------------------------------
                       // echo "<pre>";
                       // print_r($all_fields);
                       // echo "</pre>";

                       $fieldsByGroupDate = group_by('CALL_DATE', $all_fields);
                       $fieldsByGroupDatePW = group_by('CALL_DATE', $all_fieldsPW);

                       foreach ( $fieldsByGroupDatePW as &$item ) {
                         $item["Total calls per day"] = array_sum(array_column_recursive($item, "Calls"));
                       }

                       foreach ( $fieldsByGroupDate as &$item ) {
                         $item["Total calls per day"] = array_sum(array_column_recursive($item, "Calls"));
                       }

                       // echo count($fieldsByGroupDate);
                       // echo "<pre>";
                       // print_r($fieldsByGroupDate);
                       // echo "</pre>";

                } //if ( count( $re ) > 0 )

                // END AIRTALBE API CALLS
                // ****************************************************************************

              ?>


    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Number of page visits breakdown (bar chart) relation over selected Date ranges, compared to the Call volume (line chart) - the total number of calls in the Calls centre for the same date ranges." data-bs-original-title="" title="" data-i18n="d3-visits-compared-to-calls">Visits compared to call volume</span></h3>
            <div class="card-body pt-2" id="d3_visits"></div>
            <div id="d3_www_legend"></div>
              <!-- Total calls by Enquiry_line D3 bar chart -->
              <?php

                $s = $startLastGSC;
                $e = $endLastGSC;
                $s1 = $startGSC;
                $e1 = $endGSC;

                //echo $s;
                //echo gettype($e1);

                $s = date("M d", strtotime($s));
                $e = date("M d", strtotime($e));
                $s1 = date("M d", strtotime($s1));
                $e1 = date("M d", strtotime($e1));




                $days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday','Thursday','Friday', 'Saturday');
                $d3DateRanges = array($s.'-'.$e,$s1.'-'.$e1); // previous $a1
                //$dates = [[$startLastGSC, $endLastGSC], [$startGSC, $endGSC]];
                //$dataPW = $aaTrendLastWeek;
                //$dataW = $aaTrendWeek;

                for ($i = 0; $i < 7; ++$i) {
                  $final_array1["day"] = $days[$i];
                  $final_array1[$d3DateRanges[0]] = $aaTrendLastWeek[$i]['data'][1];
                  $final_array1[$d3DateRanges[1]] = $aaTrendWeek[$i]['data'][1];
                  $data_array[]=$final_array1;
                }

                //$s = $startLastGSC;
                //$e = $endLastGSC;
                //$s1 = $startGSC;
                //$e1 = $endGSC;
                //For loop for the call volume data

                $pwDays = [];
                $wDays = [];

                for ($i = 0; $i < 7; ++$i) {
                  $pwDays[] = date("Y-m-d", strtotime($startLastGSC. " +".$i."days"));
                  $wDays[] = date("Y-m-d", strtotime($startGSC. " +".$i."days"));
                }

                // echo count($pwDays);
                // echo "<pre>";
                // print_r($pwDays);
                // echo "</pre>";
                // echo "<br>";
                // echo count($wDays);
                // echo "<pre>";
                // print_r($wDays);
                // echo "</pre>";


                for ($i = 0; $i < 7; ++$i) {
                  $final_array2["day"] = $days[$i];
                  // get the key from the fieldsByGroupDate and fieldsByGroupDatePW arrays
                  // convert the value to
                  // $fieldsByGroupDatePW
                  // $fieldsByGroupDate

                  // foreach ($fieldsByGroupDatePW as $trend)
                  // {
                  if (array_key_exists($pwDays[$i], $fieldsByGroupDatePW)){
                        $final_array2[$d3DateRanges[0]] = $fieldsByGroupDatePW[$pwDays[$i]]['Total calls per day'];
                  }
                  else {
                        $final_array2[$d3DateRanges[0]] = 0;
                  }
                  if (array_key_exists($wDays[$i], $fieldsByGroupDate)){
                        $final_array2[$d3DateRanges[1]] = $fieldsByGroupDate[$wDays[$i]]['Total calls per day'];
                  }
                  else {
                        $final_array2[$d3DateRanges[1]] = 0;
                  }


                  //$final_array2[$d3DateRanges[1]] = $fieldsByGroupDate['Total calls per day'];

                  $data_array2[]=$final_array2;
                }

                // echo count($data_array2);
                // echo "<pre>";
                // print_r($data_array2);
                // echo "</pre>";



                $subgroups = json_encode($d3DateRanges);

                $groups = json_encode($days);

                $mydata = json_encode($data_array);

                $mydataCalls = json_encode($data_array2);

                ?>
                <script>
                //
                // set the dimensions and margins of the graph
                width = parseInt(d3.select('#d3_visits').style('width'), 10)
                height = width / 3;
                //alert("hellp");
                var margin = {top: 10, right: 30, bottom: 30, left: 100},
                    width = width - margin.left - margin.right,
                    height = height - margin.top - margin.bottom,
                    legendHeight = 0,
                    dualaxisWidth = 120;


                // append the svg object to the body of the page
                var svg1 = d3.select("#d3_visits")
                  .append("svg")
                    .attr("width", width + margin.left + margin.right)
                    .attr("height", height + margin.top + margin.bottom + legendHeight)
                  .append("g")
                    .attr("transform",
                          "translate(" + margin.left + "," + margin.top + ")");

                // Parse the Data
                  var data = <?=$mydata?>;

                  var dataCalls = <?=$mydataCalls?>;
                  console.log("line graph data:")
                  console.log(dataCalls);

                  //console.log(data)
                  //console.log(typeof data)
                  // List of subgroups = header of the csv files = soil condition here
                  //var subgroups = data.columns.slice(1)
                  var subgroups = <?=$subgroups?>;
                  console.log(subgroups);
                  console.log(subgroups[0]);
                  //console.log(typeof subgroups)

                  // List of groups = species here = value of the first column called group -> I show them on the X axis
                  //var groups = d3.map(data, function(d){return(d.group)}).keys()
                  var groups = <?=$groups?>;
                  //console.log(groups)
                  //console.log(typeof groups)

                  // Add X axis
                  var x = d3.scaleBand()
                      .domain(groups)
                      .range([0, width-dualaxisWidth])
                      .padding([0.4]);
                  svg1.append("g")
                    .attr("transform", "translate(0," + height + ")")
                    .call(d3.axisBottom(x).tickSizeOuter(0));

                  // get the max value from the data json object for the y axis domain (Number of visits)
                  var max1 = d3.max(data, function(d){ return d3.max(d3.values(d).filter(function(d1){ return !isNaN(d1)}))});
                  console.log(max1);
                  var num_digits1 = Math.floor(Math.log10(max1)) + 1;
                  console.log(num_digits1);
                  console.log(Math.ceil(max1/Math.pow(10,num_digits1-1))*Math.pow(10,num_digits1-1));

                  //get MAX for dual Y axis (Call volume)
                  var maxCalls = d3.max(dataCalls, function(d){ return d3.max(d3.values(d).filter(function(d1){ return !isNaN(d1)}))});
                  console.log(maxCalls);
                  var num_digitsCalls = Math.floor(Math.log10(maxCalls)) + 1;
                  console.log(num_digitsCalls);
                  console.log(Math.ceil(maxCalls/Math.pow(10,num_digitsCalls-1))*Math.pow(10,num_digitsCalls-1));

                  // Add Y axis
                  var y = d3.scaleLinear()
                    .domain([0, Math.ceil(max1/Math.pow(10,num_digits1-1))*Math.pow(10,num_digits1-1)])
                    .range([ height, 0 ]);

                  // Add dual Y axix (Call volume)
                  var y1 = d3.scaleLinear()
                    .domain([0, Math.ceil(maxCalls/Math.pow(10,num_digitsCalls-1))*Math.pow(10,num_digitsCalls-1)])
                    .range([ height, 0 ]);

                  // grid lines on Y axis
                  var yGrid = d3.axisLeft(y).tickSize(-(width-dualaxisWidth)).tickFormat('').ticks(5);

                  // Another scale for subgroup position?
                  var xSubgroup = d3.scaleBand()
                    .domain(subgroups)
                    .range([0, x.bandwidth()])
                    .padding([0.1]);

                  //create  yGrid
                  svg1.append('g')
                    .attr('class', 'axis-grid')
                    .call(yGrid);

                  //create Y axis
                  svg1.append("g")
                    .call(d3.axisLeft(y).ticks(5));

                  //create dual Y axis (Call volume)
                  svg1.append("g")
                    .attr("transform", "translate(" + (width - dualaxisWidth) + ",0)")
                    //.style("stroke", "red")
                    .attr("class", "axisRed")
                    .call(d3.axisRight(y1).ticks(5));

                  // color palette = one color per subgroup
                  var color = d3.scaleOrdinal()
                    .domain(subgroups)
                    .range(['#B6C2CB','#345EA5']);

                    // Show the bars
                  svg1.append("g")
                      .selectAll("g")
                      // Enter in data = loop group per group
                      .data(data)
                      .enter()
                      .append("g")
                        .attr("transform", function(d) { return "translate(" + x(d.day) + ",0)"; })
                      .selectAll("rect")
                      .data(function(d) { return subgroups.map(function(key) { return {key: key, value: d[key]}; }); })
                      .enter().append("rect")
                        .attr("x", function(d) { return xSubgroup(d.key); })
                        .attr("y", function(d) { return y(d.value); })
                        .attr("width", xSubgroup.bandwidth())
                        .attr("height", function(d) { return height - y(d.value); })
                        .attr("fill", function(d) { return color(d.key); });

                  svg1.selectAll(".tick text")
                       .style("font-size","14px")
                       .style("fill","#666");


                  // Add the line
                 svg1.append("path")
                   .datum(dataCalls)
                   .attr("fill", "none")
                   .attr("stroke", "orange")
                   .attr("stroke-width", 1.5)
                   .attr("d", d3.line()
                     .x(function(d) { return x(d.day) })
                     //.y(function(d) { return y1(d['Sep 26-Oct 02']) })
                     .y(function(d) { return y1(d[subgroups[0]]) })
                   );

                 // Add the line
                svg1.append("path")
                  .datum(dataCalls)
                  .attr("fill", "none")
                  .attr("stroke", "red")
                  .attr("stroke-width", 1.5)
                  .attr("d", d3.line()
                    .x(function(d) { return x(d.day) })
                    //.y(function(d) { return y1(d['Sep 26-Oct 02']) })
                    .y(function(d) { return y1(d[subgroups[1]]) })
                  );



                       // Add the line
                   // svg1.append("path")
                   //   .datum(dataCalls)
                   //   .attr("fill", "none")
                   //   .attr("stroke", "steelblue")
                   //   .attr("stroke-width", 1.5)
                   //   .attr("d", d3.line()
                   //     .x(function(d) { return x(d.day) })
                   //     .y(function(d) { return y(d.value) })
                   //   );


                 // var valueline = d3.svg1.line()
                 //     .x(function(d) { return x(d.day); })
                 //     .y(function(d) { return y0(d.close); });
                 //
                 // var valueline2 = d3.svg1.line()
                 //     .x(function(d) { return x(d.date); })
                 //     .y(function(d) { return y1(d.open); });

                  // D3 legend
                  //color.domain(d3.keys(data[0]).filter(function(key) { return key !== "dateRange"; }));

                  // svg_new.append("g")
                  //    .attr("class", "legendOrdinal")
                  //    .attr("transform", "translate(0,"+(height+45)+")");
                  //
                  // var legendOrdinal = d3.legendColor()
                  //  .shape("rect")
                  //  .shapePadding(150)
                  //  .orient('horizontal')
                  //  .labelAlign("start")
                  //  .scale(color);
                  //
                  // svg_new.select(".legendOrdinal")
                  //    .call(legendOrdinal);

                  var legend = d3.select('#d3_www_legend').selectAll("legend")
                      .data(subgroups);

                  var legend_cells = legend.enter().append("div")
                    .attr("class","legend");

                  var p1 = legend_cells.append("p").attr("class","legend_field");
                  p1.append("span").attr("class","legend_color").style("background",function(d,i) { return color(i) } );
                  p1.insert("text").text(function(d,i) { return d } );

                  // text label for the y axis
                  svg1.append("text")
                      .attr("transform", "rotate(-90)")
                      .attr("y",0 - margin.left)
                      .attr("x",0 - (height / 2))
                      .attr("dy", "1em")
                      .style("text-anchor", "middle")
                      .text("Visits");

                  // text label for the second (dual) y axis
                  svg1.append("text")
                        .attr("transform", "rotate(-90)")
                        .attr("y",width - 40)
                        .attr("x",0 - (height / 2))
                        .attr("dy", "1em")
                        .style("fill", "red")
                        .style("text-anchor", "middle")
                        .text("Call volume");

                </script>
                 <details class="details-chart">
                      <summary data-i18n="view-data-table">View table data</summary>
                      <div class="table-responsive">
                          <table class="table">
                            <caption><!--Last Week--></caption>
                            <thead>
                              <th data-i18n="date">Date (<?=$d3DateRanges[0]?>)</th>
                              <th data-i18n="visits">Visits</th>
                            </thead>
                            <tbody>

                              <?php
                                  foreach ($aaTrendLastWeek as $trend)
                                  {

                                  ?>

                                          <tr>
                                            <td><?=$trend['value'] ?></td>
                                            <td><?=number_format($trend['data'][1]) ?></td>
                                          </tr>

                                          <?php
                                  }

                                  ?>


                            </tbody>
                          </table>

                          <table class="table">
                            <caption><!--Week--></caption>
                            <thead>
                              <th data-i18n="date">Date (<?=$d3DateRanges[1]?>)</th>
                              <th data-i18n="visits">Visits</th>
                            </thead>
                            <tbody>

                              <?php
                                  foreach ($aaTrendWeek as $trend)
                                  {

                                  ?>

                                          <tr>
                                            <td><?=$trend['value'] ?></td>
                                            <td><?=number_format($trend['data'][1]) ?></td>
                                          </tr>

                                          <?php
                                  }

                                  ?>

                            </tbody>
                          </table>
                    </details>
          </div>
        </div>
      </div>
    </div>
      <?php
// GSC
$data = include ('./php/data-gsc.php');

$type = ['ovrvw-smmry-totals', 'ovrvw-smmry-qryAll'];

$results = 5;

$gscArr = array();
$gscResp = array();

$start2 = microtime(true);

foreach ($type as $t)
{

    foreach ($dates as $d)
    {

        $analytics = initializeAnalytics();
        $response = getReport($d[0], $d[1], $results, $url, $t);
        $u = printResults($analytics, $response, $t);
        $u = json_decode($u, true);

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

$diff = differ($lastImp, $imp);
$posi = posOrNeg($diff);
$pieces = explode(":", $posi);

$diff = abs($diff);

?>

        <div class="row mb-3 gx-3">
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card">
              <div class="card-body card-pad pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="The number of times CRA pages appeared in Google search results" data-i18n="total-impressions-google">Total impressions from Google</span></h3>
                  <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=number_format($imp) ?></span><span class="small"><?//=number_format($lastImp) ?></span></div>
                    <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?=percent($diff) ?></span></div>
                </div>
              </div>
            </div>
          </div>

          <?php
$diff = numDiffer($lastCtr, $ctr);
$posi = posOrNeg($diff);
$pieces = explode(":", $posi);

$diff = abs($diff);
?>

          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card">
              <div class="card-body card-pad pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Percentage of impressions in Google search results that resulted in a click" data-i18n="ctr-google">Click through rate from Google</span></h3>
                  <div class="row">
                    <div class="col-md-8 col-sm-6"><span class="h3 text-nowrap"><?=percent($ctr) ?></span><span class="small"><?//=percent($lastCtr) ?></span></div>
                    <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?=percent($diff) ?></span></div>
                </div>
              </div>
            </div>
          </div>

          <?php
$diff = round(numDiffer($lastPos, $pos));
$posi = posOrNeg($diff);
$pieces = explode(":", $posi);

$diff = abs($diff);
?>
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card">
              <div class="card-body card-pad pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Average rank in Google search results for all CRA pages" data-i18n="avg-rank-google">Average rank on Google</span></h3>
                  <div class="row">
                    <div class="col-sm-8"><span class="h3 text-nowrap"><?=number_format($pos) ?></span><span class="small"><?//=number_format($lastPos) ?></span></div>
                    <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?=$diff ?></span></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <?php
// function implode_recursive($g, $p)
// {
//     return is_array($p) ? implode($g, array_map(__FUNCTION__, array_fill(0, count($p) , $g) , $p)) : $p;
// }

$config = include ('./php/config-at.php');

$start2 = microtime(true);

$airtable = new Airtable($config['tasks']);

//var_dump($taskArray);
// Tasks in AirTable
$params = array(
    "filterByFormula" => 'SEARCH(Task, "' . implode($taskArray, ',') . '") != ""'
);
//print_r($params);
$table = 'Top Task Survey (PP)';

$request = getContentRecursive($airtable, $table, $params);
$lo = ['fields', ['Task', 'Tasks']];

$con = parseJSON2($request, $lo);

//echo "<br /><br /> Connection Main: ";
//var_dump ( $con );
// Enquiry Lines in AirTable
$params = array(); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
//print_r($params);
$table = 'Weekly Calls (2021)';

$fullArray = [];
$request = $airtable->getContent($table, $params);
do
{
    $response = $request->getResponse();
    $fullArray = array_merge($fullArray, ($response->records));
}
while ($request = $response->next());

//var_dump($fullArray);
$m = ['fields', 'Equiry Line'];
$l = ['fields', 'Total Calls'];

$con1 = parseJSON($fullArray, $l);
//var_dump($con1);
$con2 = parseJSON($fullArray, $m);
//var_dump($con2);
$arrFinal = array();
for ($i = 0;$i < count($con1) - 1;$i++)
{
    if (isset($arrFinal[($con2[$i]) ])) $arrFinal[($con2[$i]) ] += $con1[$i];
    else $arrFinal += array(
        $con2[$i] => $con1[$i]
    );
}
//var_dump($arrFinal);
$params = array(); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
//print_r($params);
$table = 'User Testing';

$fullArray = [];
$request = $airtable->getContent($table, $params);
do
{
    $response = $request->getResponse();
    $fullArray = array_merge($fullArray, ($response->records));
}
while ($request = $response->next());

//var_dump($fullArray);
$m = ['fields', '# of Users'];
$l = ['fields', 'Success Rate'];

$con1 = parseJSON($fullArray, $m);
$con2 = parseJSON($fullArray, $l);

$totalTasks = number_format(count($fullArray));
$avgSuccessRate = percent(array_sum($con2) / $totalTasks);
$sumNumUsers = number_format(array_sum($con1));

//echo 'total tasks: ' . $totalTasks . "<br /><br />avg success rate: " . $avgSuccessUsers . '<br />br />sum of users: ' . $sumNumUsers;



?>

    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Top Tasks from the CRA Quarterly Top Task Survey" data-bs-original-title="" title="" data-i18n="top10-tasks">Top 10 tasks</span></h3>
            <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

               <?php

              $qry = $aaTasksStats;
              //var_dump($qry);

                if (count($qry) > 0) { ?>
                  <div class="table-responsive">
              <table class="table table-striped dataTable no-footer">
                <thead>
                  <tr>
                    <th data-i18n="task">Task</th>
                    <th data-i18n="change">>Change</th>
                    <th data-i18n="">Task Success Survey Completed</th>
                  </tr>
                </thead>
                <tbody>
              <?php foreach ($qry as $row) { ?>
                  <tr>
                    <td><?=$row['value'];?></td>
                    <?php
                    $diff = differ( $row['data'][2], $row['data'][3] );
                    $posi = posOrNeg2($diff);
                    $pieces = explode(":", $posi);
                    $diff = abs($diff);
                    ?>
                    <td><span class="<?=$pieces[0]?>"><?=$pieces[1]?> <?=percent($diff)?></span></td>
                    <td><span><strong><?=$row['data'][3]?></strong></span> <span class="small"><?//=$row['data'][2]?></span></td>
                  </tr>
              <?php } ?>
                </tbody>
              </table>
            </div>
              <?php } ?>

            </div></div><div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Total number of calls per enquiry line: ITE (Individual Tax Enquiries), e-services, Child and Family Benefits, Buiness Enquiries, etc." data-bs-original-title="" title="" data-i18n="d3-tcbil">Total calls by inquiry line</span></h3>
            <div class="card-body pt-2" id="d3_tcbil"></div>
              <div id="d3_www_legend3"></div>
                <!-- Total calls by Enquiry_line D3 bar chart -->
                <?php

                  $fieldsByGroupEL = group_by('Enquiry_line', $all_fields);
                  $fieldsByGroupELPW = group_by('Enquiry_line', $all_fieldsPW);


                  foreach ( $fieldsByGroupEL as &$item ) {
                    $item["Total EL calls"] = array_sum(array_column_recursive($item, "Calls"));
                  }

                  foreach ( $fieldsByGroupELPW as &$item ) {
                    $item["Total EL calls"] = array_sum(array_column_recursive($item, "Calls"));
                  }

                  $s = date("M d", strtotime($s));
                  $e = date("M d", strtotime($e));
                  $s1 = date("M d", strtotime($s1));
                  $e1 = date("M d", strtotime($e1));


                  // echo $s;
                  // echo $e;
                  // echo $s1;
                  // echo $e1;
                  //echo gettype($e1);


                  $d3DateRanges = array($s.'-'.$e,$s1.'-'.$e1); // previous $a1

                  // echo "<pre>";
                  // print_r($d3DateRanges);
                  // echo "</pre>";

                  $groups = json_encode($d3DateRanges);

                  $el = array_values(array_unique(array_column_recursive($fieldsByGroup, "Enquiry_line")));
                  $subgroups = json_encode($el);

                  /// ---------------------
                  /// MAKE SURE WE ADD THE DATA IN THE RIGHT DATE RANGE - AND TRIPLE CHECK THE RESULTS WITH THE ACTUAL DATA IN THE WEKLY AND PWEEKLY VARIABLES
                  /// -----------------------
                  for ($i = 0; $i < 2; ++$i) {
                    $final_array2["dateRange"] = $d3DateRanges[$i];
                          if ($i==0) {
                              for ($k = 0; $k < count($el); ++$k) {
                                $final_array2[$el[$k]] = $fieldsByGroupELPW[$el[$k]]["Total EL calls"];
                              }
                          }
                          else {
                            for ($k = 0; $k < count($el); ++$k) {
                              $final_array2[$el[$k]] = $fieldsByGroupEL[$el[$k]]["Total EL calls"];
                            }
                          }
                    // $final_array["No"] = $d3Data[$i+4];
                    $d3_data_w[]=$final_array2;
                  }

                  $mydata2 = json_encode($d3_data_w);

                  // echo "<pre>";
                  // print_r($d3_data_w);
                  // echo "</pre>";

                  ?>
                  <script>

                  // set the dimensions and margins of the graph
                  width = parseInt(d3.select('#d3_tcbil').style('width'), 10)
                  height = width / 3;
                  //alert("hellp");
                  var margin = {top: 10, right: 30, bottom: 30, left: 100},
                      width = width - margin.left - margin.right,
                      height = height - margin.top - margin.bottom,
                      legendHeight = 0;

                  // append the svg object to the body of the page
                  var svg2 = d3.select("#d3_tcbil")
                    .append("svg")
                      .attr("width", width + margin.left + margin.right)
                      .attr("height", height + margin.top + margin.bottom + legendHeight)
                    .append("g")
                      .attr("transform",
                            "translate(" + margin.left + "," + margin.top + ")");

                  // Parse the Data
                    var data2 = <?=$mydata2?>;

                    console.log("data for TCBIL");
                    console.log(data2);
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
                        .padding([0.3]);
                    svg2.append("g")
                      .attr("transform", "translate(0," + height + ")")
                      .call(d3.axisBottom(x).tickSizeOuter(0));

                    // get the max value from the data json object for the y axis domain
                    var max = d3.max(data2, function(d){ return d3.max(d3.values(d).filter(function(d1){ return !isNaN(d1)}))});
                    console.log(max);
                    var num_digits = Math.floor(Math.log10(max)) + 1;
                    console.log(num_digits);
                    console.log(Math.ceil(max/Math.pow(10,num_digits-1))*Math.pow(10,num_digits-1));

                    // Add Y axis
                    var y = d3.scaleLinear()
                      .domain([0, Math.ceil(max/Math.pow(10,num_digits-1))*Math.pow(10,num_digits-1)])
                      //.domain([0, 200000])
                      .range([ height, 0 ]);

                    // grid lines on Y axis
                    var yGrid = d3.axisLeft(y).tickSize(-width).tickFormat('').ticks(5);

                    // Another scale for subgroup position?
                    var xSubgroup = d3.scaleBand()
                      .domain(subgroups)
                      .range([0, x.bandwidth()])
                      .padding([0.1]);

                    //create  yGrid
                    svg2.append('g')
                      .attr('class', 'axis-grid')
                      .call(yGrid);

                    //create Y axis
                    svg2.append("g")
                      .call(d3.axisLeft(y).ticks(5));

                    // color palette = one color per subgroup
                    var color = d3.scaleOrdinal()
                      .domain(subgroups)
                      .range(['#345EA5','#6CB5F3','#36A69A','#F8C040','#3EE9B7','#F17F2B']);

                      // Show the bars
                    svg2.append("g")
                        .selectAll("g")
                        // Enter in data = loop group per group
                        .data(data2)
                        .enter()
                        .append("g")
                          .attr("transform", function(d) { return "translate(" + x(d.dateRange) + ",0)"; })
                        .selectAll("rect")
                        .data(function(d) { return subgroups.map(function(key) { return {key: key, value: d[key]}; }); })
                        .enter().append("rect")
                          .attr("x", function(d) { return xSubgroup(d.key); })
                          .attr("y", function(d) { return y(d.value); })
                          .attr("width", xSubgroup.bandwidth())
                          .attr("height", function(d) { return height - y(d.value); })
                          .attr("fill", function(d) { return color(d.key); });

                    svg2.selectAll(".tick text")
                         .style("font-size","14px")
                         .style("fill","#666");


                    // D3 legend
                    //color.domain(d3.keys(data[0]).filter(function(key) { return key !== "dateRange"; }));

                    // svg_new.append("g")
                    //    .attr("class", "legendOrdinal")
                    //    .attr("transform", "translate(0,"+(height+45)+")");
                    //
                    // var legendOrdinal = d3.legendColor()
                    //  .shape("rect")
                    //  .shapePadding(150)
                    //  .orient('horizontal')
                    //  .labelAlign("start")
                    //  .scale(color);
                    //
                    // svg_new.select(".legendOrdinal")
                    //    .call(legendOrdinal);

                    var legend = d3.select('#d3_www_legend3').selectAll("legend")
                        .data(subgroups);

                    var legend_cells = legend.enter().append("div")
                      .attr("class","legend");

                    var p1 = legend_cells.append("p").attr("class","legend_field");
                    p1.append("span").attr("class","legend_color").style("background",function(d,i) { return color(i) } );
                    p1.insert("text").text(function(d,i) { return d } );

                    // text label for the y axis
                    svg2.append("text")
                        .attr("transform", "rotate(-90)")
                        .attr("y", 0 - margin.left)
                        .attr("x",0 - (height / 2))
                        .attr("dy", "1em")
                        .style("text-anchor", "middle")
                        .text("Number of calls");

                  </script>
                  <details class="details-chart">
                    <summary data-i18n="view-data-table">View table data</summary>
                    <div class="table-responsive">
                      <table class="table">
                        <thead>
                          <th>Inquiry line</th>
                          <!-- <th>Previous Month</th>
                          <th>Month</th> -->
                          <th>Number of calls for <?=$d3DateRanges[0]?><!--two weeks ago--></th>
                          <th>Number of calls for <?=$d3DateRanges[1]?><!--last week--></th>
                        </thead>
                        <tbody>

                        <?php foreach ($el as $row) { ?>
                          <tr>
                            <td><?=$row?></td>
                            <td><?=number_format($fieldsByGroupELPW[$row]["Total EL calls"]) ?></td>
                            <td><?=number_format($fieldsByGroupEL[$row]["Total EL calls"]) ?></td>
                          </tr>
                        <?php } ?>

                        </tbody>
                      </table>
                    </div>
                  </details>



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
          </div>
        </div>
      </div>
      <div class="col-lg-6 col-md-6">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Reponses to  'What was wrong?' question after visitors clicked 'No' on the 'Did you find what you were looking for?' question." data-bs-original-title="" title="" data-i18n="d3-www">What was wrong?</span></h3>
              <div class="card-body pt-2" id="d3_www_barchart"></div>
                <div id="d3_www_legend2"></div>
                <!-- Did you find what you werel looking - WHAT WAS WRONG D3 100% Stacked Bar chart -->
                <?php

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

                // Parse the Data
                //d3.csv("https://raw.githubusercontent.com/holtzy/D3-graph-gallery/master/DATA/data_stacked.csv", function(data) {

                  var data = <?=$my_www_data?>;

                  console.log(data)
                  console.log(typeof data)
                  // List of subgroups = header of the csv files = soil condition here
                  //var subgroups = data.columns.slice(1)
                  //var subgroups = data.columns.slice(1)
                  var subgroups = <?=$subgroups_www?>;
                  console.log(subgroups)
                  console.log(typeof subgroups)

                  // List of groups = species here = value of the first column called group -> I show them on the X axis
                  //var groups = d3.map(data, function(d){return(d.group)}).keys()
                  var groups = <?=$groups_www?>;
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
                //})

                  svg.selectAll(".tick text")
                   //.attr("class","axis_labels")
                   .style("font-size","14px")
                   .style("fill","#666");

                  //D3 legend (if the legend labels are short and will fit in a single line)
                  // svg.append("g")
                  //     .attr("class", "legendOrdinal")
                  //     .attr("transform", "translate(0,"+(height+30)+")");
                  //
                  //  var legendOrdinal = d3.legendColor()
                  //   .shape("rect")
                  //   .shapePadding(120)
                  //   .orient('horizontal')
                  //   .labelAlign("start")
                  //   .scale(color);
                  //
                  //  svg.select(".legendOrdinal")
                  //     .call(legendOrdinal);

                    // New D3 legend (if the legend labels are long and wont fit in a single line)
                    var legend = d3.select('#d3_www_legend2').selectAll("legend")
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
                          <td data-i18n="d3-cant-find-info">I can't find the information</td>
                          <td><?=number_format($metrics2[$fwylfICantFindTheInfo + 0]) ?></td>
                          <td><?=number_format($metrics2[$fwylfICantFindTheInfo + 1]) ?></td>
                          <td><?=number_format($metrics2[$fwylfICantFindTheInfo + 2]) ?></td>
                          <td><?=number_format($metrics2[$fwylfICantFindTheInfo + 3]) ?></td>
                        </tr>

                        <tr>
                          <td data-i18n="d3-other">Other reason</td>
                          <td><?=number_format($metrics2[$fwylfOtherReason + 0]) ?></td>
                          <td><?=number_format($metrics2[$fwylfOtherReason + 1]) ?></td>
                          <td><?=number_format($metrics2[$fwylfOtherReason + 2]) ?></td>
                          <td><?=number_format($metrics2[$fwylfOtherReason + 3]) ?></td>
                        </tr>

                        <tr>
                          <td data-i18n="d3-hard-to-understand">Information hard to understand</td>
                          <td><?=number_format($metrics2[$fwylfInfoHardToUnderstand + 0]) ?></td>
                          <td><?=number_format($metrics2[$fwylfInfoHardToUnderstand + 1]) ?></td>
                          <td><?=number_format($metrics2[$fwylfInfoHardToUnderstand + 2]) ?></td>
                          <td><?=number_format($metrics2[$fwylfInfoHardToUnderstand + 3]) ?></td>
                        </tr>

                        <tr>
                          <td data-i18n="d3-error">Error/something didn't work</td>
                          <td><?=number_format($metrics2[$fwylfError + 0]) ?></td>
                          <td><?=number_format($metrics2[$fwylfError + 1]) ?></td>
                          <td><?=number_format($metrics2[$fwylfError + 2]) ?></td>
                          <td><?=number_format($metrics2[$fwylfError + 3]) ?></td>
                        </tr>

                      </tbody>
                    </table>

                </div>
            </details>
          </div>
        </div>
      </div>
    </div>
    <!-- <div class="row mb-3 gx-3">
      <h4>UX Tests</h4>
    </div> -->
    <div class="row mb-3 gx-3">
      <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="card">
          <div class="card-body card-pad pt-2">
            <h3 class="card-title"><span class="h6" title="">Tasks tested</span></h3>
              <div class="row">
                <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=$totalTasks; ?></span></div>
                <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-danger text-nowrap"></span></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="card">
          <div class="card-body card-pad pt-2">
            <h3 class="card-title"><span class="h6" title="">Average success rate</span></h3>
              <div class="row">
                <div class="col-md-8 col-sm-6"><span class="h3 text-nowrap"><?=$avgSuccessRate; ?></span></div>
                <div class="col-md-4 col-sd-6 text-end"><span class="h3 text-success text-nowrap"></span></div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="card">
          <div class="card-body card-pad pt-2">
            <h3 class="card-title"><span class="h6" title="">Participants</span></h3>
              <div class="row">
                <div class="col-sm-8"><span class="h3 text-nowrap"><?=$sumNumUsers; ?></span></div>
                <div class="col-sm-4 text-end"><span class="h3 text-danger text-nowrap"></span></div>
            </div>
          </div>
        </div>
      </div>
    </div>


<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
