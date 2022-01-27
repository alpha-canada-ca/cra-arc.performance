
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
include_once "php/lib/sqlite/DataInterface.php";
include_once 'php/Utils/Date.php';
include_once 'php/lib/sqlite/helpers.php';
use Utils\DateUtils;

$projectId = $_GET['projectId'] ?? '6f7f2cb1';

$dr = $_GET['dr'] ?? 'week';

$lang = $_GET['lang'] ?? 'en';

$db = new DataInterface();
$projectData = $db->getProjectById($projectId)[0] ?? [];
$uxTests = $db->getUxTestsByProjectId($projectId, ['Date','"Launch Date"', 'project_id', '"Project Lead"']);
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
        <li <?php if ($tab=="details") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-details">Details</a></li>
    </ul>
</div>

<?php
$dateUtils = new DateUtils();

$weeklyDatesHeader = $dateUtils->getWeeklyDates('header');
?>

<div class="row mb-4 mt-1">
    <div class="dropdown">
        <button type="button" class="btn bg-white border border-1 dropdown-toggle" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> <span data-i18n="dr-lastweek">Last week</span></button>
        <span class="text-secondary ps-2 text-nowrap dates-header-week"><strong><?=$weeklyDatesHeader['current']['start']?> - <?=$weeklyDatesHeader['current']['end']?></strong></span>
        <span class="text-secondary ps-1 text-nowrap dates-header-week" data-i18n="compared_to">compared to</span>
        <span class="text-secondary ps-1 text-nowrap dates-header-week"><strong><?=$weeklyDatesHeader['previous']['start']?> - <?=$weeklyDatesHeader['previous']['end']?></strong></span>

        <ul class="dropdown-menu" aria-labelledby="range-button" style="">
            <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
            <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
        </ul>

    </div>
</div>

<?php

$projectLeads = array_unique(array_filter(
    array_column($uxTests, 'Project Lead')
));

$startDates = array_filter(array_column($uxTests, 'Date'));
$startDate = $startDates
    ? min(array_map(fn($date) => new DateTime($date), $startDates))->format('M d, Y')
    : 'N/A';

$launchDates = array_filter(array_column($uxTests, 'Launch Date'));
$launchDate = $launchDates
    ? max(array_map(fn($date) => new DateTime($date), $launchDates))->format('M d, Y')
    : 'N/A';

?>

<div class="row mb-4">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Project description</span></h3>
                <p></p>
            </div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Members</span></h3>

                <div class="table-responsive">
                    <table class="table table-striped dataTable no-footer">
                      <caption>Members</caption>
                        <thead>
                        <tr>
                            <th data-i18n="" scope="col">Role</th>
                            <th data-i18n="" scope="col">Name</th>
                            <th data-i18n="" scope="col">Product</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($projectLeads as $projectLead): ?>
                            <tr>
                                <td>Project Lead</td>
                                <td><?=$projectLead?></td>
                                <td></td>
                            </tr>
                        <?php endforeach ?>
                        </tbody>
                    </table>
                </div>

            </div></div>

        <div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Timeline</span></h3>
                <div>
                    <p>Start Date: <?=$startDate?></p>
                    <p>Launch Date: <?=$launchDate?></p>
                    <p>Completed:</p>
                    <p>Year review:</p>
                </div>
            </div></div><div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div>
    </div>
</div>


<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
