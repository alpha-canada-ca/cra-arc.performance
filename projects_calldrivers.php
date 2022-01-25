
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

function metKPI($num, $old)
{
    if (($num > 0.8) || (abs($old-$num)>0.2))  return 'text-success:check_circle:Met';
    else return 'text-danger:warning:Did not meet';
}

?>


<?php
require 'vendor/autoload.php';
include_once "php/lib/sqlite/DataInterface.php";
include_once 'php/Utils/Date.php';
include_once 'php/lib/sqlite/helpers.php';
require_once ('./php/get_aa_data.php');
use TANIOS\Airtable\Airtable;
use Utils\DateUtils;

$projectId = $_GET['projectId'] ?? '6f7f2cb1';

$dr = $_GET['dr'] ?? 'week';

$lang = $_GET['lang'] ?? 'en';

$db = new DataInterface();
$projectData = $db->getProjectById($projectId)[0] ?? [];

$projectPages = $db->getPagesByProjectId($projectId, ['Url']);
$projectPages = array_column($projectPages, 'Url');

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

// ADDED by KOLE
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

$projectTests = $db->getUxTestsByProjectId($projectId, $uxTestSelectedFields);

$prjParticipants = array_sum(array_column_recursive($projectTests,"# of Users"));
//echo "# of Users: ".$prjParticipants;

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
    // code...
  }

  $avgTaskSuccess = (array_sum(array_column_recursive($latestTest, "Success Rate")))/(count($latestTest));
  $avgCmpTaskSuccess = $avgTaskSuccess;

}



//-------------------------------------------------------------------

$dateUtils = new DateUtils();

$weeklyDatesHeader = $dateUtils->getWeeklyDates('header');




?>

<h1 class="visually-hidden">Usability Performance Dashboard</h1>
<div class="back_link"><span class="material-icons align-top">west</span> <a href="./projects_home.php" alt="Back to Projects home page">Projects</a></div>

<h2 class="h3 pt-2 pb-2" data-i18n=""><?=$projectData['title']?> <?=$projectStatusBadges[$projectStatus]?></h2>

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


<div class="row mb-4 mt-1">
    <div class="dropdown">
        <button type="button" class="btn bg-white border border-1 dropdown-toggle" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> <span data-i18n="dr-lastweek">Last week</span></button>
        <span class="text-secondary ps-2 text-nowrap dates-header-week"><?=$weeklyDatesHeader['current']['start']?> - <?=$weeklyDatesHeader['current']['end']?></span>
        <span class="text-secondary ps-2 text-nowrap dates-header-week" data-i18n="compared_to"> compared to </span>
        <span class="text-secondary ps-2 text-nowrap dates-header-week"><?=$weeklyDatesHeader['previous']['start']?> - <?=$weeklyDatesHeader['previous']['end']?></span>

        <ul class="dropdown-menu" aria-labelledby="range-button" style="">
            <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
            <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
        </ul>

    </div>
</div>




<!--Main content end-->

<?php include "includes/upd_footer.php"; ?>
