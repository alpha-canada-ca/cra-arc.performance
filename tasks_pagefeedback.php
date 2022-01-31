
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
require_once ('./php/get_aa_data.php');
use TANIOS\Airtable\Airtable;

include_once "php/lib/sqlite/DataInterface.php";
include_once 'php/Utils/Date.php';

use Utils\DateUtils;

//$startTime = microtime(true);

$taskId = $_GET['taskId'] ?? "recXkhR8zOWnR83TU";

$dr = $_GET['dr'] ?? "week";

$lang = $_GET['lang'] ?? "en";

$db = new DataInterface();
$taskData = $db->getTaskById($taskId)[0];

$taskPages = $db->getPagesByTaskId($taskId, ['Url']);
$taskPages = array_column($taskPages, 'Url');

$taskProjects = $db->getProjectsByTaskId($taskId, ['title']);

$relatedProjects = array_column($taskProjects, 'title');

$dateUtils = new DateUtils();

$weeklyDatesHeader = $dateUtils->getWeeklyDates('header');
?>

<h1 class="visually-hidden">Usability Performance Dashboard</h1>
<div class="back_link"><span class="material-icons align-top">west</span> <a href="./tasks_home.php" alt="Back to Tasks home page">Tasks</a></div>

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
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./tasks_webtraffic.php?taskId=<?=$taskId?>" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="./tasks_searchanalytics.php?taskId=<?=$taskId?>" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-pagefeedback">Page feedback</a></li>
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

    foreach ($taskPages as $page)
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


    // AIRTABLE CONNECTION - SETUP REUQEST AND PARSE RESPONSE
    //--------------------------------------------------------------
    $s = $startLastGSC;
    $e = $endLastGSC;
    $s1 = $startGSC;
    $e1 = $endGSC;

    $config = include ('./php/config-at.php');
    $airtable = new Airtable($config['feedback']);

    // -----------------------------------------------------------------------------------------------
    // GET DATA FROM "Page Feedback" (CRA view) table filtered by date range - last two weekStart
    // -----------------------------------------------------------------------------------------------

    $listOfPages = array_map(fn($url) => "(URL = 'https://$url')", $taskPages);

    $paramPages = implode(",", $listOfPages);

    //echo $url;
    $params = array(
        // for get multiple url's or Projects from Airtable listOfPages
        "filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')), OR($paramPages))",
        "view" => "CRA"
    );
    $table = 'Page feedback';

    $fullArray = [];
    $request = $airtable->getContent($table, $params);
    do
    {
        $response = $request->getResponse();
        $fullArray = array_merge($fullArray, ($response->records));
    }
    while ($request = $response->next());

    $allData = ( json_decode(json_encode($fullArray), true));

    $all_fields = array();

    // if there's data (record exist)
    if ( count( $allData ) > 0 ) {
        $re = $allData;

        //weekly data range
        $rangeStartW = strtotime($s1);
        $rangeEndW = strtotime($e1);
        //previous week range
        $rangeStartPW = strtotime($s);
        $rangeEndPW = strtotime($e);

        //filter array by date ranges
        $WeeklyData = array_filter( $re, function($var) use ($rangeStartW, $rangeEndW) {
            $utime = strtotime($var['fields']['Date']);
            return $utime <= $rangeEndW && $utime >= $rangeStartW;
        });

        $PWeeklyData = array_filter( $re, function($var) use ($rangeStartPW, $rangeEndPW) {
            $utime = strtotime($var['fields']['Date']);
            return $utime <= $rangeEndPW && $utime >= $rangeStartPW;
        });

        if (( count( $WeeklyData ) > 0 ) && ( count( $PWeeklyData ) > 0 )) {

            // Get just the ['fields'] array of each record -  as a separate array - $all_fields
            $all_fields = array_column_recursive($WeeklyData, 'fields');
            $all_fieldsPW = array_column_recursive($PWeeklyData, 'fields');

            //we are grouping the pages by URL instead of Page Title, cause some pages might not have titles listes in the table
            //stil, the main idea is to group the pages by some unique page element

            foreach ( $all_fields as &$item ) {
                $item["Tag"] = implode($item['Lookup_tags']);
            }

            foreach ( $all_fieldsPW as &$item ) {
                $item["Tag"] = implode($item['Lookup_tags']);
            }

            $fieldsByGroupTag = group_by('Tag', $all_fields);
            $fieldsByGroupTagPW = group_by('Tag', $all_fieldsPW);

            foreach ( $fieldsByGroupTagPW as &$item ) {
                $item["Total tag comments"] = count($item);
            }
            foreach ( $fieldsByGroupTag as &$item ) {
                $item["Total tag comments"] = count($item);
            }

            $d3TotalFeedbackByPageSuccess = 1;

        }
    } else {
        $d3TotalFeedbackByPageSuccess = 0;
    }

    ?>

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

    <div class="row mb-4">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body">
                    <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Total feedback by page</span></h3>

                    <div id="d3_tcbil"></div>
                    <div id="d3_www_legend3"></div>
                    <!-- Total calls by Enquiry_line D3 bar chart  -->


                    <?php
                    //REMOVE AFTER tESTING
                    //$d3TotalFeedbackByPageSuccess = 1;
                    ?>


                    <?php
                    if ( $d3TotalFeedbackByPageSuccess == 0 ) { ?>

                        <div class="no-data">
                            <p>No Page feedback comments/tags for selected date range</p>
                        </div>

                    <?php
                    }
                    else {

                    //USE THIS - THESE ARE WELL DEFINED DATE RANGES
                    //-------------------------------------------------------
                    //$d3DateRanges = array($datesHeaderMonth[0][0].'-'.$datesHeaderMonth[0][1],$datesHeaderMonth[1][0].'-'.$datesHeaderMonth[1][1],$datesHeader[0][0].'-'.$datesHeader[0][1],$datesHeader[1][0].'-'.$datesHeader[1][1]); // previous $a1
                    //$groups = json_encode(array($d3DateRanges[2],$d3DateRanges[3]));
                    //----------------------------------------------------


                    $s = date("M d", strtotime($s));
                    $e = date("M d", strtotime($e));
                    $s1 = date("M d", strtotime($s1));
                    $e1 = date("M d", strtotime($e1));

                    $d3DateRanges = array($s.'-'.$e,$s1.'-'.$e1); // previous $a1

                    $groups = json_encode($d3DateRanges);

                    //echo count($d3DateRanges);
                    //echo count($groups[0]);

                    $el = array_values(array_unique(array_column_recursive($fieldsByGroupTag, "Tag")));
                    $subgroups = json_encode($el);

                    // echo "--------------------------------------<br><br><pre>";
                    // print_r($el); //Prev WEEK
                    // echo count($el);
                    // echo "</pre><br></br>";

                    /// ---------------------
                    /// MAKE SURE WE ADD THE DATA IN THE RIGHT DATE RANGE - AND TRIPLE CHECK THE RESULTS WITH THE ACTUAL DATA IN THE WEKLY AND PWEEKLY VARIABLES
                    /// -----------------------

                    for ($i = 0; $i < 2; ++$i) {
                        $final_tag_array["dateRange"] = $d3DateRanges[$i];
                        if ($i==0) {
                            for ($k = 0; $k < count($el); ++$k) {
                                $final_tag_array[$el[$k]] = $fieldsByGroupTagPW[$el[$k]]["Total tag comments"] ?? 0;
                            }
                        }
                        else {
                            for ($k = 0; $k < count($el); ++$k) {
                                $final_tag_array[$el[$k]] = $fieldsByGroupTag[$el[$k]]["Total tag comments"] ?? 0;
                            }
                        }
                        // $final_array["No"] = $d3Data[$i+4];
                        $d3_data[]=$final_tag_array;
                    }
                    //$mydata = json_encode($new_array);
                    //just present the Weekly date range data - index 2 and 3 from new_array
                    // echo "--------------------------------------<br><br><pre>";
                    // print_r($d3_data_w); //Prev WEEK
                    // echo "</pre><br></br>";
                    //$mydata = json_encode(array_slice($new_array, 2));
                    $mydata_tags = json_encode($d3_data);






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
                            var svg2 = d3.select("#d3_tcbil")
                                .append("svg")
                                .attr("width", width + margin.left + margin.right)
                                .attr("height", height + margin.top + margin.bottom + legendHeight)
                                .append("g")
                                .attr("transform",
                                    "translate(" + margin.left + "," + margin.top + ")");

                            // Parse the Data
                            var data = <?=$mydata_tags?>;

                            console.log("page feedback tags data:")
                            console.log(data)
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
                            svg2.append("g")
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
                                //.domain([0, max])
                                .range([ height, 0 ]);

                            // grid lines on Y axis
                            var yGrid = d3.axisLeft(y).tickSize(-width).tickFormat('').ticks(5);

                            // Another scale for subgroup position?
                            var xSubgroup = d3.scaleBand()
                                .domain(subgroups)
                                .range([0, x.bandwidth()])
                                .padding([0.1]);

                            //create  yGrid
                            svg2.append('g')
                                .attr('class', 'axis-grid')
                                .call(yGrid);

                            //create Y axis
                            svg2.append("g")
                                .call(d3.axisLeft(y).ticks(5));

                            // color palette = one color per subgroup
                            //-------------------------------------------
                            // NOTE: we need to automate the color scheme for these charts
                            // we don't know the number of categories are there, so we need to have
                            // a long list of all colors and then create a "color" variable
                            // that is an array as a slice (the number of categories (subgroups) are in the data) of the long color list
                            //--------------------------------------------------------------------------------
                            var color = d3.scaleOrdinal()
                                .domain(subgroups)
                                //.range(['#345EA5','#6CB5F3','#36A69A','#F8C040','#3EE9B7','#F17F2B']);
                                .range(['#345EA5','#6CB5F3','#36A69A','#F8C040','#3EE9B7']);

                            // Show the bars
                            svg2.append("g")
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

                            svg2.selectAll(".tick text")
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

                            var legend = d3.select('#d3_www_legend3').selectAll("legend")
                                .data(subgroups);

                            var legend_cells = legend.enter().append("div")
                                .attr("class","legend");

                            var p1 = legend_cells.append("p").attr("class","legend_field");
                            p1.append("span").attr("class","legend_color").style("background",function(d,i) { return color(i) } );
                            p1.insert("text").text(function(d,i) { return d } );

                            // text label for the y axis
                            svg2.append("text")
                                .attr("transform", "rotate(-90)")
                                .attr("y", 0 - margin.left)
                                .attr("x",0 - (height / 2))
                                .attr("dy", "1em")
                                .style("text-anchor", "middle")
                                .text("Number of comments");

                        </script>
                        <details class="details-chart">
                            <summary data-i18n="view-data-table">View table data</summary>
                            <div class="table-responsive">
                                <table class="table">
                                  <caption></caption>
                                    <thead>
                                    <th data-i18n="" scope="col">Feedback tags</th>
                                    <!-- <th>Previous Month</th>
                                    <th>Month</th> -->
                                    <th scope="col">Number of calls for <?=$d3DateRanges[0]?><!--two weeks ago--></th>
                                    <th scope="col">Number of calls for <?=$d3DateRanges[1]?><!--last week--></th>
                                    </thead>
                                    <tbody>

                                    <?php foreach ($el as $row) { ?>
                                        <tr>
                                            <td><?=$row?></td>
                                            <td><?=number_format($fieldsByGroupTagPW[$row]["Total tag comments"] ?? 0) ?></td>
                                            <td><?=number_format($fieldsByGroupTag[$row]["Total tag comments"] ?? 0) ?></td>
                                        </tr>
                                    <?php } ?>

                                    </tbody>
                                </table>
                            </div>
                        </details>

                    <?php } //else ($d3TotalFeedbackByPageSuccess == 0)  ?>

                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-lg-12 col-md-12">
            <div class="card">
                <div class="card-body pt-2">
                    <h3 class="card-title"><span class="h6" data-i18n="">Feedback by tags</span></h3>
                    <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

                                <?php

                                $qry = $all_fields;

                                if (count($qry) > 0) { ?>
                                    <div class="table-responsive">

                                        <div class="category-filter">

                                            <!-- ***************************************************************************************************
                                                 NOTE: build the dropdown programmatically from the list of all categories in the data
                                                 And we can add the number of items in each category in the brackets - for example - Contact (23)
                                                 *************************************************************************************************** -->
                                            <select id="categoryFilter" class="form-control form-select form-select-sm">
                                                <option value="">Show All (<?=count($all_fields)?>)</option>
                                                <?php
                                                $cat = $fieldsByGroupTag;

                                                foreach ($cat as $row) { ?>
                                                    <option value="<?=trim($row[0]['Tag'])?>"><?=trim($row[0]['Tag'])?> (<?=$row['Total tag comments']?>)</option>
                                                <?php } ?>

                                                <!-- <option value="">Show All</option>
                                                <option value="Eligibility">Eligibility</option>
                                                <option value="Applying / reapplying">Applying / reapplying</option>
                                                <option value="Page issue (e.g broken / link button / missing content)">Page issue (e.g broken / link button / missing content)</option>
                                                <option value="Accounts">Accounts</option>
                                                <option value="Contact">Contact</option> -->
                                            </select>
                                        </div>



                                        <!-- <div class="category-filter">

                                         <select id="categoryFilter" class="form-control form-select form-select-sm">
                                             <option value="">Show All</option>
                                             <option value="Eligibility">Eligibility</option>
                                             <option value="Applying / reapplying">Applying / reapplying</option>
                                             <option value="Page issue (e.g broken / link button / missing content)">Page issue (e.g broken / link button / missing content)</option>
                                             <option value="Accounts">Accounts</option>
                                             <option value="Contact">Contact</option>
                                           </select>
                                         </div> -->


                                        <table class="table table-striped dataTable no-footer" id="pages_dt2_filter">
                                          <caption>Feedback by tags</caption>
                                            <thead>
                                            <tr>
                                                <th data-i18n="date" scope="col">Date</th>
                                                <th data-i18n="" scope="col">Category</th>
                                                <th data-i18n="" scope="col">What was wrong</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php foreach ($qry as $row) { ?>
                                                <tr>
                                                    <td><?=array_key_exists('Date', $row) ? $row['Date'] : "";?></td>
                                                    <td><?=array_key_exists('Tag', $row) ? $row['Tag'] : "";?></td>
                                                    <td><?=array_key_exists("What's wrong", $row) ? $row["What's wrong"] : "";?></td>
                                                </tr>
                                            <?php } ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php }
                                else { ?>

                                    <div class="no-data">
                                        <p>No Page feedback comments/tags for selected date range</p>
                                    </div>

                                    <?php
                                }
                                ?>

                            </div></div><div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div></div>
                </div>
            </div>
        </div>
    </div>
<?php } ?>
<?php
//$endTime = microtime(true);

//$timeElapsed = round($endTime - $startTime, 2);

//echo "Page loaded in: $timeElapsed seconds";
?>
<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
