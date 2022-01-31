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
//require_once './php/get_aa_data.php';

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

function metKPI($num, $old)
{
    if (($num > 0.8) || (abs($old-$num)>0.2))  return 'text-success:check_circle:Met';
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
//$diff = differ($avgTaskSuccess, $avgCmpTaskSuccess);
$pos = posOrNeg($diff);
$pieces = explode(":", $pos);
//
$diff = abs($diff);
$kpi_pos = metKPI($avgTaskSuccess, $avgCmpTaskSuccess);
$kpi_pieces = explode(":", $kpi_pos);
?>


<div class="row mb-2 gx-2">
  <div class="col-lg-6 col-md-6 col-sm-12">
    <div class="card">
      <div class="card-body card-pad pt-2">
        <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-i18n="">Average task success from last UX test</span></h3>
          <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=percent($avgTaskSuccess); ?></span><span class="small"><?//=number_format($metrics[$visitors + 2]) ?></span></div>
            <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?php if (count($prjDatesUnique)>1) {echo percent($diff);}  ?></span></div>
          </div>
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12"><span class="<?=$kpi_pieces[0] ?> text-nowrap"><span class="material-icons"><?=$kpi_pieces[1] ?></span></span><span class="text-nowrap"> <?=$kpi_pieces[2]?> objective of 80% task success or 20 point increase</span></div>
          </div>
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


    <script>
        $(document).ready( function () {
            $('#pages-dt').DataTable({ deferRender: true, pageLength: 25 });
        } );
    </script>

<?php include "includes/upd_footer.php"; ?>
