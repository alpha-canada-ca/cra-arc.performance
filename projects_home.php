
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

<h2 class="h3 pt-2 pb-2" data-i18n="menu-projects">Projects</h2>

<?php
require 'vendor/autoload.php';

include 'php/lib/sqlite/DataInterface.php';
include 'php/lib/sqlite/helpers.php';
include 'php/Utils/Date.php';
use Utils\DateUtils;

$dateUtils = new DateUtils();

$db = new DataInterface();

$projectsData = getProjectsWithStatusCounts($db);

// for 4 metrics at the top:
$last6Months_startdate = date('Y-m-d', strtotime ("first day of this month", strtotime('-6 months')));
// query returns a row for each project that was completed in the last 6 months (containing the project title)
$last6MonthsCompletedSql = "
    select distinct \"UX Research Project Title\"
    from ux_tests
    where Status == 'Complete' and julianday(\"Launch Date\") > julianday('$last6Months_startdate')
";
$last6MonthsCompletedQuery = $db->getDb()->query($last6MonthsCompletedSql);

$last6MonthsCompleted = count($db->executeQuery($last6MonthsCompletedQuery));

$projectStatuses = array_column($projectsData, 'project_status');

$statusCounts = array(
    'Delayed' => count(array_filter($projectStatuses, fn($status) => $status === 'Delayed')),
    'In Progress' => count(array_filter($projectStatuses, fn($status) => $status === 'In Progress')),
    'Complete' => count(array_filter($projectStatuses, fn($status) => $status === 'Complete')),
);
$projectStatusBadges = array(
    'Delayed' => '<span class="badge rounded-pill bg-warning text-dark align-middle">Delayed</span>',
    'In progress' => '<span class="badge rounded-pill bg-primary align-middle">In progress</span>',
    'Complete' => '<span class="badge rounded-pill bg-success align-middle">Complete</span>',
    'Unknown' => '<span class="badge rounded-pill bg-secondary align-middle">Unknown</span>',
    '' => '',
);

$weeklyDatesHeader = $dateUtils->getWeeklyDates('header');
?>

<!-- Stats cards -->
<div class="row mb-4 gx-4">

    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-body card-pad pt-2">
                <h3 class="card-title"><span class="h6" data-i18n="">Projects in progress</span></h3>
                <div class="row">
                    <div class="col-sm-12">
                        <span class="h3 text-nowrap"><?=number_format($statusCounts['In Progress'])?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-body card-pad pt-2">
                <h3 class="card-title"><span class="h6" data-i18n="">Projects completed (last 6 months)</h3>
                <div class="row">
                    <div class="col-sm-12">
                        <span class="h3 text-nowrap"><?=number_format($last6MonthsCompleted) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-body card-pad pt-2">
                <h3 class="card-title"><span class="h6" data-i18n="">Total projects completed</h3>
                <div class="row">
                    <div class="col-sm-12">
                        <span class="h3 text-nowrap"><?=number_format($statusCounts['Complete']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-body card-pad pt-2">
                <h3 class="card-title"><span class="h6" data-i18n="">Projects delayed</h3>
                <div class="row">
                    <div class="col-sm-12">
                        <span class="h3 text-nowrap"><?=number_format($statusCounts['Delayed']) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


<!-- Dropdown - date range   -->
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


<div class="row mb-4">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body pt-2">
                <h3 class="card-title"><span class="h6" data-i18n="">List of all projects</span></h3>
                <div class="dataTables_wrapper dt-bootstrap5 no-footer">
                    <div class="row">
                        <div class="col-sm-12">
                            <?php
                            if (count($projectsData) > 0) { ?>
                                <div class="table-responsive">
                                    <table class="table table-striped dataTable no-footer">
                                        <thead>
                                        <tr>
                                            <th data-i18n="">Name</th>
                                            <th data-i18n="type">Type</th>
                                            <th data-i18n="">Status</th>
                                            <th data-i18n="">Start date</th>
                                            <th data-i18n="">Launch date</th>
                                            <th data-i18n="">Average test success rate</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        <?php foreach ($projectsData as $row) { ?>
                                            <tr>
                                                <td><a href="/projects_summary.php?projectId=<?=$row['id']?>"><?=$row['title']?></a></td>
                                                <td><span class="badge bg-primary"><?=$row['COPS'] === 1 ? 'COPS' : ''?></span></td>
                                                <td><?=$projectStatusBadges[$row['project_status']]?></td>
                                                <td><?=$row['start_date'] ?? ''?></td>
                                                <td><?=$row['Launch Date'] ?? 'N/A'?></td>
                                                <td><?=$row['avg_success_rate']?>%</td>
                                            </tr>
                                        <?php } ?>
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

<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
