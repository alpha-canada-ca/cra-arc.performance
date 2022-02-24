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
include 'php/lib/sqlite/helpers.php';
include_once 'php/Utils/Date.php';
include_once 'php/lib/sqlite/DataInterface.php';
require_once './php/get_aa_data.php';

use Utils\DateUtils;

// get query params or use defaults
$taskId = $_GET['taskId'] ?? 'reczyr0m4MDpsce02'; // arbitrary random taskId
$dateRange = $_GET['dateRange'] ?? 'week';
$lang = $_GET['lang'] ?? "en";

function cardPercentage($percentage) {
    if ($percentage === '-' or is_null($percentage)) return '';

    switch (true) {
        case $percentage > 0 : {
            $colour = 'text-success';
            $arrow = 'arrow_upward';
            break;
        }

        case $percentage < 0 : {
            $colour = 'text-danger';
            $arrow = 'arrow_downward';
            break;
        }

        default: {
            $colour = '';
            //$arrow = 'horizontal_rule';
            $arrow = '';
            break;
        }
    }

    return "<span class=\"h3 $colour text-nowrap\"><span class=\"material-icons\">$arrow</span> $percentage%</span>";
}

$dateUtils = new DateUtils();

$db = new DataInterface();

$taskData = $db->getTaskById($taskId)[0];

$taskPages = $db->getPagesByTaskId($taskId, ['Url']);

$taskProjects = $db->getProjectsByTaskId($taskId, ['title']);

$taskUrls = array_column($taskPages, 'Url');

$urlsForQuery = implode(',', array_map(fn($url) => "'$url'", $taskUrls));
$queryDates = $dateUtils->getWeeklyDates('Y-m-d');

$visitsByPageQuery = "(
    SELECT \"Page URL (v12)\" as url, sum(Visits) as visits_current from pages_metrics
    WHERE url IN ($urlsForQuery)
        AND julianday(Date) BETWEEN julianday('" . $queryDates['current']['start'] . "') AND julianday('" . $queryDates['current']['end'] . "')
    GROUP BY url
) as visits_current";
$visitsByPageQueryPriorDates = "(
    SELECT \"Page URL (v12)\" as url, sum(Visits) as visits_previous from pages_metrics
    WHERE url IN ($urlsForQuery)
        AND julianday(Date) BETWEEN julianday('" . $queryDates['previous']['start'] . "') AND julianday('" . $queryDates['previous']['end'] . "')
    GROUP BY url
) as visits_previous";

// todo: fix the duplicates from Airtable
$pagesJoinSql = "
    SELECT DISTINCT id, \"Page Title\", pages.Url, visits_current, visits_previous, (cast(visits_current AS REAL)/cast(visits_previous AS REAL) - 1) as change FROM pages
    LEFT JOIN $visitsByPageQuery on pages.Url = visits_current.url
    LEFT JOIN $visitsByPageQueryPriorDates on pages.Url = visits_previous.url
    WHERE pages.Url IN ($urlsForQuery)
    ORDER BY visits_current DESC
";

$pagesJoinQuery = $db->getDb()->query($pagesJoinSql);

$visitsByPage = $db->executeQuery($pagesJoinQuery);

$totalVisits = array_sum(array_column($visitsByPage, 'visits_current'));
$previousTotalVisits = array_sum(array_column($visitsByPage, 'visits_previous'));

if ($previousTotalVisits === 0) {
    $totalVisitsPercentChange = '-';
} else {
    $totalVisitsPercentChange = round(($totalVisits / $previousTotalVisits - 1) * 100, 0);
}

$weeklyDatesHeader = $dateUtils->getWeeklyDates('header');



// ADDED by KOLE

//-----------------------------
// FUNCTIONS
// we need to add these in functions.php and remove them from every other page
//-----------------------------


//-----------------------------
// FUNCTIONS
// we need to add these in functions.php and remove them from every other page
//-----------------------------

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

function metKPI($old, $new)
{
    if (($new > 0.8) || (($new-$old)>0.2))  return 'text-success:check_circle:Met';
    else return 'text-danger:warning:Did not meet';
}


$uxTestSelectedFields = [
      '"Test title"',
      '"Success Rate"',
      '"Scenario/Questions"',
      'Date',
      '"# of Users"',
      '"Test Type"'
];

$projectTasks = $db->getTasksByProjectId($projectId, ['Task']);
$prjTasks = array_column($projectTasks, 'Task');

// echo "<pre>";
// print_r($prjTasks);
// echo "</pre>";

$projectTests = $db->getUxTestsByTaskId($taskId, $uxTestSelectedFields);
//$taskTests = $db->getUxTestsByTaskId($taskId, $uxTestSelectedFields);
//$relatedUxTests = array_column($projectTests, "Success Rate");


//$relatedUxTests = array_column($taskTests, 'title');

//
// echo "<h4>UX Test correct</h4><pre>";
// print_r($projectTests);
// echo "</pre>";


// ------------------------------------------------------------------
// $prjTasks = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Lookup_Tasks"))));
// $prjPages = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Lookup_Pages"))));
// $prjStatus = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Status"))));
$prjParticipants = array_sum(array_column_recursive($projectTests,"# of Users"));
//echo "# of Users: ".$prjParticipants;

# of Users
// $relatedTasks = $fullArray[0]['fields']['Lookup_Tasks'];//['records'];
// $relatedProjects = $fullArray[0]['fields']['Projects'];

// echo "<pre>";
// print_r($prjTasks);
// echo "</pre>";
// echo "-------------<br/>";
// echo "<pre>";
// print_r($prjPages);
// echo "</pre>";



//$prjData = array_column_recursive($fullArray,"fields");
$prjData = $projectTests;

// echo "<pre>";
// print_r($prjData);
// echo "</pre>";

//sort the array by Date
usort($prjData, function($b, $a) {
   return new DateTime($a['Date']) <=> new DateTime($b['Date']);
 });

$prjByGroupType = group_by('Test Type', $prjData);


// echo "<pre>";
// print_r($prjByGroupType);
// //print_r($prjData[0]['Success Rate']);
// echo "</pre>";

//-------------------------------------------------------
// there are 2 ways to get the latest two UX testings per project
//-------------------------------------------------------
// 1. sort the array by DATE and group it by the Test Type and get the last two UX test types
// 2. get the unique dates for all tests (as an array), sort the array and then with foreach loops get the last two tests
//-------------------------------------------------------


// 1.------------------------------------------------------

$prjDatesUnique = array_values(array_unique(array_flatten(array_column_recursive($prjData,"Date"))));
// echo "<pre>";
// print_r($prjDatesUnique);
// echo "</pre>";


$latestTestDate = $prjDatesUnique[0];
$compareTestDate = $prjDatesUnique[1];

//echo count($prjDatesUnique);
//
// echo "<pre>";
// print_r($latestTestDate);
// echo "</pre>";
// echo "<br>";
// echo "<pre>";
// print_r($compareTestDate);
// echo "</pre>";

if (count($prjDatesUnique)>1) {
    foreach ($prjData as $item) {
      if ($item['Date'] == $latestTestDate) {
        $latestTest[] = $item;
      }
      if ($item['Date'] == $compareTestDate) {
        $compareTest[] = $item;
      }
      // code...
    }


    //
    //
    // echo "<pre>";
    // print_r($latestTest);
    // echo "</pre>";
    // echo "<br>";
    // echo "<pre>";
    // print_r($compareTest);
    // echo "</pre>";

    $avgTaskSuccess = (array_sum(array_column_recursive($latestTest, "Success Rate")))/(count($latestTest));
    $avgCmpTaskSuccess = (array_sum(array_column_recursive($compareTest, "Success Rate")))/(count($compareTest));

}
else {
    foreach ($prjData as $item) {
      if ($item['Date'] == $latestTestDate) {
        $latestTest[] = $item;
      }
    }

    $avgTaskSuccess = (array_sum(array_column_recursive($latestTest, "Success Rate")))/(count($latestTest));
    $avgCmpTaskSuccess = $avgTaskSuccess;

}



//-------------------------------------------------------------------


// List of UX tests for the Task
//------------------------------

$uxTestSelectedFields2 = [
      '"Test title"',
      '"Success Rate"',
      '"Test Type"',
      'Date',
      '"# of Users"'
];

$taskTests = $db->getUxTestsByTaskId($taskId, $uxTestSelectedFields2);
//$taskTests = $db->getUxTestsByTaskId($taskId, $uxTestSelectedFields);
$relatedUxTests = array_column($taskTests, "Success Rate");

?>

<div class="back_link">
    <span class="material-icons align-top">west</span> <a href="./tasks_home.php" alt="Back to tasks home page">Tasks</a>
</div>

<!-- <div class="row">
    <h2 class="h3 pt-2 pb-2 d-inline-block" data-i18n=""><?=$taskData['Task']?></h2>
</div> -->
<h2 class="h3 pt-2 pb-2" data-i18n=""><?=$taskData['Task']?></h2>

<div class="page_header back_link">
        <span id="page_project">
              <?php
              if (count($taskProjects) > 0) {
                  echo '<span class="material-icons align-top">folder</span>';
              }

              echo implode(", ", array_map(function($project) {
                  return '<a href="./projects_summary.php?projectId='.$project['id'].'" alt="Project: '.$project['title'].'">' . $project['title'] . '</a>';
                  //SWITCH TO THIS line after the summary page is done
                  //return '<a href="./projects_summary.php?prj='.$project.'" alt="Project: '.$project.'">' . $project . '</a>';
              }, $taskProjects));
              ?>
         </span>
</div>

<div class="tabs sticky">
    <ul>
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./tasks_webtraffic.php?taskId=<?=$taskId?>" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="./tasks_searchanalytics.php?taskId=<?=$taskId?>" data-i18n="tab-searchanalytics">Search analytics</a></li>
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
        <span class="text-secondary ps-3 text-nowrap dates-header-week"><strong><?=$weeklyDatesHeader['current']['start']?> - <?=$weeklyDatesHeader['current']['end']?></strong></span>
        <span class="text-secondary ps-1 text-nowrap dates-header-week" data-i18n="compared_to">compared to</span>
        <span class="text-secondary ps-1 text-nowrap dates-header-week"><strong><?=$weeklyDatesHeader['previous']['start']?> - <?=$weeklyDatesHeader['previous']['end']?></strong></span>

        <ul class="dropdown-menu" aria-labelledby="range-button" style="">
            <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
            <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
        </ul>
    </div>
</div>

<?php
$diff = differ($avgCmpTaskSuccess, $avgTaskSuccess);
// echo $avgCmpTaskSuccess;
// echo "<br>";
// echo $avgTaskSuccess;
//$diff = differ($avgTaskSuccess, $avgCmpTaskSuccess);
$pos = posOrNeg($diff);
$pieces = explode(":", $pos);
//
$diff = abs($diff);
//$kpi_pos = metKPI($avgTaskSuccess, $avgCmpTaskSuccess);
$kpi_pos = metKPI($avgCmpTaskSuccess, $avgTaskSuccess);
$kpi_pieces = explode(":", $kpi_pos);
?>


<div class="row mb-2 gx-2">
  <div class="col-lg-6 col-md-6 col-sm-12">
    <div class="card">
      <div class="card-body card-pad pt-2">
        <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-i18n="">Average task success from last UX test</span></h3>
            <?php
                if (count($prjDatesUnique)>0) { ?>
                    <div class="row">
                      <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=percent($avgTaskSuccess); ?></span><span class="small"><?//=number_format($metrics[$visitors + 2]) ?></span></div>
                      <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?php if (count($prjDatesUnique)>1) {echo percent($diff);}  ?></span></div>
                    </div>
                    <div class="row">
                      <div class="col-lg-12 col-md-12 col-sm-12"><span class="<?=$kpi_pieces[0] ?> text-nowrap"><span class="material-icons"><?=$kpi_pieces[1] ?></span></span><span class="text-nowrap"> <?=$kpi_pieces[2]?> objective of 80% task success or 20 point increase</span></div>
                    </div>
                <?php }
                else { ?>
                    <div class="row">
                      <div class="col-lg-8 col-md-8 col-sm-8"><span class="small">This task hasn't been tested yet.</span></div>
                      <div class="col-lg-4 col-md-4 col-sm-4 text-end"></div>
                    </div>
                <?php }
            ?>
      </div>
    </div>
  </div>
    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-body card-pad pt-2">
                <h3 class="card-title"><span class="h6" data-i18n="">Total visits from all pages</span></h3>
                <div class="row">
                    <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=number_format($totalVisits) ?></span><span class="small"></span></div>
                    <div class="col-lg-4 col-md-4 col-sm-4 text-end"><?=cardPercentage($totalVisitsPercentChange)?></div>
                </div>
            </div>
        </div>
    </div>
 </div>



    <!-- Page data table -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body card-pad pt-2">
                    <h3 class="card-title"><span class="h6" data-i18n="">Visits by page</span></h3>
                    <div class="dataTables_wrapper dt-bootstrap5 no-footer">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="table-responsive">
                                    <table id="pages-dt" class="table table-striped dataTable no-footer">
                                      <caption>Visits by page</caption>
                                        <thead>
                                        <tr>
                                            <th scope="col">Page title</th>
                                            <th scope="col">Url</th>
                                            <th class="text-nowrap" scope="col">Unique visits</th>
                                            <th class="text-nowrap" scope="col">% Change</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php
                                        $pagesSummaryUrl = './pages_summary.php?url=https://';
                                        // todo: datatable stuff - pagination, sorting, etc.

                                        foreach ($visitsByPage as $row) {
                                            echo "<tr>";
                                            echo "<td><a href='$pagesSummaryUrl{$row['Url']}'>{$row['Page Title']}</a></td>";
                                            echo "<td class='small'><a href='https://{$row['Url']}'>{$row['Url']}</a></td>";

                                            $numVisits = number_format($row['visits_current']);
                                            echo "<td>$numVisits</td>";
                                            $percentChange = round($row['change'] * 100, 1) . '%';
                                            $fontColour = $percentChange > 0 ? 'text-success' : (
                                            $percentChange < 0 ? 'text-danger' : ''
                                            );
                                            echo "<td class=\"$fontColour\">$percentChange</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Adobe Analytics - DYFWYWLF code  -->

    <?php

    $r = new ApiClient($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token']);

    $temp = ['aa-pages-smmry-metrics', 'aa-pages-smmry-fwylf'];
    $result = array();
    $j = array();
    $allAPI = array();
    $allj = array();

    $weeklyDatesAA = $dateUtils->getWeeklyDates('aa');
    $monthlyDatesAA = $dateUtils->getMonthlyDates('aa');

    foreach ($temp as $t)
    {

        foreach ($taskUrls as $page)
        {
            $json = $data[$t];

            $json = sprintf($json, $page);

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
                $monthlyDatesAA['previous']['start'],
                $monthlyDatesAA['previous']['end'],
                $monthlyDatesAA['current']['start'],
                $monthlyDatesAA['current']['end'],
                $weeklyDatesAA['previous']['start'],
                $weeklyDatesAA['previous']['end'],
                $weeklyDatesAA['current']['start'],
                $weeklyDatesAA['current']['end'],
            ) , $json);

            $response = get_aa_data($json, $r);
            $result[$page] = json_decode($response,true);
            $j[] = $json;

        }

        $allAPI[] = $result;
        $allj[$t] = $j;

    }

    $result = $allAPI;


    // -----------------------------------------------------------------------
    // METRICS query (Visit metrics and DYFWYWLF- Yes and No answers)
    // -----------------------------------------------------------------------

    $metrics = array_column_recursive($result[0], "filteredTotals");

    $sum_metrics = array_reduce($metrics, function($sums, $row) {
        for ($i = 0; $i < count($row); $i++) {
            $row[$i] = $row[$i] + $sums[$i];
        }

        return $row;
    }, array_map(fn($item) => 0, $metrics[0] ?? []));

    $tmp = array_slice($sum_metrics, 0, 8);

    // -----------------------------------------------------------------------
    // DYFWYWLF query (What went wrong answers)
    // -----------------------------------------------------------------------

    $metrics2 = array_column_recursive($result[1], "filteredTotals");

    $sum_metrics2 = array_reduce($metrics2, function($sums, $row) {
        for ($i = 0; $i < count($row); $i++) {
            $row[$i] = $row[$i] + $sums[$i];
        }

        return $row;
    }, array_map(fn($item) => 0, $metrics2[0] ?? []));


    //DOES THIS PAGE HAS PAGE FEEDBACK TOOL OR NOT
    if (empty($tmp)) {
        echo "None of the pages for this Task have a Page feedback tool!";
    }
    else {
        $metrics = $sum_metrics;
        $metrics2 = $sum_metrics2;

        $fwylfYes = 0;
        $fwylfNo = 4;
        $pv = 8;
        $visitors = 12;
        $visits = 16;

        // function differ($old, $new)
        // {
        //     return (($new - $old) / $old);
        // }

        // function numDiffer($old, $new)
        // {
        //     return ($new - $old);
        // }
        //
        // function posOrNeg($num)
        // {
        //     if ($num > 0) return 'text-success:arrow_upward';
        //     else if ($num == 0) return 'text-warning:';
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

        $fwylfICantFindTheInfo = 0;
        $fwylfOtherReason = 4;
        $fwylfInfoHardToUnderstand = 8;
        $fwylfError = 12;
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


        $monthStartHeader = (new DateTime("first day of last month midnight"))->format($iso);
        $monthEndHeader = (new DateTime("last day of last month midnight"))->format($iso);

        $previousMonthStartHeader = (new DateTime("first day of -2 month midnight"))->format($iso);
        $previousMonthEndHeader = (new DateTime("last day of -2 month midnight"))->format($iso);

        // Monthly date ranges for the Header
        $datesHeaderMonth = [[$previousMonthStartHeader, $previousMonthEndHeader], [$monthStartHeader, $monthEndHeader]];


        ?>


    <!-- END - Adobe Analytics - DYFWYWLF code  -->


    <!-- DYFWYWLF -->

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

                    //just present the Weekly date range data - index 2 and 3 from new_array
                    $mydata = json_encode(array_slice($new_array, 2));


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

                        // List of subgroups = header of the csv files = soil condition here
                        //var subgroups = data.columns.slice(1)
                        var subgroups = <?=$subgroups?>;

                        // List of groups = species here = value of the first column called group -> I show them on the X axis
                        //var groups = d3.map(data, function(d){return(d.group)}).keys()
                        var groups = <?=$groups?>;

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
                              <caption></caption>
                                <thead>
                                <tr>
                                    <th scope="col">Metrics</th>
                                    <th scope="col">Previous Month</th>
                                    <th scope="col">Month</th>
                                    <th scope="col">Previous Week</th>
                                    <th scope="col">Week</th>
                                </tr>
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

                    //just present the Weekly date range data - index 2 and 3 from new_array
                    $my_www_data = json_encode(array_slice($new_www_array, 2));

                    $subgroups_www = json_encode(array("I can't find the info", "Other reason","Info is hard to understand","Error/something didn't work"));

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


                        var data = <?=$my_www_data?>;

                        console.log("www data:")
                        console.log(data)
                        console.log(typeof data)
                        // List of subgroups = header of the csv files = soil condition here
                        //var subgroups = data.columns.slice(1)
                        //var subgroups = data.columns.slice(1)
                        var subgroups = <?=$subgroups_www?>;
                        console.log("www subgroups:")
                        console.log(subgroups)
                        console.log(typeof subgroups)

                        // List of groups = species here = value of the first column called group -> I show them on the X axis
                        //var groups = d3.map(data, function(d){return(d.group)}).keys()
                        var groups = <?=$groups_www?>;
                        console.log("www groups:")
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

                        svg.selectAll(".tick text")
                            //.attr("class","axis_labels")
                            .style("font-size","14px")
                            .style("fill","#666");

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
                        <summary data-i18n="view-data-table">View table data</summary>
                        <div class="table-responsive">
                            <table class="table">
                              <caption></caption>
                                <thead>
                                <tr>
                                    <th scope="col">Metrics</th>
                                    <th scope="col">Previous Month</th>
                                    <th scope="col">Month</th>
                                    <th scope="col">Previous Week</th>
                                    <th scope="col">Week</th>
                                </tr>
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
<?php } ?>


    <!-- Task success by UX test -->
    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Task success by UX test" data-bs-original-title="" title="" data-i18n="">Task success by UX test</span></h3>
            <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

              <?php
                  // uasort($prevPages, function($b, $a) {
                  //    if ($a["data"][3] == $b["data"][3]) {
                  //        return 0;
                  //    }
                  //    return ($a["data"][3] < $b["data"][3]) ? -1 : 1;
                  //  });
                  //
                   // $top15prevPages = array_slice($prevPages, 0, 15);
                   // //$top5Decrease = array_reverse(array_slice($fieldsByGroup, -5));
                   $qry = $taskTests;
                   // echo "---<pre>";
                   // print_r($taskTests);
                   // echo "</pre>";

                   if (count($qry) > 0) { ?>
                     <div class="table-responsive">
                       <table class="table table-striped dataTable no-footer" role="grid" id="toptask">
                         <caption>Success rate and scenarios</caption>
                         <thead>
                           <tr>
                             <th class="sorting" aria-controls="toptask" aria-label="Project" data-i18n="" scope="col">UX Test</th>
                             <th class="sorting" aria-controls="toptask" aria-label="Date" data-i18n="" scope="col">Date</th>
                             <th class="sorting" aria-controls="toptask" aria-label="Scenario" data-i18n="" scope="col">Test Type</th>
                             <th class="sorting" aria-controls="toptask" aria-label="Result" data-i18n="" scope="col">Success rate</th>
                           </tr>
                         </thead>
                         <tbody>
                       <?php foreach ($qry as $row) {
                         // echo "---<pre>";
                         // print_r($row);
                         // echo "</pre>";
                         // '"Test title"',
                         // '"Success Rate"',
                         // '"Scenario/Questions"',
                         // 'Date',
                         // '"# of Users"'

                         ?>
                           <tr>
                             <td><?=$row['Test title']?></td>
                             <td><?=date("Y-m-d", strtotime($row['Date']))?></td>
                             <td><?=$row['Test Type']?></td>
                             <td><?=percent($row['Success Rate'])?></td>
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


    <script>
        $(document).ready( function () {
            $('#pages-dt').DataTable({ deferRender: true, pageLength: 25 });
        } );
    </script>

<?php include "includes/upd_footer.php"; ?>
