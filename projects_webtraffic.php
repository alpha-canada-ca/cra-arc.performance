
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
include 'php/lib/sqlite/helpers.php';
include_once 'php/Utils/Date.php';
include_once 'php/lib/sqlite/DataInterface.php';

// get query params or use defaults
$projectId = $_GET['projectId'] ?? "6f7f05b5"; // arbitrary random projectId
$dateRange = $_GET['dateRange'] ?? "week";

// Get dates
use Utils\DateUtils;
$dateUtils = new DateUtils();

// Set up queries & get data

$db = new DataInterface();

$uxTests = $db->getUxTestsByProjectId($projectId, ['Date','"Launch Date"', 'project_id']);

$projectsData = getProjectsWithStatusCounts($db);

$filterByProjectId = makeFilterByProjectId($projectId);

$getCompletionDate = compose(
    makeSelectCol('Launch Date'),
    makeMap(fn($launchDate) => date($launchDate)),
    fn($launchDateCol) => max($launchDateCol),
);

// example of this without compose:
//$completionDate = max(array_map(fn($launchDate) => date($launchDate), array_column(array_filter($uxTests, fn($row) => $row['project_id'] == $projectId), 'Launch Date')));
$completionDate = $getCompletionDate($uxTests);

$getAvgSuccessRate = compose(
    makeSelectCol('Success Rate'),
    fn($successRates) => count($successRates) > 0 ? avg($successRates) : 0,
);

$avgSuccessRate = $getAvgSuccessRate($uxTests);

$getMostRecentTestDate = compose(
    makeSelectCol('Date'),
    makeMap(fn($date) => date($date)),
    fn($dates) => count($dates) > 0 ? max($dates) : 0,
);

$filterRowsByMostRecentTestDate = makeFilter(fn($row) => date($row['Date']) === $getMostRecentTestDate($projectsData));

$getLatestTestSuccessRate = compose(
    $filterRowsByMostRecentTestDate,
    makeSelectCol('Success Rate'),
    fn($successRates) => $successRates[0] ?? null,
);

$lastSuccessRate = $getLatestTestSuccessRate($uxTests);

$getTotalUsers = compose(
    makeSelectCol('# Of Users'),
    fn($numUsers) => count($numUsers) > 0 ? array_sum($numUsers) : 0,
);

$totalUsers = $getTotalUsers($uxTests);

$totalTests = count($uxTests);

$getStatusCount = fn($colName) => compose(
    makeFilter(fn($row) => $row['id'] == $projectId),
    makeSelectCol($colName),
    fn($testsDelayed) => count($testsDelayed) > 0
        ? $testsDelayed[0]
        : null,
)($projectsData);

$numTestsDelayed = $getStatusCount('num_tests_delayed') ?? 0;
$numTestsInProgress = $getStatusCount('num_tests_in_progress') ?? 0;
$numTestsComplete = $getStatusCount('num_tests_complete') ?? 0;

$statusCounts = array(
    'Delayed' => $numTestsDelayed,
    'In progress' => $numTestsInProgress,
    'Complete' => $numTestsComplete,
);

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

$projectData = $db->getProjectById($projectId)[0];

$projectPages = $db->getPagesByProjectId($projectId, ['Url']);
$projectUrls = array_column($projectPages, 'Url');
$urlsForQuery = implode(',', array_map(fn($url) => "'$url'", $projectUrls));
$queryDates = $dateUtils->getWeeklyDates('Y-m-d');

// todo: date ranges
$visitsByPageQuery = "(
    SELECT DISTINCT \"Page URL (v12)\" as url, sum(Visits) as visits_current from pages_metrics
    WHERE url IN ($urlsForQuery)
        AND julianday(Date) BETWEEN julianday('" . $queryDates['current']['start'] . "') AND julianday('" . $queryDates['current']['end'] . "')
    GROUP BY url
) as visits_current";
$visitsByPageQueryPriorDates = "(
    SELECT DISTINCT \"Page URL (v12)\" as url, sum(Visits) as visits_previous from pages_metrics
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

$projectStatus = getProjectStatus($statusCounts);

$projectStatusBadges = array(
    'Delayed' => '<span class="badge rounded-pill bg-warning text-dark align-middle">Delayed</span>',
    'In progress' => '<span class="badge rounded-pill bg-primary align-middle">In progress</span>',
    'Complete' => '<span class="badge rounded-pill bg-success align-middle">Complete</span>',
    'Unknown' => '',
    '' => '',
);

function cardPercentage($percentage) {
    if ($percentage === '-') return '';

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
            $arrow = 'horizontal_rule';
            break;
        }
    }

    return "<span class=\"h3 $colour text-nowrap\"><span class=\"material-icons\">$arrow</span>$percentage%</span>";
}

?>

<h1 class="visually-hidden">Usability Performance Dashboard</h1>
<div class="back_link">
    <span class="material-icons align-top">west</span> <a href="./projects_home.php" alt="Back to Projects home page">Projects</a>
</div>

<div class="row">
    <h2 class="h3 pt-2 pb-2 d-inline-block" data-i18n="">
        <?=$projectData['title']?>
        <span class="h5 d-inline-block mb-0 align-top ms-1">
            <?=$projectStatusBadges[$projectStatus]?>
        </span>
    </h2>
</div>

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
        <span class="text-secondary ps-2 text-nowrap dates-header-week" data-i18n="compared_to">compared to</span>
        <span class="text-secondary ps-2 text-nowrap dates-header-week"><?=$weeklyDatesHeader['previous']['start']?> - <?=$weeklyDatesHeader['previous']['end']?></span>

        <ul class="dropdown-menu" aria-labelledby="range-button" style="">
            <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
            <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
        </ul>
    </div>
</div>

<!-- Total visits card -->
<div class="row mb-4 gx-3">
    <div class="col-lg-4 col-md-6 col-sm-12">
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
                                    <thead>
                                        <tr>
                                            <th>Page title</th>
                                            <th>Url</th>
                                            <th class="text-nowrap">Unique visits</th>
                                            <th class="text-nowrap">% Change</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    $pagesSummaryUrl = '/pages_summary.php?url=https://';
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
