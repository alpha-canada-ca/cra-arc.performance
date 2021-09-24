
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
        //$temp = ['aa-ovrvw-smmry-metrics', 'aa-ovrvw-webtrafic-top10pages-weekly', 'aa-ovrvw-smmry-trnd', 'aa-ovrvw-webtrafic-top10pages-monthly'];
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
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">Top 10 pages visited</span></h3>
            <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

              <?php


              $qry = $gscTerms['rows'];
              // echo "<pre>";
              // print_r($qry);
              // echo "</pre>";

               //var_dump($qry);

                // if (count($qry) > 0) { ?>
                   <!-- <div class="table-responsive">
                     <table class="table table-striped dataTable no-footer" data="" role="grid">
                       <thead>
                         <tr>
                           <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Rank</th>
                           <th class="sorting ascending" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Page</th>
                           <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Visitors</th>
                           <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Comparison</th>
                         </tr>
                       </thead>
                       <tbody>
                     <?php // foreach ($qry as $row) { ?>
                         <tr>
                           <td><?//=$row['keys'][0];?></td>
                           <td><?//=number_format($row['clicks']);?></td>
                           <?php
                                  // $curr_term = $row['keys'][0];
                                  // //echo $curr_term;
                                  // $key_index = array_search($curr_term, $key);
                                  //
                                  //
                                  // if (is_int($key_index) && ($qryLast[$key_index]['clicks'])) {
                                  //       //&& (array_key_exists($qryLast[$key_index]['clicks'], $qryLast))
                                  //   //echo $qryLast[$key_index]['clicks'];
                                  //      $diff = differ($qryLast[$key_index]['clicks'], $row['clicks']);
                                  //      $posi = posOrNeg2($diff);
                                  //      $pieces = explode(":", $posi);
                                  // //
                                  //      $diff = abs($diff);
                                  // //     break;
                                  //  }
                                  //  else {
                                  //     $diff = 0;
                                  //     $pieces = explode(":", 'text-warning:');
                                  //  }
                            ?>
                           <td><span class="<?//=$pieces[0]?>"><?//=$pieces[1]?> <?//=percent($diff)?></span></td>
                           <td><?//=number_format($row['impressions']);?></td>
                         </tr>
                     <?php //} ?>
                       </tbody>
                     </table>
                   </div>
               <?php// } ?> -->

            </div></div><div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div></div>
          </div>
        </div>
      </div>
    </div>




<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
