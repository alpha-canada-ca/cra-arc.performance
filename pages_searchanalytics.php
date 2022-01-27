
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
$time = microtime(true);
//echo $url;

if (isset($_GET['url'])) {
$url = $_GET['url'];
}
else {
$url = "https://www.canada.ca/en/revenue-agency/services/benefits/recovery-benefit/crb-how-apply.html";
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
// function getSiteOG( $url ){
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

$sUrl = substr($url, 8, strlen($url));

$params =  array( "filterByFormula" => "( Url = '$sUrl' )" );
$table = "Pages";

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

// echo "AT url for tasks<pre>";
// print_r($fullArray);
// echo "</pre>";
//
// echo "AT url for tasks<pre>";
// print_r($fullArray[0]['fields']);
// echo "</pre>";


$relatedTasks = $fullArray[0]['fields']['Lookup_Tasks'];//['records'];
$relatedProjects = $fullArray[0]['fields']['Projects'];

// echo "Tasks<pre>";
// print_r($relatedTasks);
// echo "</pre>";


// echo "Projects<pre>";
// print_r($relatedProjects);
// echo "</pre>";

// if ($relatedProjects == null) {echo "null projects";}
// else {echo $relatedProjects; }


?>



<h1 class="visually-hidden">Usability Performance Dashboard</h1>
<div class="back_link"><span class="material-icons align-top">west</span> <a href="./pages_home.php" alt="Back to Pages home page">Pages</a></div>

      <h2 class="h3 pt-2 pb-2" data-i18n=""><?=getSiteTitle($url)?></h2>
      <p data-i18n="" class="page_url hidden"><?=$url?></p>

      <script type="text/javascript">
            function copy_to_clipboard() {
              navigator.clipboard.writeText('<?=$url?>');
            }
      </script>

      <div class="page_header back_link">
          <span id="page_project">
            <?php if ($relatedProjects != null) { ?>
                <span class="material-icons align-top">folder</span>
                    <?php
                      if (is_array($relatedProjects)) {
                          echo implode(", ",$relatedProjects);
                      }
                      else {
                        echo $relatedProjects;
                      }
                    ?>
               </span>
            <?php } ?>
            <span id="view_url"><span class="material-icons align-top">link</span> View URL </span>
            <span id="copy_url" onclick="copy_to_clipboard()"><span class="material-icons align-top">content_copy</span> Copy URL</span>
      </div>

    <div class="tabs sticky">
      <ul>
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="./pages_summary.php?url=<?=$url?>" data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./pages_webtraffic.php?url=<?=$url?>" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="./pages_pagefeedback.php?url=<?=$url?>" data-i18n="tab-pagefeedback">Page feedback</a></li>
      </ul>
    </div>

           <?php
// require 'vendor/autoload.php';
// use TANIOS\Airtable\Airtable;

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
    require_once ('./php/get_aa_data.php');
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

    $monthStartGSC = (new DateTime("first day of last month midnight"))->format($iso);
    $monthEndGSC = (new DateTime("last day of last month midnight"))->format($iso);

    $previousMonthStartGSC = (new DateTime("first day of -2 month midnight"))->format($iso);
    $previousMonthEndGSC = (new DateTime("last day of -2 month midnight"))->format($iso);

    // echo "<pre>";
    // print_r($monthStart);
    // echo "</pre>";

    $alldates = [[$startLastGSC, $endLastGSC], [$startGSC, $endGSC],[$previousMonthStartGSC,$previousMonthEndGSC],[$monthStartGSC, $monthEndGSC]];
    // echo "<pre>";
    // print_r($alldates);
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

?>

    <div class="row mb-4 mt-1">
      <div class="dropdown">
        <button type="button" class="btn bg-white border border-1 dropdown-toggle" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> <span data-i18n="dr-lastweek">Last week</span></button>
            <span class="text-secondary ps-2 text-nowrap dates-header-week"><strong><?=$datesHeader[1][0] ?> - <?=$datesHeader[1][1] ?></strong></span>
            <span class="text-secondary ps-2 text-nowrap dates-header-week" data-i18n="compared_to">compared to</span>
            <span class="text-secondary ps-2 text-nowrap dates-header-week"><strong><?=$datesHeader[0][0] ?> - <?=$datesHeader[0][1] ?></strong></span>

        <ul class="dropdown-menu" aria-labelledby="range-button" style="">
          <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
          <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
        </ul>

      </div>
    </div>



        <?php
        $urls = "";
        //$url = "";
        if (substr($url, 0, 8) == "https://")
        {
            $urls = substr($url, 8, strlen($url));
        }
        else
        {
            $urls = $url;
        }

        $r = new ApiClient($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token']);

        $temp = ['pages-referrer-type', 'pages-internal-search' ]; //, 'fwylf' ];
        //$temp = ['aa-pages-smmry-metrics', 'aa-pages-smmry-fwylf', 'aa-pages-smmry-trnd', 'aa-ovrvw-smmry-tsks', 'prvs']; //, 'fwylf' ];
        $result = array();
        $j = array();

        $tt = -1;
        //$start1 = microtime(true);
        foreach ($temp as $t)
        {

            $json = $data[$t];

            if ( $t == 'pages-referrer-type' ) {
                $json = sprintf($json, $urls);
            } else if ( $t == 'pages-internal-search' ) {
                $json = sprintf($json, ( 'https://' . $urls ));
            }

            // if ($t == "activityMap2") {
            //     $pgTitle = getSiteTitle($url);
            //     //  echo $pgTitle;
            //     $json = sprintf($json, $pgTitle);
            //     //  print_r($json);
            // }
            // else {
            //     $json = sprintf($json, $urls);
            // }

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

            //echo $json;

            //echo ++$tt;
            //$time_elapsed_secs1 = microtime(true) - $start1;
            //echo "<p>Time for query ".++$tt." taken: " . number_format($time_elapsed_secs1 , 2) . " seconds</p>";
            // echo "<pre>";
            // print_r($json);
            // echo "</pre><br>";

            //$result = api_post($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token'], $api);
//            $result[] = $r->requestEntity($json);
            $result[] = get_aa_data($json, $r);
            //var_dump($result);
            $j[] = $json;

            // $time_elapsed_secs = microtime(true) - $time;
            // echo "<p>Time taken: " . number_format($time_elapsed_secs, 2) . " seconds</p>";

        }

        //echo var_dump($result[0]);
        foreach ($result as $r)
        {

        }

        //$res = json_decode($result[0], true);
        // $metrics = $res["summaryData"]["filteredTotals"];

        // $res2 = json_decode($result[1], true);
        // $metrics2 = $res2["summaryData"]["filteredTotals"];

        $ref = json_decode($result[0], true);
        $referrerType = $ref["rows"];
        //var_dump($referrerType);

        $ref = json_decode($result[1], true);
        $internalSearch = $ref["rows"];

        //var_dump($internalSearch);

        $value_date = array_column($referrerType, 'value');
        $pMonth= array_column($referrerType, 'data')[0];
        $month= array_column($referrerType, 'data')[1];
        $pWeek= array_column($referrerType, 'data')[2];
        $week= array_column($referrerType, 'data')[3];


        // $aaTrendWeeks = array_slice($aaMetricsTrend, -14);

        //
        // //$aaTrendWeeks = array_slice($aaMetricsTrend, -14);
        // $aaTrendWeeks = array_slice($aaMetricsTrend, $index_key, 14);
        // $aaTrendLastWeek = array_slice($aaTrendWeeks, 0, 7);
        // $aaTrendWeek = array_slice($aaTrendWeeks, -7);
        //
        // // $aaTasks = json_decode($result[2], true);
        // // $aaTasksStats = $aaTasks["rows"];
        // //
        // // $taskArray = array();
        // // foreach ($aaTasksStats as $task)
        // // {
        // //     $taskArray[] = $task['value'];
        // // }
        //
        // $fwylfYes = 0;
        // $fwylfNo = 4;
        // $pv = 8;
        // $visitors = 12;
        // $visits = 16;
        //
        // // function differ($old, $new)
        // // {
        // //     return (($new - $old) / $old);
        // // }

        function differ($old, $new)
        {
            if ($old == 0) {
              $dif = $new;
            }
            else {
              $dif = (($new - $old) / $old);
            }
              return $dif;
        }

        function numDiffer($old, $new)
        {
            return ($new - $old);
        }

        function posOrNeg($num)
        {
            if ($num > 0) return 'text-success:arrow_upward';
            else if ($num == 0) return 'text-warning:';
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

        // function percent($num)
        // {
        //     if ($num==0) {
        //       $result = 0;
        //     }
        //     else {
        //       $result = round($num * 100, 0);
        //     }
        //     return $result . '%';
        // }



        // $fwylfICantFindTheInfo = 0;
        // $fwylfOtherReason = 4;
        // $fwylfInfoHardToUnderstand = 8;
        // $fwylfError = 12;
        //
        //
        // //
        // $metricsNew = json_decode($result[1], true);
        // $deviceTypeAndAvgTime = $metricsNew["summaryData"]["filteredTotals"];
        // //$whatWasClicked = $activityMap["rows"];
        // // echo "<pre>";
        // // print_r($deviceTypeAndAvgTime);
        // // echo "</pre>";
        //
        // $deviceDesktop = 0;
        // $deviceMobile = 4;
        // $deviceTablet = 8;
        // $deviceOther = 12;
        // $avgTimeOnPage = 16;
        //
        //
        // $pp = json_decode($result[2], true);
        // $prevPages = $pp["rows"];
        // //$whatWasClicked = $activityMap["rows"];
        // // echo "<pre>";
        // // print_r($prevPages);
        // // echo "</pre>";
        // //
        // $am = json_decode($result[3], true);
        // $activityMap = $am["rows"];
        // // echo "<pre>";
        // // print_r($activityMap);
        // // echo "</pre>";




        ?>

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


              ?>


              <?php
        // GSC
        $data = include ('./php/data-gsc.php');

        $type = ['totals', 'qryAll'];

        $results = 25;

        $gscArr = array();
        $gscResp = array();

        $start2 = microtime(true);

        foreach ($type as $t)
        {

            foreach ($alldates as $d)
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
        //
        // echo "-----------------------------------<pre>";
        // //print_r($gscArr[2]['rows'][0]['keys'][0]);
        // print_r($gscArr);
        // echo "</pre>";

        // echo count($gscArr);
        $time_elapsed_secs = microtime(true) - $start2;

        //totals
        $gscTotals = $gscArr[0];
        // echo "<pre>";
        // print_r($gscTotals);
        // echo "</pre>";

        $lastClicks = $gscTotals['rows'][0]['clicks'];
        $lastCtr = $gscTotals['rows'][0]['ctr'];
        $lastImp = $gscTotals['rows'][0]['impressions'];
        $lastPos = $gscTotals['rows'][0]['position'];

        $gscTotals = $gscArr[1];
        // echo "<pre>";
        // print_r($gscTotals);
        // echo "</pre>";

        $clicks = $gscTotals['rows'][0]['clicks'];
        $ctr = $gscTotals['rows'][0]['ctr'];
        $imp = $gscTotals['rows'][0]['impressions'];
        $pos = $gscTotals['rows'][0]['position'];

        $diff = differ($lastImp, $imp);
        $posi = posOrNeg($diff);
        $pieces = explode(":", $posi);

        $diff = abs($diff);

        ?>
            <!-- Weekly data -->

                <div class="row mb-3 gx-3 datarow">
                  <div class="col-lg-4 col-md-6 col-sm-12">
                    <div class="card">
                      <div class="card-body card-pad pt-2">
                        <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="The number of times this page appeared in Google search results" data-i18n="total-impressions-google">Total impressions from Google</span></h3>
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
                        <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Average rank in Google search results for this page" data-i18n="avg-rank-google">Average rank on Google</span></h3>
                          <div class="row">
                            <div class="col-sm-8"><span class="h3 text-nowrap"><?=number_format($pos) ?></span><span class="small"><?//=number_format($lastPos) ?></span></div>
                            <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?=$diff ?></span></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

        <?php
        $gscTotals = $gscArr[2];
        // echo "<pre>";
        // print_r($gscTotals);
        // echo "</pre>";

        $lastClicks = $gscTotals['rows'][0]['clicks'];
        $lastCtr = $gscTotals['rows'][0]['ctr'];
        $lastImp = $gscTotals['rows'][0]['impressions'];
        $lastPos = $gscTotals['rows'][0]['position'];

        $gscTotals = $gscArr[3];
        // echo "<pre>";
        // print_r($gscTotals);
        // echo "</pre>";

        $clicks = $gscTotals['rows'][0]['clicks'];
        $ctr = $gscTotals['rows'][0]['ctr'];
        $imp = $gscTotals['rows'][0]['impressions'];
        $pos = $gscTotals['rows'][0]['position'];

        $diff = differ($lastImp, $imp);
        $posi = posOrNeg($diff);
        $pieces = explode(":", $posi);

        $diff = abs($diff);


        ?>



  <!-- Top 10 searched weekly data -->

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


        <!-- Top 10 searched weekly data -->
            <div class="row mb-4 datarow">
              <div class="col-lg-12 col-md-12">
                <div class="card">
                  <div class="card-body pt-2">
                    <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Top 25 most used search terms in Google to access this page." data-bs-original-title="" title="" data-i18n="">Top 25 search terms from Google</span></h3>
                    <div class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

                      <?php

                      $gscLastTerms = $gscArr[4];
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



                      $gscTerms = $gscArr[5];
                      $qry = $gscTerms['rows'];
                      // echo "<pre>";
                      // print_r($qry);
                      // echo "</pre>";

                       //var_dump($qry);

                         if (count($qry) > 0) { ?>
                           <div class="table-responsive">
                             <table class="table table-striped dataTable no-footer" id="toptask" role="grid"> <!-- id="pages_dt" -->
                               <caption>Top 25 search terms from Google</caption>
                               <thead>
                                 <tr>
                                   <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="search-terms" scope="col" >Search term</th>
                                   <th class="sorting ascending" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="clicks" scope="col" >Clicks</th>
                                   <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="comparison" scope="col" >Comparison</th>
                                   <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="impressions" scope="col" >Impressions</th>
                                   <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="ctr" scope="col" >Click through rate (CTR)</th>
                                   <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="position" scope="col" >Position</th>
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




    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Search terms from Canada.ca</span></h3>
            <div class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

                  <div class="table-responsive">
                             <table class="table table-striped dataTable no-footer" id="toptask2" role="grid"> <!-- id="pages_dt" -->
                               <caption>Search terms from Canada.ca</caption>
                               <thead>
                                 <tr>
                                   <th class="sorting" aria-controls="toptask2" aria-label="Change: activate to sort column" data-i18n="search-terms" scope="col" >Search term</th>
                                   <th class="sorting ascending" aria-controls="toptask2" aria-label="Change: activate to sort column" data-i18n="clicks" scope="col" >Clicks</th>
                                   <th class="sorting" aria-controls="toptask2" aria-label="Change: activate to sort column" data-i18n="comparison" scope="col" >Comparison</th>
                                 </tr>
                               </thead>
                               <tbody>
                                <?php
                                  //foreach ($aaTrendWeek as $trend)
                                  foreach ($internalSearch as $key=>$value)
                                  {
                                    // don't display the values with 0's
                                    if ( $value['data'][3] != 0 ) {

                                    $diff = differ($value['data'][2], $value['data'][3]);
                                    $pos = posOrNeg2($diff);
                                    $pieces = explode(":", $pos);

                                    $diff = abs($diff);

                                  ?>

                                          <tr>
                                            <td><?=$value['value'] ?></td>
                                            <td><?=number_format($value['data'][3]) ?></td>
                                            <td><span class="<?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?=percent($diff) ?></span></td>
                                          </tr>

                                          <?php
                                  }
                              }

                                  ?>

                                 </tr>
                               </tbody>
                             </table>
                           </div>


            </div></div><div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div></div>
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Referrer Type</span></h3>
            <div class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">


                           <div class="table-responsive">
                             <table class="table table-striped dataTable no-footer" id="toptask3" data="" role="grid"> <!-- id="pages_dt" -->
                               <caption>Referrer Type</caption>
                               <thead>
                                 <tr>
                                   <th class="sorting" aria-controls="toptask3" aria-label="Change: activate to sort column" data-i18n="type" scope="col" >Type</th>
                                   <th class="sorting ascending" aria-controls="toptask3" aria-label="Change: activate to sort column" data-i18n="visits" scope="col" >Visits</th>
                                   <th class="sorting" aria-controls="toptask3" aria-label="Change: activate to sort column" data-i18n="comparison" scope="col" >Comparison</th>
                                 </tr>
                               </thead>
                               <tbody>
                                <?php
                                  //foreach ($aaTrendWeek as $trend)
                                  foreach ($referrerType as $key=>$value)
                                  {

                                    // don't display the values with 0's
                                    if ( $value['data'][3] != 0 ) {

                                    $diff = differ($value['data'][2], $value['data'][3]);
                                    $pos = posOrNeg2($diff);
                                    $pieces = explode(":", $pos);

                                    $diff = abs($diff);

                                  ?>

                                          <tr>
                                            <td><?=$value['value'] ?></td>
                                            <td><?=number_format($value['data'][3]) ?></td>
                                            <td><span class="<?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?=percent($diff) ?></span></td>
                                          </tr>

                                          <?php
                                  }
                              }

                                  ?>


                               </tbody>
                             </table>
                           </div>

            </div></div><div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div></div>
          </div>
        </div>
      </div>
    </div>

    <!-- <div class="row mb-3 gx-3">
      <h4>UX Tests</h4>
    </div> -->

    <?php
    // $time_elapsed_secs = microtime(true) - $time;
    // echo "<p>Time taken: " . number_format($time_elapsed_secs, 2) . " seconds</p>";
    ?>


<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
