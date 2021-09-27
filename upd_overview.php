
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
        <button type="button" class="btn bg-white border border-1 dropdown-toggle" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> Last week </button> <span class="text-secondary ps-2 text-nowrap"><?=$datesHeader[1][0] ?> to <?=$datesHeader[1][1] ?> compared to <?=$datesHeader[0][0] ?> to <?=$datesHeader[0][1] ?></span>

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

        $aaTrendWeeks = array_slice($aaMetricsTrend, -14);
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
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means">Unique visitors</span></h3>
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
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means">Visits to all CRA pages</span></h3>
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
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means">Page views</span></h3>
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
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">Traffic breakdown compared to call volume</span></h3>
            <!-- Chart.js temporary placeholder-->
            <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.4.1/chart.min.js" integrity="sha512-5vwN8yor2fFT9pgPS9p9R7AszYaNn0LkQElTXIsZFCL7ucT8zDCAqlQXDdaqgA1mZP47hdvztBMsIoFxq/FyyQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script> -->

            <canvas id="mixed-chart" width="1262" height="315" style="display: block; box-sizing: border-box; height: 315px; width: 1262px;"></canvas>
            <!-- <script>
            new Chart(document.getElementById("mixed-chart"), {
                type: 'bar',
                data: {
                  labels: ["1900", "1950", "1999", "2050"],
                  datasets: [{
                      label: "Europe",
                      type: "line",
                      borderColor: "#8e5ea2",
                      barPercentage: 0.5,
                      data: [408,547,675,734],
                      fill: false
                    }, {
                      label: "Africa",
                      type: "line",
                      borderColor: "#3e95cd",
                      barPercentage: 0.5,
                      data: [133,221,783,2478],
                      fill: false
                    }, {
                      label: "Europe",
                      type: "bar",
                      backgroundColor: "#2E5EA7",
                      barPercentage: 0.5,
                      data: [408,547,675,734],
                    }, {
                      label: "Africa",
                      type: "bar",
                      backgroundColor: "#B5C2CC",
                      backgroundColorHover: "#3e95cd",
                      barPercentage: 0.5,
                      data: [133,221,783,2478]
                    }
                  ]
                },
                options: {
                  title: {
                    display: true,
                    text: 'Population growth (millions): Europe & Africa'
                  },
                  legend: { display: false }
                }
            });
            </script> -->
             <details class="details-chart">
                  <summary>View table data</summary>
                  <div class="table-responsive">
    <table class="table">
      <caption>Last Week</caption>
      <thead>
        <th>Date</th>
        <th>Value</th>
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
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means">Total impressions from Google</span></h3>
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
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means">Click through rate from Google</span></h3>
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
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means">Average rank on Google</span></h3>
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

$airtable = new Airtable($config);

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
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">Top 10 tasks - GC task success survey</span></h3>
            <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

               <?php

              $qry = $aaTasksStats;
              //var_dump($qry);

                if (count($qry) > 0) { ?>
                  <div class="table-responsive">
              <table class="table table-striped dataTable no-footer">
                <thead>
                  <tr>
                    <th>Task</th>
                    <th>Change</th>
                    <th>Task Success Survey Completed</th>
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

              <!--
              <table id="toptask" class="table table-striped dataTable no-footer" data="" role="grid">
              <thead>
              <tr role="row"><th class="sorting sorting_asc" tabindex="0" aria-controls="toptask" rowspan="1" colspan="1" aria-sort="ascending" aria-label="Topic: activate to sort column descending" style="width: 255.781px;">Topic</th><th class="sorting" tabindex="0" aria-controls="toptask" rowspan="1" colspan="1" aria-label="Sub-topic: activate to sort column ascending" style="width: 325.516px;">Sub-topic</th><th class="sorting" tabindex="0" aria-controls="toptask" rowspan="1" colspan="1" aria-label="Change: activate to sort column ascending" style="width: 281.078px;">Change</th><th class="sorting" tabindex="0" aria-controls="toptask" rowspan="1" colspan="1" aria-label="--: activate to sort column ascending" style="width: 247.625px;">--</th></tr>
              </thead>
              <tbody>






              <tr class="odd">
                <td class="sorting_1">COVID-19</td>
                <td>CRB</td>
                <td><span class="text-danger">+ 5.0%</span></td>
                <td>1,938,203</td>
              </tr><tr class="even">
                <td class="sorting_1">COVID-19</td>
                <td>CRB</td>
                <td><span class="text-danger">+ 5.0%</span></td>
                <td>1,938,203</td>
              </tr><tr class="odd">
                <td class="sorting_1">COVID-19</td>
                <td>CRB</td>
                <td><span class="text-danger">+ 5.0%</span></td>
                <td>1,938,203</td>
              </tr><tr class="even">
                <td class="sorting_1">COVID-19</td>
                <td>CRB</td>
                <td><span class="text-danger">+ 5.0%</span></td>
                <td>1,938,203</td>
              </tr><tr class="odd">
                <td class="sorting_1">cra login</td>
                <td>My account</td>
                <td>− 5.0%</td>
                <td>938,203</td>
              </tr><tr class="even">
                <td class="sorting_1">cra login</td>
                <td>My account</td>
                <td>− 5.0%</td>
                <td>938,203</td>
              </tr></tbody>
            </table>
          -->

            </div></div><div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">Total calls by inquiry line</span></h3>
             <canvas id="myChart" width="1262" height="315" style="display: block; box-sizing: border-box; height: 315px; width: 1262px;"></canvas>
            <!--<script>
            var ctx = document.getElementById('myChart');
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: ['Red', 'Blue', 'Yellow', 'Green', 'Purple'],
                    datasets: [{
                        label: '# of Votes',
                        barPercentage: 0.5,
                        data: [12, 19, 3, 5, 2],
                        backgroundColor: [
                            '#2e5ea7',
                            '#64b5f6',
                            '#26a69a',
                            '#f57f17',
                            '#fbc02d'
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 206, 86, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(153, 102, 255, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            </script> -->
            <details class="details-chart">
              <summary>View table data</summary>
              <?php
if (count($arrFinal) > 0): ?>
        <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>Inquiry Line</th>
          <th>Number of calls</th>
        </tr>
      </thead>
      <tbody>
    <?php foreach ($arrFinal as $key => $value)
    {
        if ($key !== '')
        {
?>
        <tr>
          <td><?=$key; ?></td>
          <td><?=number_format($value); ?></td>
        </tr>
    <?php
        }
    }
?>
      </tbody>
    </table>
</div>
    <?php
endif;
?>
            </details>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-lg-6 col-md-6">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">Did you find what you were looking for?</span></h3>
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
                  <!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/d3/4.13.0/d3.min.js"></script>
                  <script src="https://cdnjs.cloudflare.com/ajax/libs/d3-legend/2.25.6/d3-legend.min.js"></script> -->

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
              <summary>View table data</summary>
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
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">What was wrong?</span></h3>
              <div class="card-body pt-2" id="d3_www_barchart"></div>
              <div id="d3_www_legend"></div>
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
                    var legend = d3.select('#d3_www_legend').selectAll("legend")
                        .data(subgroups);

                    var legend_cells = legend.enter().append("div")
                      .attr("class","legend");

                    var p1 = legend_cells.append("p").attr("class","legend_field");
                    p1.append("span").attr("class","legend_color").style("background",function(d,i) { return color(i) } );
                    p1.insert("text").text(function(d,i) { return d } );


                </script>

            <details class="details-chart">
              <summary>View table data</summary>
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
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-3 gx-3">
      <div class="col-lg-4 col-md-6 col-sm-12">
        <div class="card">
          <div class="card-body card-pad pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">Tasks tested</span></h3>
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
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">Average success rate</span></h3>
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
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">Participants</span></h3>
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
