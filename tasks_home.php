
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

<!--Main content start-->

<h1 class="visually-hidden">Usability Performance Dashboard</h1>

<h2 class="h3 pt-2 pb-2" data-i18n="">Tasks</h2>

<?php
require 'vendor/autoload.php';
use Utils\DateUtils;

include "php/lib/sqlite/DataInterface.php";
include 'php/lib/sqlite/helpers.php';
include "php/Utils/Date.php";

$dateUtils = new DateUtils();

$db = new DataInterface();

$queryDatesWeekly = $dateUtils->getWeeklyDates('gsc');

$tasksDataWithVisitsSql = "
    select id, Task, Topic, \"Sub Topic\", ifnull(visits, 0) as num_visits from tasks
    left join (
        select task_id, sum(visits) as visits from pages
            left join tasks_pages tp on pages.id = tp.page_id
            left join (
                select \"Page URL (v12)\" as url, ifnull(sum(Visits), 0) as visits from pages_metrics
                where julianday(pages_metrics.Date) between julianday('{$queryDatesWeekly['current']['start']}') and julianday('{$queryDatesWeekly['current']['end']}')
                group by url
            ) as pages_visits on pages.Url = pages_visits.url
        group by task_id
    ) as tasks_visits on task_id = tasks.id
";

$tasksDataWithVisitsQuery = $db->getDb()->query($tasksDataWithVisitsSql);
$tasksData = $db->executeQuery($tasksDataWithVisitsQuery);

$weeklyDatesHeader = $dateUtils->getWeeklyDates('header');

// Sort Task data by Task title
usort($tasksData, fn($rowA, $rowB) => strcmp($rowA['Task'], $rowB['Task']));
?>

<!-- Dropdown - date range  -->
<div class="row mb-4 mt-1">
    <div class="dropdown">
        <button type="button" class="btn bg-white border border-1 dropdown-toggle" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> <span data-i18n="dr-lastweek">Last week</span></button>
        <span class="text-secondary ps-2 text-nowrap dates-header-week"><?=$weeklyDatesHeader['current']['start']?> - <?=$weeklyDatesHeader['current']['end']?></span>
        <span class="text-secondary ps-1 text-nowrap dates-header-week" data-i18n="compared_to">compared to</span>
        <span class="text-secondary ps-1 text-nowrap dates-header-week"><?=$weeklyDatesHeader['previous']['start']?> - <?=$weeklyDatesHeader['previous']['end']?></span>

        <ul class="dropdown-menu" aria-labelledby="range-button" style="">
            <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
            <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
        </ul>
    </div>
</div>

<!-- Recent US test results by page - currently listed ALL instead of recent only -->
<div class="row mb-4">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body pt-2">
                <h3 class="card-title"><span class="h6" data-i18n="">List of all Tasks</span></h3>
                <div class="dataTables_wrapper dt-bootstrap5 no-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <?php

                            // Filter tasks w/ 0 pages for now
                            $filteredTasks = array_filter($tasksData, fn($row) => count($db->getPagesByTaskId($row['id'])) > 0);

                            if (count($filteredTasks) > 0) { ?>
                                <div class="table-responsive">
                                    <table id="tasks-dt" class="table table-striped dataTable no-footer">
                                        <thead>
                                        <tr>
                                            <th data-i18n="">Task</th>
                                            <th data-i18n="">Sub-category</th>
                                            <th data-i18n="">Category</th>
                                            <th data-i18n="">Visits</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($filteredTasks as $row): ?>
                                            <tr>
                                                <td><a href="./tasks_summary.php?taskId=<?=$row['id']?>"><?=$row['Task']?></a></td>
                                                <td><?=$row['Sub Topic']?></td>
                                                <td><?=$row['Topic']?></td>
                                                <td><?=number_format($row['num_visits'])?></td>
                                            </tr>
                                        <?php endforeach ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready( function () {
        $('#tasks-dt').DataTable({ deferRender: true, pageLength: 25 });
    } );
</script>
<?php
// $time_elapsed_secs = microtime(true) - $time;
// echo "<p>Time taken: " . number_format($time_elapsed_secs, 2) . " seconds</p>";
?>

<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
