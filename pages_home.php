
<?php include "./includes/upd_header.php"; ?>
<?php include "./includes/upd_sidebar.php"; ?>
<?php include "./includes/date-ranges.php"; ?>
<?php include "./includes/functions.php"; ?>
<?php ini_set('display_errors', 1);
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

    <h2 class="h3 pt-2 pb-2" data-i18n="menu-pages">Pages</h2>

    <!-- <div class="tabs sticky">
      <ul>
        <li <?php //if ($tab=="summary") {echo "class='is-active'";} ?>><a href="./pages_summary.php" data-i18n="tab-summary">Summary</a></li>
        <li <?php //if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./pages_webtraffic.php" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php //if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="./pages_searchanalytics.php" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php //if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-pagefeedback">Page feedback</a></li>
        <li <?php //if ($tab=="details") {echo "class='is-active'";} ?>><a href="./pages_details.php" data-i18n="tab-details">Details</a></li>
      </ul>
    </div> -->

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

?>

    <div class="row mb-4 mt-1">

      <form action="pages_summary.php" method="GET" class="row">
          <div class="col-10">
            <!-- <div class="input-group mb-3"> -->
              <!-- <div class="input-group-prepend">
                <span class="input-group-text" id="basic-addon3">https://example.com/users/</span>
              </div> -->
              <input type="text" class="form-control" id="basic-url" name="url" placeholder="Search by URL" aria-label="Search by URL" aria-describedby="basic-addon3">
              <!-- <ul class="dropdown-menu" aria-labelledby="basic-url" style="">
                <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
                <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
              </ul> -->
            <!-- </div> -->
          </div>

          <!-- <div class="col">
            <div class="dropdown">
              <button type="button" class="btn bg-white border border-1 dropdown-toggle" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> <span data-i18n="dr-lastweek">Last week</span></button>
              <ul class="dropdown-menu" aria-labelledby="range-button" style="">
                <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
                <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
              </ul>

            </div>
          </div> -->

          <div class="col">
            <!-- <button type="button" class="btn btn-primary btn-block col-12">Search</button> -->
            <input type="submit" value="Search" class="btn btn-primary btn-block col-12">

          </div>

          <!-- <input type="text" class="form-control col-10" id="basic-url" name="url" placeholder="Search by URL" aria-label="Search by URL" aria-describedby="basic-addon3">
          <input type="submit" value="Search" class="btn btn-primary btn-block col-2"> -->

      </form>

    </div>


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

        //$temp = ['aa-ovrvw-smmry-metrics', 'aa-ovrvw-smmry-fwylf', 'aa-ovrvw-smmry-trnd', 'aa-ovrvw-smmry-tsks']; //, 'fwylf' ];
        $temp = []; //, 'fwylf' ];
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
//            $result[] = $r->requestEntity($json);
            $result[] = get_aa_data($json, $r);
            $j[] = $json;

        }

        //echo var_dump($result[0]);
        foreach ($result as $r)
        {

        }

        // $res = json_decode($result[0], true);
        // $metrics = $res["summaryData"]["filteredTotals"];
        //
        // $res2 = json_decode($result[1], true);
        // $metrics2 = $res2["summaryData"]["filteredTotals"];
        //
        // $aaTasks = json_decode($result[2], true);
        // $aaTasksStats = $aaTasks["rows"];
        //
        // $taskArray = array();
        // foreach ($aaTasksStats as $task)
        // {
        //     $taskArray[] = $task['value'];
        // }
        //
        // $fwylfYes = 0;
        // $fwylfNo = 4;
        // $pv = 8;
        // $visitors = 12;
        // $visits = 16;
        //
        // function differ($old, $new)
        // {
        //     return (($new - $old) / $old);
        // }
        //
        // function numDiffer($old, $new)
        // {
        //     return ($new - $old);
        // }
        //
        // function posOrNeg($num)
        // {
        //     if ($num > 0) return 'text-success:arrow_upward';
        //     else if ($num == 0) return 'text-warning:horizontal_rule';
        //     else return 'text-danger:arrow_downward';
        // }
        //
        // function posOrNeg2($num)
        // {
        //     if ($num > 0) return 'text-success:+';
        //     else if ($num == 0) return 'text-warning:';
        //     else return 'text-danger:-';
        // }
        //
        // function percent($num)
        // {
        //     return round($num * 100, 0) . '%';
        // }
        //
        //
        // $fwylfICantFindTheInfo = 0;
        // $fwylfOtherReason = 4;
        // $fwylfInfoHardToUnderstand = 8;
        // $fwylfError = 12;
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



        // AIRTABLE CONNECTION - SETUP REUQEST AND PARSE RESPONSE
        //--------------------------------------------------------------
        $s = $startLastGSC;
        $e = $endLastGSC;
        $s1 = $startGSC;
        $e1 = $endGSC;


        $config = include ('./php/config-at.php');
        $airtable = new Airtable($config['tasks']);

        // -----------------------------------------------------------------------------------------------
        // GET DATA FROM "Page Feedback" (CRA view) table filtered by date range - last two weekStart
        // -----------------------------------------------------------------------------------------------

        // $params = array(
        //     "filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')))",
        //     "view" => "CRA"
        // );
        $params = array();
        $table = 'Pages';

        $fullArray = [];
        $request = $airtable->getContent($table, $params);
        do
        {
            $response = $request->getResponse();
            $fullArray = array_merge($fullArray, ($response->records));
        }
        while ($request = $response->next());

        $allPages = ( json_decode(json_encode($fullArray), true));//['records'];

        // if there's data (record exist)
        if ( count( $allPages ) > 0 ) {
          // do things here
        }


        $re = array_column($allPages, "fields");

        // echo "<pre>";
        // print_r(count($re));
        // print_r($re);
        // echo "</pre>";

        ?>


    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="h6"></span></h3>
            <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

               <?php

                  $qry = $re;

                    if (count($qry) > 0) { ?>
                      <div class="table-responsive">
                        <table class="table table-striped dataTable no-footer" id="pages_dt">
                          <caption></caption>
                          <thead>
                            <tr>
                              <th data-i18n="" scope="col">Page title</th>
                              <th data-i18n="" scope="col">URL</th>
                            </tr>
                          </thead>
                          <tbody>
                              <?php foreach ($qry as $row) { ?>
                                  <tr>
                                    <td><?= $row['Page Title'] ?></td>
                                    <td><a href="./pages_summary.php?url=https://<?= $row['Url'] ?>"><?= $row['Url'] ?></a></td>
                                    <!-- <td><?//= $row['Url'] ?></td> -->
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
