
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
include_once "php/lib/sqlite/DataInterface.php";
include_once 'php/Utils/Date.php';
include_once 'php/lib/sqlite/helpers.php';
require_once ('./php/get_aa_data.php');
use TANIOS\Airtable\Airtable;
use Utils\DateUtils;

//$projectId = $_GET['projectId'] ?? '6f7f2cb1';
$projectId = $_GET['projectId'] ?? '6f7f2cae';

$dr = $_GET['dr'] ?? 'week';

$lang = $_GET['lang'] ?? 'en';

$db = new DataInterface();
$projectData = $db->getProjectById($projectId)[0] ?? [];

$projectPages = $db->getPagesByProjectId($projectId, ['Url']);
$projectPages = array_column($projectPages, 'Url');

$projectTasks = $db->getTasksByProjectId($projectId, ['Task']);
$prjTasks = array_column($projectTasks, 'Task');

?>

<?php
// for statuses and stuff
$projectsData = getProjectsWithStatusCounts($db);

$getStatusCount = fn($colName) => compose(
    makeFilter(fn($row) => $row['id'] == $projectId),
    makeSelectCol($colName),
    fn($testsDelayed) => count($testsDelayed) > 0
        ? $testsDelayed[0]
        : null,
)($projectsData);

$statusCounts = array(
    'Delayed' => $getStatusCount('num_tests_delayed') ?? 0,
    'In progress' => $getStatusCount('num_tests_in_progress') ?? 0,
    'Complete' => $getStatusCount('num_tests_complete') ?? 0,
);

// todo: write an sql query to get status
function getProjectStatus($statusCounts): string
{
    switch(true) {
        case $statusCounts['Delayed'] > 0:
            return 'Delayed';
        case $statusCounts['In progress'] > 0:
            return 'In progress';
        case $statusCounts['Complete'] > 0:
            return 'Complete';

        default: return '';
    }
}

$projectStatus = getProjectStatus($statusCounts);

$projectStatusBadges = array(
    'Delayed' => '<span class="badge rounded-pill bg-warning text-dark align-middle">Delayed</span>',
    'In progress' => '<span class="badge rounded-pill bg-primary align-middle">In progress</span>',
    'Complete' => '<span class="badge rounded-pill bg-success align-middle">Complete</span>',
    'Unknown' => '',
    '' => '',
);


// FOR filtering records in Airtable API call.

$qTasks="";
foreach ($projectTasks as $task) {
  $qTasks = $qTasks."Lookup_Task = '".$task['Task']."'".",";
  //$qTasks = $qTasks.",";
}

$qTasks = substr($qTasks, 0, -1);
//print_r($qTasks);
?>


<h1 class="visually-hidden">Usability Performance Dashboard</h1>
<div class="back_link"><span class="material-icons align-top">west</span> <a href="./projects_home.php" alt="Back to Projects home page">Projects</a></div>

    <h2 class="h3 pt-2 pb-2" data-i18n=""><?=$projectData['title']?>
        <span class="h5 d-inline-block mb-0 align-top ms-1">
            <?=$projectStatusBadges[$projectStatus]?>
        </span>
    </h2>

    <!-- Tabs menu -->
    <div class="tabs sticky">
        <ul>
            <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="./projects_summary.php?projectId=<?=$projectId?>" data-i18n="tab-summary">Summary</a></li>
            <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./projects_webtraffic.php?projectId=<?=$projectId?>" data-i18n="tab-webtraffic">Web traffic</a></li>
            <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="./projects_searchanalytics.php?projectId=<?=$projectId?>" data-i18n="tab-searchanalytics">Search analytics</a></li>
            <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="./projects_pagefeedback.php?projectId=<?=$projectId?>" data-i18n="tab-pagefeedback">Page feedback</a></li>
            <li <?php if ($tab=="calldrivers") {echo "class='is-active'";} ?>><a href="./projects_calldrivers.php?projectId=<?=$projectId?>" data-i18n="tab-calldrivers">Call drivers</a></li>
            <li <?php if ($tab=="uxtests") {echo "class='is-active'";} ?>><a href="./projects_uxtests.php?projectId=<?=$projectId?>" data-i18n="tab-uxtests">UX tests</a></li>
            <li <?php if ($tab=="details") {echo "class='is-active'";} ?>><a href="./projects_details.php?projectId=<?=$projectId?>" data-i18n="tab-details">Details</a></li>
        </ul>
    </div>

      <?php

        ini_set('display_errors', 0);
        require 'vendor/autoload.php';
        //use TANIOS\Airtable\Airtable;

        // Adobe Analytics
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

        // require_once ('./php/api_post.php');
        // $config = include ('./php/config-aa.php');
        // $data = include ('./php/data-aa.php');

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
        // echo "<pre>";
        // print_r($datesHeaderMonth);
        // echo "</pre>";
        //
        // echo "<pre>";
        // print_r($datesHeaderMonth);
        // echo "</pre>";



        // }

        ?>

    <!-- Dropdown - date range   -->
    <div class="row mb-4 mt-1">
      <div class="dropdown">
        <button type="button" class="btn bg-white border border-1 dropdown-toggle" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> <span data-i18n="dr-lastweek">Last week</span></button>
            <span class="text-secondary ps-3 text-nowrap dates-header-week"><strong><?=$datesHeader[1][0] ?> - <?=$datesHeader[1][1] ?></strong></span>
            <span class="text-secondary ps-1 text-nowrap dates-header-week" data-i18n="compared_to">compared to</span>
            <span class="text-secondary ps-1 text-nowrap dates-header-week"><strong><?=$datesHeader[0][0] ?> - <?=$datesHeader[0][1] ?></strong></span>
        <?php /*<span class="text-secondary ps-2 text-nowrap dates-header-month d-none"><?=$datesHeaderMonth[1][0] ?> to <?=$datesHeaderMonth[1][1] ?> compared to <?=$datesHeaderMonth[0][0] ?> to <?=$datesHeaderMonth[0][1] ?></span>*/?>

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
   // require 'vendor/autoload.php';
   // include_once "php/lib/sqlite/DataInterface.php";
   // include_once 'php/Utils/Date.php';
   // include_once 'php/lib/sqlite/helpers.php';
   // require_once ('./php/get_aa_data.php');
   // use TANIOS\Airtable\Airtable;
   // use Utils\DateUtils;
   //
   // $projectId = $_GET['projectId'] ?? '6f7f2cb1'; //CERS: 6f7f2cad  CRB: 6f7f2cb1
   //
   // $dr = $_GET['dr'] ?? 'week';
   //
   // $lang = $_GET['lang'] ?? 'en';
   //
   // $db = new DataInterface();
   // $projectData = $db->getProjectById($projectId)[0] ?? [];
   // //
   // // $projectPages = $db->getPagesByProjectId($projectId, ['Url']);
   // // $projectPages = array_column($projectPages, 'Url');
   //
   // $projectTasks = $db->getTasksByProjectId($projectId, ['Task']);
   // $prjTasks = array_column($projectTasks, 'Task');


   // print_r($prjTasks);

   ?>

    <?php

        function differ($old, $new)
        {
            if ($old == 0) {
              return 0;
            }
            else {
              return (($new - $old) / $old);
          }
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


    ?>



      <?php


              $lastMonthAndYear = date('F Y', strtotime($monthStartHeader));
              $twoMonthsAgoAndYear = date('F Y', strtotime($previousMonthStartHeader));
              // echo $currentMonthAndYear;
              // echo "<br/>";
              // echo $lastMonthAndYear;
              // echo "<br/>";
              // echo $twoMonthsAgoAndYear;


              // $s = '27-06-2021';
              // $e = '03-07-2021';
              // $s1 = '04-07-2021';
              // $e1 = '10-07-2021';

              // $s = '29-08-2021';
              // $e = '04-09-2021';
              // $s1 = '05-09-2021';
              // $e1 = '11-09-2021';

              //$dates = [[$startLastGSC, $endLastGSC], [$startGSC, $endGSC]];
              $s = $startLastGSC;
              $e = $endLastGSC;
              $s1 = $startGSC;
              $e1 = $endGSC;
              //$result1 = getMonthRanges('29-05-2021', '16-08-2021');
              //$weeklyMonthRanges = getMonthRanges('22-08-2021', '04-09-2021');

              // NOTE: you can use any kind of date format in the function
              // NOTE: but the output date format of the function is always "2021-06-30"
              //$weeklyMonthRanges = getMonthRanges('22-08-2021', '04-09-2021');
              //$weeklyMonthRanges = getMonthRanges('2021-08-16', '2021-09-14');

              $weeklyMonthRanges = getMonthRanges($s, $e1);

              // //we use GSC - '2 weeks ago' start date and 'last week' end date for this range
              // //$weeklyMonthRanges = getMonthRanges($startLastGSC, $endGSC);
              // echo "<br><br><pre>";
              // print_r($weeklyMonthRanges); //Prev WEEK
              // echo "</pre><br></br>";
              //echo count($result1);

              /* **************************************************************************************************** */
              // Set up config file and credentials for the Airtable API
              /* **************************************************************************************************** */
              //$config = include ('./php/config-at_cd.php');
              $config = include ('./php/config-at.php');


              //time the page load
              $start2 = microtime(true);


              //COMMENT - KOLE for testing
              //--------------------------------------------------------------
              if (count($weeklyMonthRanges) > 1) {
                  //echo "data ranges spread in two months";
                  $curMonth1 = date('F Y', strtotime($weeklyMonthRanges[0]['start']));
                  $curMonth2 = date('F Y', strtotime($weeklyMonthRanges[1]['start']));

                  //echo $curMonth1;
                  //echo $curMonth2;

                  $curQuarter1 = ceil(date("n", strtotime($weeklyMonthRanges[0]['start'])) / 3);
                  $curQuarter2 = ceil(date("n", strtotime($weeklyMonthRanges[1]['start'])) / 3);

                  //echo "<br>";
                  //echo $curQuarter1;
                  //echo $curQuarter2;

                  $curYear1 = date('Y', strtotime($weeklyMonthRanges[0]['start']));
                  $curYear2 = date('Y', strtotime($weeklyMonthRanges[1]['start']));

                  //echo "<br>";
                  //echo $curYear1;
                  //echo $curYear2;

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

                  // $currentMonthAndYear = date('F Y');
                  // //BASE - current quarter
                  // $month = date("n", );
                  //$currentQuarter =
                  //get first month range ($result[0])
                  //get second month range ($result[1])
                  //get the current month for ech array
                  // echo $weeklyMonthRanges[0]['start'];  echo $weeklyMonthRanges[0]['end'];
                  // echo "<br/>";
                  // echo $weeklyMonthRanges[1]['start'];  echo $weeklyMonthRanges[1]['end'];
                  // echo "<br/>";
                  // echo gettype($weeklyMonthRanges[0]['start']);
                  $wMrS1 = $weeklyMonthRanges[0]['start'];
                  $wMrE1 = $weeklyMonthRanges[0]['end'];
                  $wMrS2 = $weeklyMonthRanges[1]['start'];
                  $wMrE2 = $weeklyMonthRanges[1]['end'];

                  //echo $wMrS1;
                  //echo $wMrE1;
                  //echo $wMrS2;
                  //echo $wMrE2;


                  // **************************************************************************************************************************
                  // I M P O R T A N T
                  // IS_AFTER() and IS_BEFORE() functions don't count the border dates when filtering.
                  // that's why we do DATEADD (one day) for is_BEFORE() ...
                  // for IS_AFTER() we don't substract a day for $params1, cause that start day is always Saturday and on Sundays there's no call centre data.
                  // As a correct coding practice, we need to subtact a day from $params1 (TO BE DONE)
                  // for $params2, instead of subtract a day, we use the $params1 end date (that is a one day less the the $params2 start date).
                  // **************************************************************************************************************************

                  //$params = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$s'), IS_BEFORE({CALL_DATE}, DATEADD('$e1',1,'days')), OR(".$qTasks."))");
                  //$params1 = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$wMrS1'), IS_BEFORE({CALL_DATE}, DATEADD('$wMrE1',1,'days')))"); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
                  //$params2 = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$wMrE1'), IS_BEFORE({CALL_DATE}, DATEADD('$wMrE2',1,'days')))"); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
                  $params1 = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$wMrS1'), IS_BEFORE({CALL_DATE}, DATEADD('$wMrE1',1,'days')), OR(".$qTasks."))"); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
                  $params2 = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$wMrE1'), IS_BEFORE({CALL_DATE}, DATEADD('$wMrE2',1,'days')), OR(".$qTasks."))"); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');

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
                    // echo "first monthly range array <br>";
                    // echo count($weeklyRe1);
                    // echo "<br>";
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
                    // echo "second monthly range array <br>";
                    // echo count($weeklyRe2);
                    // echo "<br>";
                  }

                  // WE NEED TO MERGE $weeklyRe1 and $weeklyRe2
                  $fullArray = array_merge($fullArray1, $fullArray2);
                  // echo "full_array_merged<br>";
                  // echo count($fullArray);
                  // echo "<br>";



              }



            //END COMMENT - KOLE for testing
            //--------------------------------------------------------------
              if (count($weeklyMonthRanges) == 1) {

                  //echo "data ranges in one month";
                //$month = date("n", );
                //$currentQuarter = ceil($month / 3);
                  $curMonth = date('F Y', strtotime($endGSC));
                  $curQuarter = ceil(date("n", strtotime($endGSC)) / 3);
                  $curYear = date('Y', strtotime($endGSC));


                  //$curMonth = date('F Y', strtotime($e1));
                  // $curMonth = "November 2021";
                  // $curQuarter = ceil(date("n", strtotime($e1)) / 3);
                  // $curYear = date('Y', strtotime($e1));

                  //echo $curMonth;
                  //echo $curQuarter;
                  //echo $curYear;
                  //echo "<br>";

                  $base = "DCD-".$curYear."-Q".$curQuarter;
                  //$base = "2021 Daily Call Drivers Q4";
                  //echo "base: ". $base;

                  $airtable = new Airtable(array(
                       'api_key'   => $config['call_data']['api_key'],
                       'base'      => $config['call_data']['base'][$base],
                  ));
                  // echo $curMonth;
                  // echo "<br/>";
                  // echo $startLastGSC;
                  // echo "<br/>";
                  // echo $endGSC;
                  // echo "<br/>";
                  // echo gettype($startLastGSC); //returns string
                  // $test_date1 = '2021-07-04';
                  // $test_date2 = '2021-07-10';
                  //NEED to get the BEFORE day for + 1 (it doesnt take the recoreds for july 10, only to the day before) - use DATEADD("07/10/19", 10, "days")
                  //$iso = 'Y-m-d';

                  $s = date("Y-m-d", strtotime($s));
                  $e1 = date("Y-m-d", strtotime($e1));
                  // $s = "2021-11-07";
                  // $e = "2021-11-13";
                  // $s1 = "2021-11-14";
                  // $e1 = "2021-11-20";
                  // echo $s;
                  // echo "<br/>";
                  // echo "$e1";
                  // echo "<br/>";
                  // $e1 = date("Y-m-d", strtotime("2020-06-01"));
                  //$s = (new DateTime($previousWeekStart))->format($iso);

                  //$params = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$s'), IS_BEFORE({CALL_DATE}, DATEADD('$e1',1,'days')))");

                  //FINAL working line
                  //$params = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$s'), IS_BEFORE({CALL_DATE}, DATEADD('$e1',1,'days')), (Lookup_Task_ID = 'rec8UrXPrTCryLYF2'))");
                  //$params = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$s'), IS_BEFORE({CALL_DATE}, DATEADD('$e1',1,'days')), (Lookup_Task_ID = '$taskId'))");


                  //$params = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$s'), IS_BEFORE({CALL_DATE}, DATEADD('$e1',1,'days')), (Lookup_Task_ID = '$taskId'))");

                  // get the query with Look_Task ID
                  $params = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$s'), IS_BEFORE({CALL_DATE}, DATEADD('$e1',1,'days')), OR(".$qTasks."))");
                  // get teh query with Lookup_Taks name instead of Look_Task_Id
                  //$params = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$s'), IS_BEFORE({CALL_DATE}, DATEADD('$e1',1,'days')), (Lookup_Task = '$taskName'))");

                  //$params = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$s'), IS_BEFORE({CALL_DATE}, DATEADD('$e1',1,'days')), OR(Lookup_Task_ID = '$taskId', Lookup_Task_ID='recmoIEMloY2HUjYN'))");
                  //$params = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$s'), IS_BEFORE({CALL_DATE}, DATEADD('$e1',1,'days')), OR(Lookup_Task = 'Apply for CRB', Lookup_Task = 'Determine CRB Eligibility'))");

                  //$params = array("filterByFormula" => "FIND('Apply for CERS', ARRAYJOIN({Lookup_Task}))");rec8UrXPrTCryLYF2
                  //$params = array("filterByFormula" => "{Lookup_Task}=rec8UrXPrTCryLYF2");
                  //$params = array("filterByFormula" => "FIND('recmoIEMloY2HUjYN', ARRAYJOIN({Task (from TPC_ID copy)}))");

                  //$params = array("filterByFormula" => "(Lookup_Task_ID = 'rec8UrXPrTCryLYF2')");

                  //$params = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$s'), IS_BEFORE({CALL_DATE}, DATEADD('$e1',1,'days')), ('Task (from TPC_ID copy)' = 'Get a print version of a document, form or publication'))");
                  //$params = array("filterByFormula" => "AND(IS_AFTER({CALL_DATE}, '$startLastGSC'), IS_BEFORE({CALL_DATE}, DATEADD('$endGSC',1,'days')))"); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
                  // echo $s;
                  // echo "<br/>";
                  // echo "$e1";
                  // echo "<br/>";
                  //parameters are empty when getting the whole table (one month)
                  //$params_monthly = array();
                  //print_r($params);
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
                    //echo "we got data from the table";
                  }
                  //we can use the $dates array with GSC date ranges for '2 weeks ago' and 'last week'.
                  //get the current month
                  //get data with API
                  $fullArray = $weeklyRe;

              }


              $re = ( json_decode(json_encode($fullArray), true));//['records'];

              // echo count($re);
              // echo "<br><br><pre>";
              // print_r($re); //Prev WEEK
              // echo "</pre><br></br>";
              // $s = '06-06-2021';
              // $e = '12-06-2021';
              // $s1 = '13-06-2021';
              // $e1 = '19-06-2021';

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

              // echo count($WeeklyData);
              // echo "<br>";
              // echo count($PWeeklyData);


              // $re = array_filter( $re, function($var) use ($rangeStartW, $rangeEndW) {
              //    $utime = strtotime($var['fields']['CALL_DATE']);
              //    return $utime <= $rangeEndW && $utime >= $rangeStartW;
              // });

              //echo count($re);
              //echo gettype($re[0]['fields']["CALL_DATE"]);
              // echo "<br><br><pre>";
              // print_r($re); //Prev WEEK
              // echo "</pre><br></br>";

              // if there's data (record exist)
              //if ( count( $re ) > 0 ) {
              //if ( count( $re ) > 0 ) {

              if (( count( $WeeklyData ) > 0 ) && ( count( $PWeeklyData ) > 0 )) {

                //var_dump($re);
                //echo "test";

                    // Get just the ['fields'] array of each record -  as a separate array - $all_fields
                    $all_fields = array_column_recursive($WeeklyData, 'fields');
                    $all_fieldsPW = array_column_recursive($PWeeklyData, 'fields');


                    // echo count($all_fields);
                    // echo "<br>";
                    // echo count($all_fieldsPW);
                    // echo "<br><br><pre>";
                    // print_r($all_fieldsPW); //Prev WEEK
                    // echo "</pre><br></br>";

                    //Sort all_fields array by Call_Date key in descending order
                    // if we need an ascernding order, swap the $a and $b variable as function arguments
                    usort($all_fields, function($a, $b) {
                       return new DateTime($a['CALL_DATE']) <=> new DateTime($b['CALL_DATE']);
                     });

                     usort($all_fieldsPW, function($a, $b) {
                        return new DateTime($a['CALL_DATE']) <=> new DateTime($b['CALL_DATE']);
                      });

                    $fieldsByGroup = group_by('Topic', $all_fields);
                    $fieldsByGroupPW = group_by('Topic', $all_fieldsPW);

                    //
                    // echo count($fieldsByGroup);
                    // echo "<br><br><pre>";
                    // print_r($fieldsByGroup); //Prev WEEK
                    // echo "</pre><br></br>";

                    foreach ( $fieldsByGroupPW as &$item ) {
                      $item["Total topic calls"] = array_sum(array_column_recursive($item, "Calls"));
                    }
                    //

                    $i=1;
                    foreach ( $fieldsByGroup as &$item1 ) {
                      // Add Total Topic Calls and Change (in teh number of calls from previous week) keys
                      $item1["Total topic calls"] = array_sum(array_column_recursive($item1, "Calls"));
                      //echo "--------------------------------------<br><br><pre>";
                      //if $item[0]['Topic']
                       //Prev WEEK
                      //echo "</pre><br></br>";
                      //echo array_key_exists($item[0]['Topic'], $fieldsByGroupPW);
                      //if (array_key_exists($item[0]['Topic'], $fieldsByGroupPW)){
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

                    // echo count($fieldsByGroupPW);
                    // echo "--------------------------------------<br><br><pre>";
                    // print_r($fieldsByGroupPW); //Prev WEEK
                    // echo "</pre><br></br>";


                    //uasort -keeps the key associations
                    uasort($fieldsByGroup, function($b, $a) {
                         if ($a["Total topic calls"] == $b["Total topic calls"]) {
                             return 0;
                         }
                         return ($a["Total topic calls"] < $b["Total topic calls"]) ? -1 : 1;
                     });

                     $top5Topics = array_slice($fieldsByGroup, 0, 5);




                     //usort -does not keep the key associations - it becomes an array (no keys)
                     // usort($fieldsByGroup, function($b, $a) {
                     //      if ($a == $b) {
                     //          return 0;
                     //      }
                     //      return ($a < $b) ? -1 : 1;
                     //  });


                     // usort($fieldsByGroup, function($a, $b) {
                     //    return new DateTime($a['Total topic calls']) <=> new DateTime($b['Total topic calls']);
                     //  });

                     // echo "***************************************************************************";
                     //  echo "<pre>";
                     //  print_r( $fieldsByGroup );
                     //  echo "</pre>";







              } //if ( count( $re ) > 0 )

          ?>


    <!-- D3 - TOTAL CALLS BY INQUIRY LINE -->
    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Total number of calls per enquiry line: ITE (Individual Tax Enquiries), e-services, Child and Family Benefits, Buiness Enquiries, etc." data-bs-original-title="" title="" data-i18n="d3-tcbil">Total calls by inquiry line</span></h3>

            <div class="card-body pt-2" id="d3_tcbil"></div>
            <div id="d3_www_legend"></div>
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





                // echo "--------------------------------------<br><br><pre>";
                // print_r($fieldsByGroupELPW); //Prev WEEK
                // echo "</pre><br></br>";


                // echo "--------------------------------------<br><br><pre>";
                // print_r($el); //Prev WEEK
                // echo "</pre><br></br>";

                //USE THIS - THESE ARE WELL DEFINED DATE RANGES
                //-------------------------------------------------------
                //$d3DateRanges = array($datesHeaderMonth[0][0].'-'.$datesHeaderMonth[0][1],$datesHeaderMonth[1][0].'-'.$datesHeaderMonth[1][1],$datesHeader[0][0].'-'.$datesHeader[0][1],$datesHeader[1][0].'-'.$datesHeader[1][1]); // previous $a1
                //$groups = json_encode(array($d3DateRanges[2],$d3DateRanges[3]));
                //----------------------------------------------------


                //$d3DateRanges = array($s.'-'.$e,$s1.'-'.$e1); // previous $a1

                //$d3DateRanges = array("June 06-12", "June 13-19");

                // $s = $startLastGSC;
                // $e = $endLastGSC;
                // $s1 = $startGSC;
                // $e1 = $endGSC;

                $s = date("M d", strtotime($s));
                $e = date("M d", strtotime($e));
                $s1 = date("M d", strtotime($s1));
                $e1 = date("M d", strtotime($e1));

                $d3DateRanges = array($s.'-'.$e,$s1.'-'.$e1); // previous $a1

                $groups = json_encode($d3DateRanges);

                //echo count($d3DateRanges);
                //echo count($groups[0]);

                //$d3Subgroups =  array("Yes","Yes","Yes","Yes","No","No","No","No"); // previous $b1
                //$d3Data = array_slice($metrics, 0, 8); // previous $c1

                // echo "--------------------------------------<br><br><pre>";
                // print_r($groups); //Prev WEEK
                // echo "</pre><br></br>";


                //get the list of all unique Equity lines from THE CURRENT WEEK ONLY!!!!
                //------------------------------------------------------------------------------------------
                //$el = array_values(array_unique(array_column_recursive($fieldsByGroup, "Enquiry_line")));
                //------------------------------------------------------------------------------------------

                //------------------------------------------------------------------------------------------
                //IF we need to get the Equity lines from both weeks (current and previous week)
                //FOR REVIEW: I'm not sure if this is correct way or not - this way we are showing the equity lines from previous week
                // that didn't show up in the current week)
                $elW = array_values(array_unique(array_column_recursive($fieldsByGroup, "Enquiry_line")));
                $elPW = array_values(array_unique(array_column_recursive($fieldsByGroupPW, "Enquiry_line")));
                //------------------------------------------------------------------------------------------/


                $el = array_values(array_unique(array_merge($elW, $elPW)));

                //$el = array_values(array_unique(array_column_recursive($fieldsByGroup, "Enquiry_line")));
                $subgroups = json_encode($el);

                // echo "--------------------------------------<br><br><pre>";
                // print_r($el); //Prev WEEK
                // echo count($el);
                // echo "</pre><br></br>";

                /// ---------------------
                /// MAKE SURE WE ADD THE DATA IN THE RIGHT DATE RANGE - AND TRIPLE CHECK THE RESULTS WITH THE ACTUAL DATA IN THE WEKLY AND PWEEKLY VARIABLES
                /// -----------------------
                for ($i = 0; $i < 2; ++$i) {
                  $final_array["dateRange"] = $d3DateRanges[$i];
                        if ($i==0) {
                            for ($k = 0; $k < count($el); ++$k) {
                              $final_array[$el[$k]] = $fieldsByGroupELPW[$el[$k]]["Total EL calls"];
                            }
                        }
                        else {
                          for ($k = 0; $k < count($el); ++$k) {
                            $final_array[$el[$k]] = $fieldsByGroupEL[$el[$k]]["Total EL calls"];
                          }
                        }
                  // $final_array["No"] = $d3Data[$i+4];
                  $d3_data_w[]=$final_array;
                }
                //$mydata = json_encode($new_array);
                //just present the Weekly date range data - index 2 and 3 from new_array
                // echo "--------------------------------------<br><br><pre>";
                // print_r($d3_data_w); //Prev WEEK
                // echo "</pre><br></br>";
                //$mydata = json_encode(array_slice($new_array, 2));
                $mydata = json_encode($d3_data_w);






                //$groups = json_encode(array_unique($d3Data_DYFWYWLF_DateRanges));
                //just present the Weekly date ranges


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
                var svg_new = d3.select("#d3_tcbil")
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
                      .padding([0.3]);
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
                    .range(['#345EA5','#6CB5F3','#36A69A','#F8C040','#3EE9B7','#F17F2B']);

                    // Show the bars
                  svg_new.append("g")
                      .selectAll("g")
                      // Enter in data = loop group per group
                      .data(data)
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
                      .text("Number of calls");

                </script>
                <details class="details-chart">
                  <summary data-i18n="view-data-table">View table data</summary>
                  <div class="table-responsive">
                    <table class="table">
                      <caption>Total calls by inqury line</caption>
                      <thead>
                        <th data-i18n="" scope="col">Inquiry line</th>
                        <!-- <th>Previous Month</th>
                        <th>Month</th> -->
                        <th scope="col">Number of calls for <?=$d3DateRanges[0]?><!--two weeks ago--></th>
                        <th scope="col">Number of calls for <?=$d3DateRanges[1]?><!--last week--></th>
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

    <!-- TOP 5 CALL DRIVERS table -->
    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="h6" data-i18n="top5-call-drivers">Top 5 call drivers</span></h3>
              <div class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

                <?php

                   $t5t = $top5Topics;

                   //echo gettype($t5t['Total topic calls'][0]);
                   // echo "<pre>";
                   // var_dump($top5Topics);
                   // echo "</pre>";
                   //var_dump($qry);

                     if (count($t5t) > 0) { ?>
                       <div class="table-responsive">
                         <table class="table table-striped dataTable no-footer" id="toptask" role="grid">
                           <caption>Top 5 call drivers</caption>
                           <thead>
                             <tr>
                               <th class="sorting" aria-controls="toptask" aria-label="Topic: activate to sort column" data-i18n="topic" scope="col">Topic</th>
                               <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" scope="col" >Number of calls for <?=$d3DateRanges[1]?></th>
                               <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" scope="col" >Number of calls for <?=$d3DateRanges[0]?></th>
                               <!-- <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Prev Week Calls</th> -->
                               <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="comparison" scope="col">Comparison (# of calls)</th>
                             </tr>
                           </thead>
                           <tbody>
                         <?php foreach ($t5t as $row) {
                           // echo "---<pre>";
                           // print_r($row);
                           // echo "</pre>";

                           ?>
                             <tr>
                               <td><?= array_key_exists('Topic', $row[0]) ? $row[0]['Topic'] : "N/A (No Topic)";?></td>
                               <td><span><?=array_key_exists('Total topic calls', $row) ? number_format($row['Total topic calls']) : "";?></span></td>
                               <td><span><?=array_key_exists($row[0]['Topic'], $fieldsByGroupPW) ? number_format($fieldsByGroupPW[$row[0]['Topic']]['Total topic calls']) : "";?></span></td>
                               <?php
                               $diff = differ( $fieldsByGroupPW[$row[0]['Topic']]['Total topic calls'], $row['Total topic calls'] );
                               $posi = posOrNeg2($diff);
                               $pieces = explode(":", $posi);
                               $diff = abs($diff);
                               ?>
                               <!-- <td><span>--><?//=$fieldsByGroupPW[$row[0]['Topic']]['Total topic calls'];?><!--</span></td> -->
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

<?php

uasort($fieldsByGroup, function($b, $a) {
   if ($a["Change"] == $b["Change"]) {
       return 0;
   }
   return ($a["Change"] < $b["Change"]) ? -1 : 1;
 });

 $top5Increase = array_slice($fieldsByGroup, 0, 5);
 $top5Decrease = array_reverse(array_slice($fieldsByGroup, -5));

?>

  <!-- TOP 5 CALL DRIVERS- - INCREASE table -->
    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="h6" data-i18n="top5-call-drivers-increase">Top 5 call drivers with biggest increase over period</span></h3>
              <div class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">
                <?php
                if (count($top5Decrease) > 0) { ?>
                  <div class="table-responsive">
                    <table class="table table-striped dataTable no-footer" id="toptask3" role="grid">
                      <caption>Top 5 call drivers with biggest increase over period</caption>
                      <thead>
                        <tr>
                          <th class="sorting ascending" aria-controls="toptask3" aria-label="Topic: activate to sort column ascending" data-i18n="topic" scope="col">Topic</th>
                          <!-- <th class="sorting" aria-controls="toptask3" aria-label="Change: activate to sort column" data-i18n="# of calls" scope="col" >Number of calls</th> -->
                          <th class="sorting" aria-controls="toptask3" aria-label="Change: activate to sort column" data-i18n="" scope="col" >Number of calls for <?=$d3DateRanges[1]?></th>
                          <th class="sorting" aria-controls="toptask3" aria-label="Change: activate to sort column" data-i18n="" scope="col" >Number of calls for <?=$d3DateRanges[0]?></th>
                          <th class="sorting" aria-controls="toptask3" aria-label="Change: activate to sort column ascending" data-i18n="comparison" scope="col" >Comparison (# of calls)</th>
                        </tr>
                      </thead>
                      <tbody>
                    <?php foreach ($top5Increase as $row) { ?>
                        <tr>
                          <td><?= array_key_exists('Topic', $row[0]) ? $row[0]['Topic'] : "N/A (No Topic)";?></td>
                          <!-- <td><span><?//=array_sum(array_column_recursive($row, "Calls"));?></span></td> -->
                          <td><span><?=number_format($row['Total topic calls'])?></span></td>
                           <td><span><?=array_key_exists($row[0]['Topic'], $fieldsByGroupPW) ? number_format($fieldsByGroupPW[$row[0]['Topic']]['Total topic calls']) : "";?></span></td>
                            <?php

                            // if the key from this week doesnt exist in the previous week data,
                            // do we make the change value 0% or 100%
                            // ---------------TO BE DETERMINED ------------------
                            if (array_key_exists($row[0]['Topic'], $fieldsByGroupPW)){
                                //$diff = differ( $fieldsByGroupPW[$row[0]['Topic']]['Total topic calls'], $row['Total topic calls'] );
                                $diff = $row['Change'];
                                $posi = posOrNeg2($diff);
                                $pieces = explode(":", $posi);
                                $diff = abs($diff);
                            }
                            else {
                              $diff = 0;
                              $pieces = explode(":", 'text-warning:');
                            }
                            ?>
                            <!-- <td><span>--><?//=$fieldsByGroupPW[$row[0]['Topic']]['Total topic calls'];?><!--</span></td> -->
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

    <!-- TOP 5 CALL DRIVERS - DECREASE table -->
    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="h6" data-i18n="top5-call-drivers-decrease">Top 5 call drivers with biggest decrease over period</span></h3>
              <div class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">
                <?php
                    // uasort($fieldsByGroup, function($b, $a) {
                    //    if ($a["Change"] == $b["Change"]) {
                    //        return 0;
                    //    }
                    //    return ($a["Change"] < $b["Change"]) ? -1 : 1;
                    //  });
                    //
                    //  $top5Increase = array_slice($fieldsByGroup, 0, 5);
                    //  $top5Decrease = array_reverse(array_slice($fieldsByGroup, -5));

                     //var_dump($qry);

                    if (count($top5Decrease) > 0) { ?>
                      <div class="table-responsive">
                        <table class="table table-striped dataTable no-footer" id="toptask2" role="grid">
                          <caption>Top 5 call drivers with biggest decrease over period</caption>
                          <thead>
                            <tr>
                              <th class="sorting ascending" aria-controls="toptask2" aria-label="Topic: activate to sort column ascending" data-i18n="topic" scope="col">Topic</th>
                              <!-- <th class="sorting" aria-controls="toptask2" aria-label="Change: activate to sort column" data-i18n="# of calls" scope="col">Number of calls</th> -->
                              <th class="sorting" aria-controls="toptask2" aria-label="Change: activate to sort column" data-i18n="" scope="col" >Number of calls for <?=$d3DateRanges[1]?></th>
                              <th class="sorting" aria-controls="toptask2" aria-label="Change: activate to sort column" data-i18n="" scope="col" >Number of calls for <?=$d3DateRanges[0]?></th>
                              <th class="sorting" aria-controls="toptask2" aria-label="Change: activate to sort column ascending" data-i18n="comparison" scope="col" >Comparison (# of calls)</th>
                            </tr>
                          </thead>
                          <tbody>
                        <?php foreach ($top5Decrease as $row) { ?>
                            <tr>
                              <td><?= array_key_exists('Topic', $row[0]) ? $row[0]['Topic'] : "N/A (No Topic)";?></td>
                              <!-- <td><span><?//=array_sum(array_column_recursive($row, "Calls"));?></span></td> -->
                              <td><span><?=number_format($row['Total topic calls'])?></span></td>
                               <td><span><?=array_key_exists($row[0]['Topic'], $fieldsByGroupPW) ? number_format($fieldsByGroupPW[$row[0]['Topic']]['Total topic calls']) : "";?></span></td>
                              <?php

                              // if the key from this week doesnt exist in the previous week data,
                              // do we make the change value 0% or 100%
                              // ---------------TO BE DETERMINED ------------------
                              if (array_key_exists($row[0]['Topic'], $fieldsByGroupPW)){
                                  //$diff = differ( $fieldsByGroupPW[$row[0]['Topic']]['Total topic calls'], $row['Total topic calls'] );
                                  $diff = $row['Change'];
                                  $posi = posOrNeg2($diff);
                                  $pieces = explode(":", $posi);
                                  $diff = abs($diff);
                              }
                              else {
                                $diff = 0;
                                $pieces = explode(":", 'text-warning:');
                              }
                              ?>
                              <!-- <td><span>--><?//=$fieldsByGroupPW[$row[0]['Topic']]['Total topic calls'];?><!--</span></td> -->
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
