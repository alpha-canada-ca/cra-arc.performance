
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
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="./overview_summary.php" data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-webtraffic">Web traffic</a></li>
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

    // TEST new datetime variales that will work on the last day of teh month
    // --------------------------------------------------------------------------------------
    // $weekStartNEW = (new DateTime("last sunday -2 week midnight"))->modify( 'next sunday' )->format($iso);
    // $weekEndNEW = (new DateTime("last sunday"))->format($iso);
    // $previousWeekStartNEW = (new DateTime("last sunday -2 week midnight"))->format($iso);
    // //$d->modify( 'last day of previous month' );
    // $previousWeekEndNEW = $weekStartNEW;

    //$previousWeekEndNEW = (new DateTime("next sunday"))->format($iso);
    // $monthStartNEW = (new DateTime("first day of last month midnight"))->format($iso);
    // $monthEndNEW = (new DateTime("first day of this month midnight"))->format($iso);
    // $previousMonthStartNEW = (new DateTime("first day of -2 month midnight"))->format($iso);
    // $previousMonthEndNEW = $monthStartNEW;


    // $monthStart2 = strtotime("first day of last month midnight");
    // $monthEnd2 = strtotime("first day of this month midnight");
    // $monthStart2 = date($iso, $monthStart2);
    // $monthEnd2 = date($iso, $monthEnd2);
    //
    // $previousMonthStart2 = strtotime("first day of -2 month midnight");
    // $previousMonthEnd2 = strtotime("first day of last month midnight");
    // $previousMonthStart2 = date($iso, $previousMonthStart2);
    // $previousMonthEnd2 = date($iso, $previousMonthEnd2);


    // $datetime1 = $monthEnd;
    // $datetime2 = new DateTime();
    // $difference = $datetime1->diff($datetime2);
    //
    // echo 'Difference: '.$difference->y.' years, '
    //                    .$difference->m.' months, '
    //                    .$difference->d.' days';
    //
    // print_r($difference);

    // $now = time();
    // $now = date($iso, $now);
    // $datediff = $monthEnd2 - $now;
    // echo "date difference:<br>";
    // echo $datediff;
    // //echo round($datediff / (60 * 60 * 24));
    // echo "<br>";

    // set default timezone

    // $dateTime = new \DateTime();
    // /**
    //  * You can get the string by using format
    //  */
    // echo $dateTime->format('Y-m-d H:i:s');
    //date_default_timezone_set('America/Toronto'); // CDT

    //$current_date = date('d/m/Y == H:i:s');

    //echo "current time on the server: ". $dateTime;
    // echo "<br>";
    // echo "prev month start: ". $previousMonthStart."<br>";
    // echo "prev month end: ". $previousMonthEnd."<br>";
    // echo "month start: ". $monthStart."<br>";
    // echo "month end: ". $monthEnd."<br>";
    // echo "prev week start: ". $previousWeekStart."<br>";
    // echo "prev week end: ". $previousWeekEnd."<br>";
    // echo "week start: ". $weekStart."<br>";
    // echo "week end: ". $weekEnd."<br>";
    //
    // echo "-----------------------------------------------<br>";
    // echo "NEW prev month start: ". $previousMonthStartNEW."<br>";
    // echo "NEW prev month end: ". $previousMonthEndNEW."<br>";
    // echo "NEW month start: ". $monthStartNEW."<br>";
    // echo "NEW month end: ". $monthEndNEW."<br>";
    // echo "NEW prev week start: ". $previousWeekStartNEW."<br>";
    // echo "NEW prev week end: ". $previousWeekEndNEW."<br>";
    // echo "NEW week start: ". $weekStartNEW."<br>";
    // echo "NEW week end: ". $weekEndNEW."<br>";
    // echo "-----------------------------------------------<br>";
    // echo "NEW NEW prev month start (no datetime object): ". $previousMonthStart2."<br>";
    // echo "NEW NEW prev month end (no datetime object): ". $previousMonthEnd2."<br>";
    // echo "NEW NEW month start (no datetime object): ". $monthStart2."<br>";
    // echo "NEW NEW month end (no datetime object): ". $monthEnd2."<br>";





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

        $temp = ['aa-ovrvw-smmry-metrics', 'aa-ovrvw-smmry-fwylf', 'aa-ovrvw-smmry-trnd', 'aa-ovrvw-smmry-tsks', 'aa-ovrvw-wbtrff-top10-monthly', 'aa-ovrvw-wbtrff-top10-weekly']; //, 'fwylf' ];
        //$temp = ['aa-ovrvw-smmry-metrics', 'aa-ovrvw-webtrafic-top10pages-weekly', 'aa-ovrvw-smmry-trnd', 'aa-ovrvw-webtrafic-top10pages-monthly'];
        $result = array();
        $j = array();

        foreach ($temp as $t)
        {

            $json = $data[$t];
            $json = sprintf($json, $urls);

            if ($t=='aa-ovrvw-wbtrff-top10-monthly') {
                    $json = str_replace(array(
                        "*previousMonthStart*",
                        "*previousMonthEnd*",
                        "*monthStart*",
                        "*monthEnd*"
                    ) , array(
                        $previousMonthStart,
                        $previousMonthEnd,
                        $monthStart,
                        $monthEnd
                    ) , $json);
            }
            elseif  ($t=='aa-ovrvw-wbtrff-top10-weekly') {
                    $json = str_replace(array(
                        "*previousWeekStart*",
                        "*previousWeekEnd*",
                        "*weekStart*",
                        "*weekEnd*"
                    ) , array(
                        $previousWeekStart,
                        $previousWeekEnd,
                        $weekStart,
                        $weekEnd
                    ) , $json);
            }
            else {
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
            }
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

        // echo count($aaMetricsTrend);
        // echo "<pre>";
        // print_r($aaMetricsTrend);
        // echo "</pre>";

        //get the index of the element that has the Previous week start date.
        $weeks_index = date("M j, Y", strtotime($previousWeekStart));
        //echo $weeks_index;
        $value_date = array_column($aaMetricsTrend, 'value');
        $index_key = array_search($weeks_index, $value_date);
        //echo $key;
        //echo "<br>". intval($key+13);
        //echo gettype($key);
        //echo gettype(intval($key+13));

        // WE CAN'T use this code anymore cause it's not working in all cases
        // in some cases, where the WeekEnd is earlier than the MonthEnd, we don't get the
        // correct elements for the weekly date ranges (its not the last 14 elements)
        //$aaTrendWeeks = array_slice($aaMetricsTrend, -14);

        // instead we find the index of the Previous Week start and count 14 elements after that
        $aaTrendWeeks = array_slice($aaMetricsTrend, $index_key, 14);
        $aaTrendLastWeek = array_slice($aaTrendWeeks, 0, 7);
        $aaTrendWeek = array_slice($aaTrendWeeks, -7);

        // echo count($aaTrendWeeks);
        // echo "<pre>";
        // print_r($aaTrendWeek);
        // echo "</pre>";
        // echo count($aaTrendWeek);
        // echo "----<br>";
        // echo "<pre>";
        // print_r($aaTrendWeek);
        // echo "</pre>";



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

        $aaTop10vPm = json_decode($result[4], true);
        $aaTop10VisitedPagesMonthly = $aaTop10vPm["rows"];

        // echo "<pre>";
        // print_r($aaTop10VisitedPagesMonthly);
        // echo "</pre>";

        $aaTop10vPw = json_decode($result[5], true);
        $aaTop10VisitedPagesWeekly = $aaTop10vPw["rows"];

        // echo "<pre>";
        // print_r($aaTop10VisitedPagesWeekly);
        // echo "</pre>";


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

    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="h6" data-i18n="visits">Visits</span></h3>

            <div class="card-body pt-2" id="d3_visits"></div>
            <div id="d3_www_legend"></div>
              <!-- Total calls by Enquiry_line D3 bar chart -->
              <?php

                $s = $startLastGSC;
                $e = $endLastGSC;
                $s1 = $startGSC;
                $e1 = $endGSC;

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
                  $final_array["day"] = $days[$i];
                  $final_array[$d3DateRanges[0]] = $aaTrendLastWeek[$i]['data'][1];
                  $final_array[$d3DateRanges[1]] = $aaTrendWeek[$i]['data'][1];
                  $data_array[]=$final_array;
                }
                //
                //
                // foreach ( $fieldsByGroupEL as &$item ) {
                //   $item["Total EL calls"] = array_sum(array_column_recursive($item, "Calls"));
                // }
                //
                // foreach ( $fieldsByGroupELPW as &$item ) {
                //   $item["Total EL calls"] = array_sum(array_column_recursive($item, "Calls"));
                // }
                //
                //
                // // $s = $startLastGSC;
                // // $e = $endLastGSC;
                // // $s1 = $startGSC;
                // // $e1 = $endGSC;
                //
                // $s = date("M d", strtotime($s));
                // $e = date("M d", strtotime($e));
                // $s1 = date("M d", strtotime($s1));
                // $e1 = date("M d", strtotime($e1));
                //
                // $d3DateRanges = array($s.'-'.$e,$s1.'-'.$e1); // previous $a1
                //
                $subgroups = json_encode($d3DateRanges);
                //
                // //echo count($d3DateRanges);
                // //echo count($groups[0]);
                //
                // //$d3Subgroups =  array("Yes","Yes","Yes","Yes","No","No","No","No"); // previous $b1
                // //$d3Data = array_slice($metrics, 0, 8); // previous $c1
                //
                // echo "--------------------------------------<br><br><pre>";
                // print_r($data_array); //Prev WEEK
                // echo "</pre><br></br>";
                //
                // $el = array_values(array_unique(array_column_recursive($fieldsByGroup, "Enquiry_line")));
                $groups = json_encode($days);
                //
                // // echo "--------------------------------------<br><br><pre>";
                // // print_r($el); //Prev WEEK
                // // echo count($el);
                // // echo "</pre><br></br>";
                //
                // /// ---------------------
                // /// MAKE SURE WE ADD THE DATA IN THE RIGHT DATE RANGE - AND TRIPLE CHECK THE RESULTS WITH THE ACTUAL DATA IN THE WEKLY AND PWEEKLY VARIABLES
                // /// -----------------------
                // for ($i = 0; $i < 2; ++$i) {
                //   $final_array["dateRange"] = $d3DateRanges[$i];
                //         if ($i==0) {
                //             for ($k = 0; $k < count($el); ++$k) {
                //               $final_array[$el[$k]] = $fieldsByGroupELPW[$el[$k]]["Total EL calls"];
                //             }
                //         }
                //         else {
                //           for ($k = 0; $k < count($el); ++$k) {
                //             $final_array[$el[$k]] = $fieldsByGroupEL[$el[$k]]["Total EL calls"];
                //           }
                //         }
                //   // $final_array["No"] = $d3Data[$i+4];
                //   $d3_data_w[]=$final_array;
                // }
                $mydata = json_encode($data_array);
                // //just present the Weekly date range data - index 2 and 3 from new_array
                // // echo "--------------------------------------<br><br><pre>";
                // // print_r($d3_data_w); //Prev WEEK
                // // echo "</pre><br></br>";
                // //$mydata = json_encode(array_slice($new_array, 2));
                // $mydata = json_encode($d3_data_w);
                //
                //
                //
                //
                //
                //
                // //$groups = json_encode(array_unique($d3Data_DYFWYWLF_DateRanges));
                // //just present the Weekly date ranges
                //
                //
                // ?>
                <script>
                //
                // set the dimensions and margins of the graph
                width = parseInt(d3.select('#d3_visits').style('width'), 10)
                height = width / 3;
                //alert("hellp");
                var margin = {top: 10, right: 30, bottom: 30, left: 100},
                    width = width - margin.left - margin.right,
                    height = height - margin.top - margin.bottom,
                    legendHeight = 0;

                // append the svg object to the body of the page
                var svg_new = d3.select("#d3_visits")
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
                      .padding([0.4]);
                  svg_new.append("g")
                    .attr("transform", "translate(0," + height + ")")
                    .call(d3.axisBottom(x).tickSizeOuter(0));

                  // get the max value from the data json object for the y axis domain
                  var max = d3.max(data, function(d){ return d3.max(d3.values(d).filter(function(d1){ return !isNaN(d1)}))});
                  console.log(max);
                  var num_digits = Math.floor(Math.log10(max)) + 1;
                  console.log(num_digits);
                  console.log(Math.ceil(max/Math.pow(10,num_digits-1))*Math.pow(10,num_digits-1));

                  // Add Y axis
                  var y = d3.scaleLinear()
                    .domain([0, Math.ceil(max/Math.pow(10,num_digits-1))*Math.pow(10,num_digits-1)])
                    .range([ height, 0 ]);

                  // grid lines on Y axis
                  var yGrid = d3.axisLeft(y).tickSize(-width).tickFormat('').ticks(5);

                  // Another scale for subgroup position?
                  var xSubgroup = d3.scaleBand()
                    .domain(subgroups)
                    .range([0, x.bandwidth()])
                    .padding([0.1]);

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
                    .range(['#B6C2CB','#345EA5']);

                    // Show the bars
                  svg_new.append("g")
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

                  svg_new.selectAll(".tick text")
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

                  var legend = d3.select('#d3_www_legend').selectAll("legend")
                      .data(subgroups);

                  var legend_cells = legend.enter().append("div")
                    .attr("class","legend");

                  var p1 = legend_cells.append("p").attr("class","legend_field");
                  p1.append("span").attr("class","legend_color").style("background",function(d,i) { return color(i) } );
                  p1.insert("text").text(function(d,i) { return d } );

                  // text label for the y axis
                  svg_new.append("text")
                      .attr("transform", "rotate(-90)")
                      .attr("y", 0 - margin.left)
                      .attr("x",0 - (height / 2))
                      .attr("dy", "1em")
                      .style("text-anchor", "middle")
                      .text("Visits");

                </script>
                 <details class="details-chart">
                      <summary data-i18n="view-data-table">View table data</summary>
                      <div class="table-responsive">
                              <table class="table">
                                <caption data-i18n="dr-lastweek">Last Week</caption>
                                <thead>
                                  <th data-i18n="date">Date</th>
                                  <th data-i18n="value">Value</th>
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
                                  <caption>Week</caption>
                                  <thead>
                                    <th>Date</th>
                                    <th>Value</th>
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
                          </div>
                    </details>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="h6" data-i18n="top10-pages-visited">Top 10 pages visited</span></h3>
            <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

              <?php


              $qry = $aaTop10VisitedPagesWeekly;
              // echo "<pre>";
              // print_r($qry);
              // echo "</pre>";

               //var_dump($qry);

                if (count($qry) > 0) { ?>
                   <div class="table-responsive">
                     <table class="table table-striped dataTable no-footer"  id="toptask" data="" role="grid">
                       <thead>
                         <tr>
                           <th class="sorting ascending" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="rank">Rank</th>
                           <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="page" >Page</th>
                           <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="visitors" >Visitors</th>
                           <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="comparison" >Comparison</th>
                         </tr>
                       </thead>
                       <tbody>
                     <?php
                        $rank = 0;
                        foreach ($qry as $row) { ?>
                         <tr>
                           <td><?=++$rank;?></td>
                           <td><?=$row['value'];?></td>
                           <td><?=number_format($row['data'][1]);?></td>
                           <?php
                                  // $curr_term = $row['keys'][0];
                                  // //echo $curr_term;
                                  // $key_index = array_search($curr_term, $key);



                                        //&& (array_key_exists($qryLast[$key_index]['clicks'], $qryLast))
                                    //echo $qryLast[$key_index]['clicks'];
                                 $diff = differ($row['data'][0], $row['data'][1]);
                                 $posi = posOrNeg2($diff);
                                 $pieces = explode(":", $posi);
                                 //
                                 $diff = abs($diff);
                                  //     break;

                            ?>
                           <td><span class="<?=$pieces[0]?>"><?=$pieces[1]?> <?=percent($diff)?></span></td>

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




<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
