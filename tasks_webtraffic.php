
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
$taskId = $_GET['taskId'] ?? "reczyr0m4MDpsce02"; // arbitrary random taskId
$dateRange = $_GET['dateRange'] ?? 'week';

// Get dates
use Utils\DateUtils;
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
            $arrow = '';
            break;
        }
    }

    return "<span class=\"h3 $colour text-nowrap\"><span class=\"material-icons\">$arrow</span>$percentage%</span>";
}

$weeklyDatesHeader = $dateUtils->getWeeklyDates('header');
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
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="./tasks_summary.php?taskId=<?=$taskId?>" data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-webtraffic">Web traffic</a></li>
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
