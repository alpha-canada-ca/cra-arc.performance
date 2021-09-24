
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
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="./overview_pagefeedback.php" data-i18n="tab-pagefeedback">Page feedback</a></li>
        <li <?php if ($tab=="calldrivers") {echo "class='is-active'";} ?>><a href="./overview_calldrivers.php" data-i18n="tab-calldrivers">Call drivers</a></li>
        <li <?php if ($tab=="uxtests") {echo "class='is-active'";} ?>><a href="./overview_uxtests.php" data-i18n="tab-uxtests">UX tests</a></li>
      </ul>
    </div>

           <?php
require 'vendor/autoload.php';
// use TANIOS\Airtable\Airtable;
//
// // Adobe Analytics
// $time = microtime(true);
// $succ = 0;
//
// if (!isset($_SESSION['CREATED']))
// {
//     $_SESSION['CREATED'] = time();
//     require_once ('./php/getToken.php');
//     $succ = 1;
// }
// else if (time() - $_SESSION['CREATED'] > 86400)
// {
//     session_regenerate_id(true);
//     $_SESSION['CREATED'] = time();
//     require_once ('./php/getToken.php');
//     $succ = 1;
// }
// if (isset($_SESSION["token"]))
// {
//     $succ = 1;
// }
//
// if ($succ === 1)
// {

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

// }

?>

    <div class="row mb-4 mt-1">
      <div class="dropdown">
        <button type="button" class="btn bg-white border border-1 dropdown-toggle" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> Last week </button> <span class="text-secondary ps-2 text-nowrap"><?=$datesHeader[1][0] ?> to <?=$datesHeader[1][1] ?> compared to <?=$datesHeader[0][0] ?> to <?=$datesHeader[0][1] ?></span>

        <!-- <ul class="dropdown-menu" aria-labelledby="range-button" style="">
          <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
          <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
        </ul> -->
        <ul class="dropdown-menu" aria-labelledby="range-button" style="">
          <li id="dr_week"><a class="dropdown-item active" aria-current="true" href="#" data-i18n="dr-lastweek">Last week</a></li>
          <li id="dr_month"><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
        </ul>

      </div>
    </div>
    <script>
      // Update Date range dropdown button with selected value
      $(".dropdown-menu li a").click(function(){
          $(".btn").text($(this).text());
          $(".btn").val($(this).text());
          //prepend the calendar icon
          $(".btn").prepend( "<span class='material-icons align-top' data-i18n='icon-calendar'>calendar_today</span> ");

          $(".dropdown-menu li a").removeClass("active").removeAttr("aria-current");
          $(this).addClass("active").attr("aria-current","true");
          //$(".dates-header-month, .dates-header-week").toggleClass("d-none");


          //add proper translation data-attribute for selected value
          if ($(".btn").text() == "Last week") {
                  $(".btn").attr("data-i18n", "dr-lastweek");
                  //$(".dates-header-week").removeClass("d-none");

                  //$("#dr_week a.dropdown-item").addClass("active").attr("aria-current","true");
                  //$("#dr_month a.dropdown-item").removeClass("active").removeAttr("aria-current");
              }
          else {
                  $(".btn").attr("data-i18n", "dr-lastmonth");
                  //$(".dates-header-month").removeClass("d-none");
                  //$(".dates-header-week").addClass("d-none");
                  //$("#dr_month a.dropdown-item").addClass("active").attr("aria-current","true");;
                  //$("#dr_week a.dropdown-item").removeClass("active").removeAttr("aria-current");
              }
      });

    </script>


        <?php
        $urls = "";
        $url = "";
        // if (substr($url, 0, 8) == "https://")
        // {
        //     $urls = substr($url, 8, strlen($url));
        // }
        // else
        // {
        //     $urls = $url;
        // }

        // $r = new ApiClient($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token']);
        //
        // $temp = ['aa-ovrvw-smmry-metrics', 'aa-ovrvw-smmry-fwylf', 'aa-ovrvw-smmry-trnd', 'aa-ovrvw-smmry-tsks']; //, 'fwylf' ];
        // $result = array();
        // $j = array();
        //
        // foreach ($temp as $t)
        // {
        //
        //     $json = $data[$t];
        //     $json = sprintf($json, $urls);
        //
        //     $json = str_replace(array(
        //         "*previousMonthStart*",
        //         "*previousMonthEnd*",
        //         "*monthStart*",
        //         "*monthEnd*",
        //         "*previousWeekStart*",
        //         "*previousWeekEnd*",
        //         "*weekStart*",
        //         "*weekEnd*"
        //     ) , array(
        //         $previousMonthStart,
        //         $previousMonthEnd,
        //         $monthStart,
        //         $monthEnd,
        //         $previousWeekStart,
        //         $previousWeekEnd,
        //         $weekStart,
        //         $weekEnd
        //     ) , $json);
        //     //$result = api_post($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token'], $api);
        //     $result[] = $r->requestEntity($json);
        //     $j[] = $json;
        //
        // }
        //
        // //echo var_dump($result[0]);
        // foreach ($result as $r)
        // {
        //
        // }

        // $res = json_decode($result[0], true);
        // $metrics = $res["summaryData"]["filteredTotals"];
        //
        // $res2 = json_decode($result[1], true);
        // $metrics2 = $res2["summaryData"]["filteredTotals"];
        //
        // $aaResultTrend = json_decode($result[2], true);
        // $aaMetricsTrend = $aaResultTrend["rows"];
        //
        // $aaTrendWeeks = array_slice($aaMetricsTrend, -14);
        // $aaTrendLastWeek = array_slice($aaTrendWeeks, 0, 7);
        // $aaTrendWeek = array_slice($aaTrendWeeks, -7);
        //
        // $aaTasks = json_decode($result[3], true);
        // $aaTasksStats = $aaTasks["rows"];
        //
        // $taskArray = array();
        // foreach ($aaTasksStats as $task)
        // {
        //     $taskArray[] = $task['value'];
        // }

        // $fwylfYes = 0;
        // $fwylfNo = 4;
        // $pv = 8;
        // $visitors = 12;
        // $visits = 16;

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

        // $diff = differ($metrics[$visitors + 2], $metrics[$visitors + 3]);
        // $pos = posOrNeg($diff);
        // $pieces = explode(":", $pos);
        //
        // $diff = abs($diff);
        //
        // $fwylfICantFindTheInfo = 0;
        // $fwylfOtherReason = 4;
        // $fwylfInfoHardToUnderstand = 8;
        // $fwylfError = 12;
        ?>


    <!-- <div class="row mb-4"> -->

      <?php
// GSC
$data = include ('./php/data-gsc.php');

$type = ['ovrvw-smmry-totals', 'ovrvw-smmry-qryAll'];

$results = 10;

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
        // echo "<pre>";
        // print_r($u);
        // echo "</pre>";
        $gscResp[] = $response;
        // echo "<br>";
        // echo "-----------------------------------<pre>";
        // print_r($response);
        // echo "</pre>";
    }
}

// echo "-----------------------------------<pre>";
// //print_r($gscArr[2]['rows'][0]['keys'][0]);
// print_r($gscArr[2]['rows'][0]['keys'][0]);
// echo "</pre>";

// echo count($gscArr);
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

// $config = include ('./php/config-at.php');
//
// $start2 = microtime(true);
//
// $airtable = new Airtable($config);
//
// //var_dump($taskArray);
// // Tasks in AirTable
// $params = array(
//     "filterByFormula" => 'SEARCH(Task, "' . implode($taskArray, ',') . '") != ""'
// );
// //print_r($params);
// $table = 'Top Task Survey (PP)';
//
// $request = getContentRecursive($airtable, $table, $params);
// $lo = ['fields', ['Task', 'Tasks']];
//
// $con = parseJSON2($request, $lo);
//
// //echo "<br /><br /> Connection Main: ";
// //var_dump ( $con );
// // Enquiry Lines in AirTable
// $params = array(); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
// //print_r($params);
// $table = 'Weekly Calls (2021)';
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
// //var_dump($fullArray);
// $m = ['fields', 'Equiry Line'];
// $l = ['fields', 'Total Calls'];
//
// $con1 = parseJSON($fullArray, $l);
// //var_dump($con1);
// $con2 = parseJSON($fullArray, $m);
// //var_dump($con2);
// $arrFinal = array();
// for ($i = 0;$i < count($con1) - 1;$i++)
// {
//     if (isset($arrFinal[($con2[$i]) ])) $arrFinal[($con2[$i]) ] += $con1[$i];
//     else $arrFinal += array(
//         $con2[$i] => $con1[$i]
//     );
// }
// //var_dump($arrFinal);
// $params = array(); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
// //print_r($params);
// $table = 'User Testing';
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
// //var_dump($fullArray);
// $m = ['fields', '# of Users'];
// $l = ['fields', 'Success Rate'];
//
// $con1 = parseJSON($fullArray, $m);
// $con2 = parseJSON($fullArray, $l);
//
// $totalTasks = number_format(count($fullArray));
// $avgSuccessRate = percent(array_sum($con2) / $totalTasks);
// $sumNumUsers = number_format(array_sum($con1));
//
// //echo 'total tasks: ' . $totalTasks . "<br /><br />avg success rate: " . $avgSuccessUsers . '<br />br />sum of users: ' . $sumNumUsers;
//
//

?>

<?php
$gscLastTerms = $gscArr[0];

$lastTerm = $gscTerms['rows'][0]['keys'][0];
$lastClicks = $gscTerms['rows'][0]['clicks'];
$lastCtr = $gscTerms['rows'][0]['ctr'];
$lastImp = $gscTerms['rows'][0]['impressions'];
$lastPos = $gscTerms['rows'][0]['position'];

$gscTerms = $gscArr[1];

$term = $gscTerms['rows'][0]['keys'][0];
$clicks = $gscTerms['rows'][0]['clicks'];
$ctr = $gscTerms['rows'][0]['ctr'];
$imp = $gscTerms['rows'][0]['impressions'];
$pos = $gscTerms['rows'][0]['position'];

$diff = differ($lastImp, $imp);
$posi = posOrNeg($diff);
$pieces = explode(":", $posi);

$diff = abs($diff);
?>



    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">Top 10 search terms from Google</span></h3>
            <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

              <?php

              $gscLastTerms = $gscArr[2];
              $qryLast = $gscLastTerms['rows'];

              //$key = array_key('308871', $qryLast);

              $key = array_column(array_column($qryLast, 'keys'),0);

              //print_r(array_search('cra', $key));
              // echo "<pre>";
              // print_r($key);
              // echo "</pre>";
              // echo "<pre>";
              // print_r($qryLast);
              // echo "</pre>";



              $gscTerms = $gscArr[3];
              $qry = $gscTerms['rows'];
              // echo "<pre>";
              // print_r($qry);
              // echo "</pre>";

               //var_dump($qry);

                 if (count($qry) > 0) { ?>
                   <div class="table-responsive">
                     <table class="table table-striped dataTable no-footer" id="toptask2" data="" role="grid">
                       <thead>
                         <tr>
                           <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Search term</th>
                           <th class="sorting ascending" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Clicks</th>
                           <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Comparison</th>
                           <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Impressions</th>
                           <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Click through rate (CTR)</th>
                           <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Position</th>
                         </tr>
                       </thead>
                       <tbody>
                     <?php foreach ($qry as $row) { ?>
                         <tr>
                           <td><?=$row['keys'][0];?></td>
                           <td><?=number_format($row['clicks']);?></td>
                           <?php $curr_term = $row['keys'][0];
                                  //echo $curr_term;
                                  $key_index = array_search($curr_term, $key);
                                  //echo $key_index;
                                  //echo array_key_exists($qryLast[$key_index]['clicks'], $qryLast);
                                  //if ($qryLast[$key_index]['clicks']) {echo "yes";}

                                  if (is_int($key_index) && ($qryLast[$key_index]['clicks'])) {
                                        //&& (array_key_exists($qryLast[$key_index]['clicks'], $qryLast))
                                    //echo $qryLast[$key_index]['clicks'];
                                       $diff = differ($qryLast[$key_index]['clicks'], $row['clicks']);
                                       $posi = posOrNeg2($diff);
                                       $pieces = explode(":", $posi);
                                  //
                                       $diff = abs($diff);
                                  //     break;
                                   }
                                   else {
                                      $diff = 0;
                                      $pieces = explode(":", 'text-warning:');
                                   }
                                  // //$comp = '';
                                //   foreach ($qryLast as $rowLast) {
                                //     if ($curr_term == $rowLast['keys'][0]) {
                                //         $diff = differ($rowLast['clicks'], $row['clicks']);
                                //         $posi = posOrNeg($diff);
                                //         $pieces = explode(":", $posi);
                                //
                                //         $diff = abs($diff);
                                //         break;
                                //     }
                                // }

                            ?>
                           <td><span class="<?=$pieces[0]?>"><?=$pieces[1]?> <?=percent($diff)?></span></td>
                           <td><?=number_format($row['impressions']);?></td>
                           <td><?=percent($row['ctr']);?></td>
                           <td><?=number_format($row['position'],1);?></td>

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
