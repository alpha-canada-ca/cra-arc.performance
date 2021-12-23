
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
$dateUtils = new DateUtils();

// get query params or use defaults
$projectId = $_GET['projectId'] ?? "6f7f05b5"; // arbitrary random projectId
$dateRange = $_GET['dateRange'] ?? "week";
$lang = $_GET['lang'] ?? "en";

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
            $arrow = 'horizontal_rule';
            break;
        }
    }

    return "<span class=\"h3 $colour text-nowrap\"><span class=\"material-icons\">$arrow</span> $percentage%</span>";
}

$db = new DataInterface();

$uxTests = $db->getUxTestsByProjectId($projectId, ['Date','"Launch Date"', 'project_id']);

$projectsData = getProjectsWithStatusCounts($db);

$prjTasks = $db->getTasksByProjectId($projectId, ['Task']);
$prjPages = $db->getPagesByProjectId($projectId, ['Url']);
$prjStatus = compose(
    makeFilter(fn($row) => $row['id'] == $projectId),
    makeSelectCol('project_status'),
    fn($row) => $row[0]
)($projectsData);

$prjData = $db->getProjectById($projectId)[0];

$last2TestsSql = $db->getDb()->query("
select id,
       \"Test title\",
       \"UX Research Project Title\",
       Date,
       round(avg(\"Success Rate\"), 3) as \"Success Rate\",
       \"Test Type\",
       \"Succesful Users\",
       project_id
from ux_tests
         left join tests_projects tp on ux_tests.id = tp.test_id
where project_id = '$projectId'
group by Date, \"Test Type\"
order by Date DESC
limit 2
");

$last2Tests = $db->executeQuery($last2TestsSql);

$lastTestHasComparison = count($last2Tests) > 1;

$lastTestSuccessRate = round($last2Tests[0]['Success Rate'] * 100, 0);

$prevTestSuccessRate = null;
$successRateChange = null;

if ($lastTestHasComparison) {
    $prevTestSuccessRate = round($last2Tests[1]['Success Rate'] * 100, 0);
    $successRateChange = $prevTestSuccessRate - $lastTestSuccessRate;
}

function metKPIOutput($lastTestSuccess, $change) {
    if (is_null($change)) return '';

    if ($lastTestSuccess >= 80 or $change >= 20) {
        $colour = 'text-success';
        $arrow = 'check_circle';
        $text = 'Met objective of 80% task success or 20 point increase';
    } else {
        $colour = 'text-danger';
        $arrow = 'warning';
        $text = 'Did not meet objective of 80% task success or 20 point increase';
    }

    return "<span class=\"text-nowrap d-inline-block h-100 mt-4\">
                <span class=\"$colour material-icons align-bottom\">$arrow</span>
                <span class=\"align-bottom\">$text</span>
            </span>";
}

$projectUrls = array_column($prjPages, 'Url');

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

<div class="row">
    <h2 class="h3 pt-2 pb-2 d-inline-block" data-i18n="">
        <?=$prjData['title']?>
        <span class="h5 d-inline-block mb-0 align-top ms-1">
            <?=$projectStatusBadges[$prjStatus]?>
        </span>
    </h2>
</div>

<div class="tabs sticky">
    <ul>
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./projects_webtraffic.php?projectId=<?=$projectId?>" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="./projects_searchanalytics.php?projectId=<?=$projectId?>" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="./projects_pagefeedback.php?projectId=<?=$projectId?>" data-i18n="tab-pagefeedback">Page feedback</a></li>
        <li <?php if ($tab=="calldrivers") {echo "class='is-active'";} ?>><a href="./projects_calldrivers.php?projectId=<?=$projectId?>" data-i18n="tab-calldrivers">Call drivers</a></li>
        <li <?php if ($tab=="uxtests") {echo "class='is-active'";} ?>><a href="./projects_uxtests.php?projectId=<?=$projectId?>" data-i18n="tab-uxtests">UX tests</a></li>
        <li <?php if ($tab=="details") {echo "class='is-active'";} ?>><a href="./projects_details.php?projectId=<?=$projectId?>" data-i18n="tab-details">Details</a></li>
    </ul>
</div>

<?php

// ADOBE ANALYTICS API QUERIES PROCESSING
//---------------------------------------------------------------------------------------------------


// Adobe Analytics
$time = microtime(true);

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


$r = new ApiClient($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token']);

$temp = ['aa-pages-smmry-metrics', 'aa-pages-smmry-fwylf'];
$result = array();
$j = array();
$allAPI = array();
$allj = array();

foreach ($temp as $t)
{

    foreach ($projectUrls as $page)
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
            $previousMonthStart,
            $previousMonthEnd,
            $monthStart,
            $monthEnd,
            $previousWeekStart,
            $previousWeekEnd,
            $weekStart,
            $weekEnd
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

$tmp = array_filter(array_slice($sum_metrics, 0, 8));



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
    $pageFeedbackOnPages = 0;
} else {
    $pageFeedbackOnPages = 1;
    //REMOVE AFTER Testing
    $metrics = $sum_metrics;
    $metrics2 = $sum_metrics2;


    $fwylfYes = 0;
    $fwylfNo = 4;
    $pv = 8;
    $visitors = 12;
    $visits = 16;


    $fwylfICantFindTheInfo = 0;
    $fwylfOtherReason = 4;
    $fwylfInfoHardToUnderstand = 8;
    $fwylfError = 12;
}
?>

<div class="row mb-4 mt-1">
    <div class="dropdown">
        <button type="button" class="btn bg-white border border-1 dropdown-toggle" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> <span data-i18n="dr-lastweek">Last week</span></button>
        <span class="text-secondary ps-2 text-nowrap dates-header-week"><?=$datesHeader[1][0] ?> - <?=$datesHeader[1][1] ?></span>
        <span class="text-secondary ps-2 text-nowrap dates-header-week" data-i18n="compared_to"> compared to </span>
        <span class="text-secondary ps-2 text-nowrap dates-header-week"><?=$datesHeader[0][0] ?> - <?=$datesHeader[0][1] ?></span>

        <ul class="dropdown-menu" aria-labelledby="range-button" style="">
            <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
            <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
        </ul>

    </div>
</div>

<div class="row mb-2 gx-2">
    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="card">
            <div class="card-body card-pad pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-i18n="">Average task success from last UX test</span></h3>
                <div class="row mt-3">
                    <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=$lastTestSuccessRate?>%</span></div>
                    <div class="col-lg-4 col-md-4 col-sm-4 text-end"><?=cardPercentage($successRateChange)?></div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <?=metKPIOutput($lastTestSuccessRate, $successRateChange)?>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <?php
    $queryDates = $dateUtils->getWeeklyDates('Y-m-d');
    $urlsForQuery = implode(',', array_map(fn($url) => "'$url'", $projectUrls));

    $totalVisitsQuery = $db->getDb()->query("
select \"Page URL (v12)\", sum(Visits) as visits_current from pages_metrics
where \"Page URL (v12)\" in ($urlsForQuery)
    AND julianday(Date) BETWEEN julianday('" . $queryDates['current']['start'] . "') AND julianday('" . $queryDates['current']['end'] . "')
");

    $prevTotalVisitsQuery = $db->getDb()->query("
select \"Page URL (v12)\", sum(Visits) as visits_previous from pages_metrics
where \"Page URL (v12)\" in ($urlsForQuery)
    AND julianday(Date) BETWEEN julianday('" . $queryDates['previous']['start'] . "') AND julianday('" . $queryDates['previous']['end'] . "')
");

    $totalVisits = array_column($db->executeQuery($totalVisitsQuery), 'visits_current')[0] ?? 0;
    $prevTotalVisits = array_column($db->executeQuery($prevTotalVisitsQuery), 'visits_previous')[0] ?? 0;

    if ($prevTotalVisits === 0) {
        $totalVisitsPercentChange = '-';
    } else {
        $totalVisitsPercentChange = round(($totalVisits / $prevTotalVisits - 1) * 100, 0);
    }

    ?>

    <div class="col-lg-6 col-md-6 col-sm-12">
        <div class="card h-100">
            <div class="card-body card-pad pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-i18n="">Total visits from all pages</span></h3>
                <div class="row mt-3">
                    <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=number_format($totalVisits) ?></span></div>
                    <div class="col-lg-4 col-md-4 col-sm-4 text-end"><?=cardPercentage($totalVisitsPercentChange)?></div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row my-4">
    <div class="col-lg-12 col-md-12">
        <div class="card">
            <div class="card-body pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Participant tasks</span></h3>
                <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

                            <?php

                            $qry = $prjTasks;

                            if (count($qry) > 0) { ?>
                                <div class="table-responsive">
                                    <table class="table table-striped dataTable no-footer">
                                        <tbody>
                                        <?php foreach ($qry as $row) { ?>
                                            <tr>
                                                <td><?=$row['Task']?></td>
                                            </tr>
                                        <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php }
                            else { ?>
                                <p>No tasks associated with this project.</p>
                                <?php
                            }
                            ?>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="row mb-4">
    <div class="col-lg-6 col-md-6">
        <div class="card">
            <div class="card-body pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Responses to  'Did you find what you were looking for?' on pages with page feedback question" data-bs-original-title="" title="" data-i18n="d3-dyfwywlf">Did you find what you were looking for?</span></h3>
                <div class="card-body pt-2" id="d3_dyfwywlf_barchart"></div>
                <!-- Did you find what you werel looking - D3 100% Stacked Bar chart -->
                <?php

                if ($pageFeedbackOnPages) {

                    $d3Data_DYFWYWLF_DateRanges = array($datesHeaderMonth[0][0].'-'.$datesHeaderMonth[0][1],$datesHeaderMonth[1][0].'-'.$datesHeaderMonth[1][1],$datesHeader[0][0].'-'.$datesHeader[0][1],$datesHeader[1][0].'-'.$datesHeader[1][1]); // previous $a1
                    $d3Data_DYFWYWLF_subgroups =  array("Yes","Yes","Yes","Yes","No","No","No","No"); // previous $b1
                    $d3Data_DYFWYWLF_data = array_slice($metrics, 0, 8); // previous $c1

                    for ($i = 0; $i < 4; ++$i) {
                        $final_array["dateRange"] = $d3Data_DYFWYWLF_DateRanges[$i];
                        $final_array["Yes"] = $d3Data_DYFWYWLF_data[$i];
                        $final_array["No"] = $d3Data_DYFWYWLF_data[$i+4];
                        $new_array[]=$final_array;
                    }
                    //$mydata = json_encode($new_array);
                    //just present the Weekly date range data - index 2 and 3 from new_array
                    $mydata = json_encode(array_slice($new_array, 2)); ;

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
                        //console.log(stackedData)
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
                                <thead>
                                <th>Metrics</th>
                                <th>Previous Month</th>
                                <th>Month</th>
                                <th>Previous Week</th>
                                <th>Week</th>
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
                <?php  }

                else {
                    echo "None of the pages for this Project have a Page feedback tool!";
                }
                ?>
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

                if ($pageFeedbackOnPages) {

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
                    //$my_www_data = json_encode($new_www_array);
                    //just present the Weekly date range data - index 2 and 3 from new_array
                    $my_www_data = json_encode(array_slice($new_www_array, 2));

                    $subgroups_www = json_encode(array("I can't find the info", "Other reason","Info is hard to understand","Error/something didn't work"));

                    //$groups_www = json_encode(array_unique($d3Data_DYFWYWLF_DateRanges));
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
                                <thead>
                                <th>Metrics</th>
                                <th>Previous Month</th>
                                <th>Month</th>
                                <th>Previous Week</th>
                                <th>Week</th>
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

                <?php  }

                else {
                    echo "None of the pages for this Project have a Page feedback tool!";
                }
                ?>
            </div>
        </div>
    </div>
</div>



<?php  //} //if (empty($tmp))  ?>
<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
