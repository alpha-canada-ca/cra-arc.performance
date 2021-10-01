
<?php include "./includes/upd_header.php"; ?>
<?php include "./includes/upd_sidebar.php"; ?>
<?php include "./includes/date-ranges.php"; ?>
<?php include "./includes/functions.php"; ?>
<?php ini_set('display_errors', 1);
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

<h1 class="visually-hidden">Usability Performance Dashboard</h1>

    <h2 class="h3 pt-2 pb-2" data-i18n="overview-title">Overview of CRA website</h2>

    <div class="tabs sticky">
      <ul>
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="./overview_summary.php" data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./overview_webtraffic.php" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="./overview_searchanalytics.php" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-pagefeedback">Page feedback</a></li>
        <li <?php if ($tab=="calldrivers") {echo "class='is-active'";} ?>><a href="./overview_calldrivers.php" data-i18n="tab-calldrivers">Call drivers</a></li>
        <li <?php if ($tab=="uxtests") {echo "class='is-active'";} ?>><a href="./overview_uxtests.php" data-i18n="tab-uxtests">UX tests</a></li>
      </ul>
    </div>

           <?php
require 'vendor/autoload.php';
use TANIOS\Airtable\Airtable;

// Adobe Analytics
$time = microtime(true);
$succ = 0;

if (!isset($_SESSION['CREATED']))
{
    $_SESSION['CREATED'] = time();
    require_once ('./php/getToken.php');
    $succ = 1;
}
else if (time() - $_SESSION['CREATED'] > 86400)
{
    session_regenerate_id(true);
    $_SESSION['CREATED'] = time();
    require_once ('./php/getToken.php');
    $succ = 1;
}
if (isset($_SESSION["token"]))
{
    $succ = 1;
}

if ($succ === 1)
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

?>

    <div class="row mb-4 mt-1">
      <div class="dropdown">
        <button type="button" class="btn bg-white border border-1 dropdown-toggle" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> Last week </button> <span class="text-secondary ps-2 text-nowrap"><?=$datesHeader[1][0] ?> to <?=$datesHeader[1][1] ?> compared to <?=$datesHeader[0][0] ?> to <?=$datesHeader[0][1] ?></span>

        <ul class="dropdown-menu" aria-labelledby="range-button" style="">
          <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
          <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
        </ul>

      </div>
    </div>



        <?php
        $urls = "";
        $url = "";
        if (substr($url, 0, 8) == "https://")
        {
            $urls = substr($url, 8, strlen($url));
        }
        else
        {
            $urls = $url;
        }

        $r = new ApiClient($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token']);

        $temp = ['aa-ovrvw-smmry-metrics', 'aa-ovrvw-smmry-fwylf', 'aa-ovrvw-smmry-trnd', 'aa-ovrvw-smmry-tsks']; //, 'fwylf' ];
        $result = array();
        $j = array();

        foreach ($temp as $t)
        {

            $json = $data[$t];
            $json = sprintf($json, $urls);

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
            //$result = api_post($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token'], $api);
            $result[] = $r->requestEntity($json);
            $j[] = $json;

        }

        //echo var_dump($result[0]);
        foreach ($result as $r)
        {

        }

        $res = json_decode($result[0], true);
        $metrics = $res["summaryData"]["filteredTotals"];

        $res2 = json_decode($result[1], true);
        $metrics2 = $res2["summaryData"]["filteredTotals"];

        $aaTasks = json_decode($result[2], true);
        $aaTasksStats = $aaTasks["rows"];

        $taskArray = array();
        foreach ($aaTasksStats as $task)
        {
            $taskArray[] = $task['value'];
        }

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

        // echo $monthStart."<br>";
        // echo $monthEnd."<br>";
        // echo $previousMonthStart."<br>";
        // echo $previousMonthEnd."<br>";

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

        $params = array(
            "filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')))",
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

        $allData = ( json_decode(json_encode($fullArray), true));//['records'];

        // if there's data (record exist)
        if ( count( $allData ) > 0 ) {
          // do things here
        }


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
              $fieldsByGroup = group_by('URL', $all_fields);
              $fieldsByGroupPW = group_by('URL', $all_fieldsPW);

              $fieldsByGroupService = group_by('Main section', $all_fields);
              $fieldsByGroupServicePW = group_by('Main section', $all_fieldsPW);

              //


              foreach ( $fieldsByGroupPW as &$item ) {
                $item["Total comments"] = count($item);
              }
              foreach ( $fieldsByGroup as &$item ) {
                $item["Total comments"] = count($item);
              }

              foreach ( $fieldsByGroupServicePW as &$item ) {
                $item["Total service comments"] = count($item);
              }
              foreach ( $fieldsByGroupService as &$item ) {
                $item["Total service comments"] = count($item);
              }
              //

              //echo count($fieldsByGroupPW);
              // echo "<br><br><pre>";
              // print_r($fieldsByGroupPW);
              // echo "</pre><br></br>";


              //uasort -keeps the key associations
              uasort($fieldsByGroup, function($b, $a) {
                   if ($a["Total comments"] == $b["Total comments"]) {
                       return 0;
                   }
                   return ($a["Total comments"] < $b["Total comments"]) ? -1 : 1;
               });

               uasort($fieldsByGroupService, function($b, $a) {
                    if ($a["Total service comments"] == $b["Total service comments"]) {
                        return 0;
                    }
                    return ($a["Total service comments"] < $b["Total service comments"]) ? -1 : 1;
                });

               $top5Pages = array_slice($fieldsByGroup, 0, 5);
               $top5Services = array_slice($fieldsByGroupService, 0, 5);



        } //if (( count( $WeeklyData ) > 0 ) && ( count( $PWeeklyData ) > 0 ))

        ?>

    <div class="row mb-4">
      <div class="col-lg-6 col-md-6">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">Did you find what you were looking for?</span></h3>
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
              <summary>View table data</summary>
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
          </div>
        </div>
      </div>
      <div class="col-lg-6 col-md-6">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">What was wrong?</span></h3>
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

                  console.log(data)
                  console.log(typeof data)
                  // List of subgroups = header of the csv files = soil condition here
                  //var subgroups = data.columns.slice(1)
                  //var subgroups = data.columns.slice(1)
                  var subgroups = <?=$subgroups_www?>;
                  console.log(subgroups)
                  console.log(typeof subgroups)

                  // List of groups = species here = value of the first column called group -> I show them on the X axis
                  //var groups = d3.map(data, function(d){return(d.group)}).keys()
                  var groups = <?=$groups_www?>;
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
              <summary>View table data</summary>
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
          </div>
        </div>
      </div>
    </div>

    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">Top 5 programs/services with the most feedback</span></h3>
            <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

              <?php

                   $qry = $top5Services;

                     if (count($qry) > 0) { ?>
                       <div class="table-responsive">
                         <table class="table table-striped dataTable no-footer">
                           <thead>
                             <tr>
                               <th>Program/service</th>
                               <th># of comments</th>
                               <th>Change</th>
                             </tr>
                           </thead>
                           <tbody>
                               <?php foreach ($qry as $row) { ?>
                                   <tr>
                                     <td><?=array_key_exists('Main section', $row[0]) ? $row[0]['Main section'] : "";?></td>
                                     <td><?=array_key_exists('Total service comments', $row) ? number_format($row['Total service comments']) : "";?></td>
                                     <?php

                                     if (array_key_exists($row[0]['Main section'], $fieldsByGroupServicePW)) {
                                         $diff = differ( $fieldsByGroupServicePW[$row[0]['Main section']]['Total service comments'], $row['Total service comments'] );
                                         $posi = posOrNeg2($diff);
                                         $pieces = explode(":", $posi);
                                         $diff = abs($diff);
                                     }
                                     else {
                                         $diff = 0;
                                         $pieces = explode(":", 'text-warning:');
                                     }
                                     ?>

                                     <td><span class="<?=$pieces[0]?>"><?=$pieces[1]?> <?=percent($diff)?></span></td>

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

    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Description of what this means" data-bs-original-title="" title="">Top 5 pages with the most feedback</span></h3>
            <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

               <?php

                  $qry = $top5Pages;

                    if (count($qry) > 0) { ?>
                      <div class="table-responsive">
                        <table class="table table-striped dataTable no-footer">
                          <thead>
                            <tr>
                              <th>Page</th>
                              <th># of comments</th>
                              <th>Change</th>
                            </tr>
                          </thead>
                          <tbody>
                              <?php foreach ($qry as $row) { ?>
                                  <tr>
                                    <td><a href="<?=array_key_exists('URL', $row[0]) ? $row[0]['URL'] : "#";?>" target="_blank"><?=array_key_exists('Lookup_page_title', $row[0]) ? $row[0]['Lookup_page_title'][0] : "";//=$row['Lookup_page_title'][0];?></a></td><?//=array_key_exists('Lookup_page_title', $row) ? $row['Lookup_page_title']) : "";?>
                                    <td><?=array_key_exists('Total comments', $row) ? number_format($row['Total comments']) : "";?></td>
                                    <?php
                                        if (array_key_exists($row[0]['URL'], $fieldsByGroupPW)){
                                            $diff = differ( $fieldsByGroupPW[$row[0]['URL']]['Total comments'], $row['Total comments'] );
                                            $posi = posOrNeg2($diff);
                                            $pieces = explode(":", $posi);
                                            $diff = abs($diff);
                                        }
                                        else {
                                            $diff = 0;
                                            $pieces = explode(":", 'text-warning:');
                                        }
                                    ?>

                                    <td><span class="<?=$pieces[0]?>"><?=$pieces[1]?> <?=percent($diff)?></span></td>

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


<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
