
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

//$url = $_REQUEST['url'];
$time = microtime(true);
//echo $url;

if (isset($_GET['url'])) {
$url = $_GET['url'];
}
else {
$url = "https://www.canada.ca/en/revenue-agency/services/benefits/recovery-benefit/crb-how-apply.html";
}

if (isset($_GET['dr'])) {
$dr = $_GET['dr'];
}
else {
$dr = "week";
}

if (isset($_GET['lang'])) {
$lang = $_GET['lang'];
}
else {
$lang = "en";
}

//$start = microtime(true);
// function getSiteOG( $url ){
//     $doc = new DOMDocument();
//     @$doc->loadHTML(file_get_contents($url));
//     //$res['title'] = $doc->getElementsByTagName('title')->item(0)->nodeValue;
//     $title = $doc->getElementsByTagName('title')->item(0)->nodeValue;
//
//     $pageTitle = trim(substr($title, 0, -12));
//     // foreach ($doc->getElementsByTagName('meta') as $m){
//     //     $tag = $m->getAttribute('name') ?: $m->getAttribute('property');
//     //     if(in_array($tag,['description','keywords']) || strpos($tag,'og:')===0) $res[str_replace('og:','',$tag)] = $m->getAttribute('content');
//     // }
//     return $pageTitle;
// }

// echo "<pre>";
// print_r(getSiteOG($url));
// echo "</pre>";

// $time_elapsed_secs = microtime(true) - $start;
// echo "<p>Time taken: " . number_format($time_elapsed_secs, 2) . " seconds</p>";


require 'vendor/autoload.php';
use TANIOS\Airtable\Airtable;

$config = include ('./php/config-at.php');

$airtable = new Airtable(array(
     'api_key'   => $config['tasks']['api_key'],
     'base'      => $config['tasks']['base'],
));

$sUrl = substr($url, 8, strlen($url));

$params =  array( "filterByFormula" => "( Url = '$sUrl' )" );
$table = "Pages";

$fullArray = [];


$request = $airtable->getContent($table, $params);
do
{
    $response = $request->getResponse();
    $fullArray = array_merge($fullArray, ($response->records));
}
while ($request = $response->next());

$weeklyRe = ( json_decode(json_encode($fullArray), true));//['records'];


// if there's data (record exist)
if ( count( $weeklyRe ) > 0 ) {
  //do things here
}
//get data with API
$fullArray = $weeklyRe;

// echo "AT url for tasks<pre>";
// print_r($fullArray);
// echo "</pre>";
//
// echo "AT url for tasks<pre>";
// print_r($fullArray[0]['fields']);
// echo "</pre>";


$relatedTasks = $fullArray[0]['fields']['Lookup_Tasks'];//['records'];
$relatedProjects = $fullArray[0]['fields']['Projects'];

// echo "Tasks<pre>";
// print_r($relatedTasks);
// echo "</pre>";


// echo "Projects<pre>";
// print_r($relatedProjects);
// echo "</pre>";

// if ($relatedProjects == null) {echo "null projects";}
// else {echo $relatedProjects; }


?>



<h1 class="visually-hidden">Usability Performance Dashboard</h1>
<div class="back_link"><span class="material-icons align-top">west</span> <a href="./pages_home.php" alt="Back to Pages home page">Pages</a></div>

      <h2 class="h3 pt-2 pb-2" data-i18n=""><?=getSiteTitle($url)?></h2>
      <p data-i18n="" class="page_url hidden"><?=$url?></p>

      <script type="text/javascript">
            function copy_to_clipboard() {
              navigator.clipboard.writeText('<?=$url?>');
            }
      </script>

      <div class="page_header back_link">
          <span id="page_project">
            <?php if ($relatedProjects != null) { ?>
                <span class="material-icons align-top">folder</span>
                    <?php
                      if (is_array($relatedProjects)) {
                          echo implode(", ",$relatedProjects);
                      }
                      else {
                        echo $relatedProjects;
                      }
                    ?>
               </span>
            <?php } ?>
            <span id="view_url"><span class="material-icons align-top">link</span> View URL </span>
            <span id="copy_url" onclick="copy_to_clipboard()"><span class="material-icons align-top">content_copy</span> Copy URL</span>
      </div>

    <div class="tabs sticky">
      <ul>
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="./pages_summary.php?url=<?=$url?>" data-i18n="tab-summary">Summary</a> </li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="./pages_searchanalytics.php?url=<?=$url?>" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="./pages_pagefeedback.php?url=<?=$url?>" data-i18n="tab-pagefeedback">Page feedback</a></li>
      </ul>
    </div>

           <?php
// require 'vendor/autoload.php';
// use TANIOS\Airtable\Airtable;

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
    require_once ('./php/get_aa_data.php');
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



        <?php
        $urls = "";
        //$url = "";
        if (substr($url, 0, 8) == "https://")
        {
            $urls = substr($url, 8, strlen($url));
        }
        else
        {
            $urls = $url;
        }

        $r = new ApiClient($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token']);

        $temp = ['aa-pages-smmry-trnd', 'metrics-new-pages', 'prvs', 'activityMap2' ]; //, 'fwylf' ];
        //$temp = ['aa-pages-smmry-metrics', 'aa-pages-smmry-fwylf', 'aa-pages-smmry-trnd', 'aa-ovrvw-smmry-tsks', 'prvs']; //, 'fwylf' ];
        $result = array();
        $j = array();

        $tt = -1;
        //$start1 = microtime(true);
        foreach ($temp as $t)
        {

            $json = $data[$t];

            if ($t == "activityMap2") {
                $pgTitle = getSiteTitle($url);
                //  echo $pgTitle;
                $json = sprintf($json, $pgTitle);
                //  print_r($json);
            }
            else {
                $json = sprintf($json, $urls);
            }

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

            //echo ++$tt;
            //$time_elapsed_secs1 = microtime(true) - $start1;
            //echo "<p>Time for query ".++$tt." taken: " . number_format($time_elapsed_secs1 , 2) . " seconds</p>";
            // echo "<pre>";
            // print_r($json);
            // echo "</pre><br>";

            //$result = api_post($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token'], $api);
//            $result[] = $r->requestEntity($json);
            $result[] = get_aa_data($json, $r);
            $j[] = $json;

            // $time_elapsed_secs = microtime(true) - $time;
            // echo "<p>Time taken: " . number_format($time_elapsed_secs, 2) . " seconds</p>";

        }

        //echo var_dump($result[0]);
        foreach ($result as $r)
        {

        }

        //$res = json_decode($result[0], true);
        // $metrics = $res["summaryData"]["filteredTotals"];

        // $res2 = json_decode($result[1], true);
        // $metrics2 = $res2["summaryData"]["filteredTotals"];

        $aaResultTrend = json_decode($result[0], true);
        $aaMetricsTrend = $aaResultTrend["rows"];

        $weeks_index = date("M j, Y", strtotime($previousWeekStart));
        //echo $weeks_index;
        $value_date = array_column($aaMetricsTrend, 'value');
        $index_key = array_search($weeks_index, $value_date);

        //$aaTrendWeeks = array_slice($aaMetricsTrend, -14);
        $aaTrendWeeks = array_slice($aaMetricsTrend, $index_key, 14);
        $aaTrendLastWeek = array_slice($aaTrendWeeks, 0, 7);
        $aaTrendWeek = array_slice($aaTrendWeeks, -7);

        // $aaTasks = json_decode($result[2], true);
        // $aaTasksStats = $aaTasks["rows"];
        //
        // $taskArray = array();
        // foreach ($aaTasksStats as $task)
        // {
        //     $taskArray[] = $task['value'];
        // }

        $fwylfYes = 0;
        $fwylfNo = 4;
        $pv = 8;
        $visitors = 12;
        $visits = 16;

        // function differ($old, $new)
        // {
        //     return (($new - $old) / $old);
        // }

        function differ($old, $new)
        {
            if ($old == 0) {
              $dif = $new;
            }
            else {
              $dif = (($new - $old) / $old);
            }
              return $dif;
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

        // function percent($num)
        // {
        //     if ($num==0) {
        //       $result = 0;
        //     }
        //     else {
        //       $result = round($num * 100, 0);
        //     }
        //     return $result . '%';
        // }



        $fwylfICantFindTheInfo = 0;
        $fwylfOtherReason = 4;
        $fwylfInfoHardToUnderstand = 8;
        $fwylfError = 12;


        //
        $metricsNew = json_decode($result[1], true);
        $deviceTypeAndAvgTime = $metricsNew["summaryData"]["filteredTotals"];
        //$whatWasClicked = $activityMap["rows"];
        // echo "<pre>";
        // print_r($deviceTypeAndAvgTime);
        // echo "</pre>";

        $deviceDesktop = 0;
        $deviceMobile = 4;
        $deviceTablet = 8;
        $deviceOther = 12;
        $avgTimeOnPage = 16;


        $pp = json_decode($result[2], true);
        $prevPages = $pp["rows"];
        //$whatWasClicked = $activityMap["rows"];
        // echo "<pre>";
        // print_r($prevPages);
        // echo "</pre>";
        //
        $am = json_decode($result[3], true);
        $activityMap = $am["rows"];
        // echo "<pre>";
        // print_r($activityMap);
        // echo "</pre>";




        ?>

          <?php
                // ***************************************************************************
                // AIRTABLE API calls for Call drivers data for TOTAL CALLS BY INQURY LINE
                // ***************************************************************************


                $lastMonthAndYear = date('F Y', strtotime($monthStartHeader));
                $twoMonthsAgoAndYear = date('F Y', strtotime($previousMonthStartHeader));

                $s = $startLastGSC;
                $e = $endLastGSC;
                $s1 = $startGSC;
                $e1 = $endGSC;

                $weeklyMonthRanges = getMonthRanges($s, $e1);


              ?>


    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Number of page visits breakdown (bar chart) relation over selected Date ranges, compared to the Call volume (line chart) - the total number of calls in the Calls centre for the same date ranges." data-bs-original-title="" title="" data-i18n="">Visits by day</span></h3>
            <div class="card-body pt-2" id="d3_visits"></div>
            <div id="d3_www_legend"></div><div id="d3_www_legend4"></div>
              <!-- Total calls by Enquiry_line D3 bar chart -->
              <?php

                $s = $startLastGSC;
                $e = $endLastGSC;
                $s1 = $startGSC;
                $e1 = $endGSC;

                //echo $s;
                //echo gettype($e1);

                $s = date("M d", strtotime($s));
                $e = date("M d", strtotime($e));
                $s1 = date("M d", strtotime($s1));
                $e1 = date("M d", strtotime($e1));




                $days = array('Sunday', 'Monday', 'Tuesday', 'Wednesday','Thursday','Friday', 'Saturday');
                $d3DateRanges = array($s.'-'.$e,$s1.'-'.$e1); // previous $a1
                //$dates = [[$startLastGSC, $endLastGSC], [$startGSC, $endGSC]];
                //$dataPW = $aaTrendLastWeek;
                //$dataW = $aaTrendWeek;

                for ($i = 0; $i < 7; ++$i) {
                  $final_array1["day"] = $days[$i];
                  $final_array1[$d3DateRanges[0]] = $aaTrendLastWeek[$i]['data'][1];
                  $final_array1[$d3DateRanges[1]] = $aaTrendWeek[$i]['data'][1];
                  $data_array[]=$final_array1;
                }

                //$s = $startLastGSC;
                //$e = $endLastGSC;
                //$s1 = $startGSC;
                //$e1 = $endGSC;
                //For loop for the call volume data

                $pwDays = [];
                $wDays = [];

                for ($i = 0; $i < 7; ++$i) {
                  $pwDays[] = date("Y-m-d", strtotime($startLastGSC. " +".$i."days"));
                  $wDays[] = date("Y-m-d", strtotime($startGSC. " +".$i."days"));
                }


                for ($i = 0; $i < 7; ++$i) {
                  $final_array2["day"] = $days[$i];
                  // get the key from the fieldsByGroupDate and fieldsByGroupDatePW arrays
                  // convert the value to
                  // $fieldsByGroupDatePW
                  // $fieldsByGroupDate

                  // foreach ($fieldsByGroupDatePW as $trend)
                  // {
                  if (array_key_exists($pwDays[$i], $fieldsByGroupDatePW)){
                        $final_array2[$d3DateRanges[0]] = $fieldsByGroupDatePW[$pwDays[$i]]['Total calls per day'];
                  }
                  else {
                        $final_array2[$d3DateRanges[0]] = 0;
                  }
                  if (array_key_exists($wDays[$i], $fieldsByGroupDate)){
                        $final_array2[$d3DateRanges[1]] = $fieldsByGroupDate[$wDays[$i]]['Total calls per day'];
                  }
                  else {
                        $final_array2[$d3DateRanges[1]] = 0;
                  }


                  //$final_array2[$d3DateRanges[1]] = $fieldsByGroupDate['Total calls per day'];

                  $data_array2[]=$final_array2;
                }

                // echo count($data_array2);
                // echo "<pre>";
                // print_r($data_array2);
                // echo "</pre>";



                $subgroups = json_encode($d3DateRanges);

                $groups = json_encode($days);

                $mydata = json_encode($data_array);

                $mydataCalls = json_encode($data_array2);

                // echo "<pre>";
                // print_r($data_array2);
                // echo "</pre>";

                ?>
                <script>
                //
                // set the dimensions and margins of the graph
                width = parseInt(d3.select('#d3_visits').style('width'), 10)
                height = width / 3;
                //alert("hellp");
                var margin = {top: 10, right: 30, bottom: 30, left: 100},
                    width = width - margin.left - margin.right,
                    height = height - margin.top - margin.bottom,
                    legendHeight = 0,
                    //dualaxisWidth = 120;
                    dualaxisWidth = 0;


                // append the svg object to the body of the page
                var svg1 = d3.select("#d3_visits")
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
                  console.log(subgroups);
                  console.log(subgroups[0]);
                  //console.log(typeof subgroups)

                  // List of groups = species here = value of the first column called group -> I show them on the X axis
                  //var groups = d3.map(data, function(d){return(d.group)}).keys()
                  var groups = <?=$groups?>;
                  //console.log(groups)
                  //console.log(typeof groups)

                  // Add X axis
                  var x = d3.scaleBand()
                      .domain(groups)
                      .range([0, width-dualaxisWidth])
                      .padding([0.4]);
                  svg1.append("g")
                    .attr("transform", "translate(0," + height + ")")
                    .call(d3.axisBottom(x).tickSizeOuter(0));

                  // get the max value from the data json object for the y axis domain (Number of visits)
                  var max1 = d3.max(data, function(d){ return d3.max(d3.values(d).filter(function(d1){ return !isNaN(d1)}))});
                  console.log(max1);
                  var num_digits1 = Math.floor(Math.log10(max1)) + 1;
                  console.log(num_digits1);
                  console.log(Math.ceil(max1/Math.pow(10,num_digits1-1))*Math.pow(10,num_digits1-1));

                  // Add Y axis
                  var y = d3.scaleLinear()
                    .domain([0, Math.ceil(max1/Math.pow(10,num_digits1-1))*Math.pow(10,num_digits1-1)])
                    .range([ height, 0 ]);

                  // grid lines on Y axis
                  var yGrid = d3.axisLeft(y).tickSize(-(width-dualaxisWidth)).tickFormat('').ticks(5);

                  // Another scale for subgroup position?
                  var xSubgroup = d3.scaleBand()
                    .domain(subgroups)
                    .range([0, x.bandwidth()])
                    .padding([0.1]);

                  //create  yGrid
                  svg1.append('g')
                    .attr('class', 'axis-grid')
                    .call(yGrid);

                  //create Y axis
                  svg1.append("g")
                    .call(d3.axisLeft(y).ticks(5));

                  // color palette = one color per subgroup
                  var color = d3.scaleOrdinal()
                    .domain(subgroups)
                    .range(['#B6C2CB','#345EA5']);

                    // Show the bars
                  svg1.append("g")
                      .selectAll("g")
                      // Enter in data = loop group per group
                      .data(data)
                      .enter()
                      .append("g")
                        .attr("transform", function(d) { return "translate(" + x(d.day) + ",0)"; })
                      .selectAll("rect")
                      .data(function(d) { return subgroups.map(function(key) { return {key: key, value: d[key]}; }); })
                      .enter().append("rect")
                        .attr("x", function(d) { return xSubgroup(d.key); })
                        .attr("y", function(d) { return y(d.value); })
                        .attr("width", xSubgroup.bandwidth())
                        .attr("height", function(d) { return height - y(d.value); })
                        .attr("fill", function(d) { return color(d.key); });

                  svg1.selectAll(".tick text")
                       .style("font-size","14px")
                       .style("fill","#666");




                  var legend = d3.select('#d3_www_legend').selectAll("legend")
                      .data(subgroups);

                  var legend_cells = legend.enter().append("div")
                    .attr("class","legend");

                  var p1 = legend_cells.append("p").attr("class","legend_field");
                  p1.append("span").attr("class","legend_color").style("background",function(d,i) { return color(i) } );
                  p1.insert("text").text(function(d,i) { return d } );



                  // text label for the y axis
                  svg1.append("text")
                      .attr("transform", "rotate(-90)")
                      .attr("y",0 - margin.left)
                      .attr("x",0 - (height / 2))
                      .attr("dy", "1em")
                      .style("text-anchor", "middle")
                      .text("Visits");




                </script>
                 <details class="details-chart">
                      <summary data-i18n="view-data-table">View table data</summary>
                      <div class="table-responsive">
                          <table class="table">
                            <caption><!--Last Week--></caption>
                            <thead>
                              <th data-i18n="date">Date (<?=$d3DateRanges[0]?>)</th>
                              <th data-i18n="visits">Visits</th>
                            </thead>
                            <tbody>

                              <?php
                                  //foreach ($aaTrendLastWeek as $trend)
                                  foreach ($aaTrendLastWeek as $key=>$value)
                                  {

                                  ?>

                                          <tr>
                                            <td><?=$value['value'] ?></td>
                                            <td><?=number_format($value['data'][1]) ?></td>
                                          </tr>

                                          <?php
                                  }

                                  ?>


                            </tbody>
                          </table>

                          <table class="table">
                            <caption><!--Week--></caption>
                            <thead>
                              <th data-i18n="date">Date (<?=$d3DateRanges[1]?>)</th>
                              <th data-i18n="visits">Visits</th>
                            </thead>
                            <tbody>

                              <?php
                                  //foreach ($aaTrendWeek as $trend)
                                  foreach ($aaTrendWeek as $key=>$value)
                                  {

                                  ?>

                                          <tr>
                                            <td><?=$value['value'] ?></td>
                                            <td><?=number_format($value['data'][1]) ?></td>
                                          </tr>

                                          <?php
                                  }

                                  ?>

                            </tbody>
                          </table>

                </details>
              </div>
            </div>
          </div>
        </div>

        <div class="row mb-4">
          <div class="col-lg-12 col-md-12">
            <div class="card">
              <div class="card-body pt-2">
                <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Where visitors came from" data-bs-original-title="" title="" data-i18n="">Where visitors came from</span></h3>
                <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

                  <?php
                      uasort($prevPages, function($b, $a) {
                         if ($a["data"][3] == $b["data"][3]) {
                             return 0;
                         }
                         return ($a["data"][3] < $b["data"][3]) ? -1 : 1;
                       });

                       $top15prevPages = array_slice($prevPages, 0, 15);
                       //$top5Decrease = array_reverse(array_slice($fieldsByGroup, -5));
                       $qry = $top15prevPages;

                       if (count($qry) > 0) { ?>
                         <div class="table-responsive">
                           <table class="table table-striped dataTable no-footer" data="" role="grid" id="toptask">
                             <thead>
                               <tr>
                                 <th class="sorting" aria-controls="toptask" aria-label="Topic: activate to sort column" data-i18n="">Previous page URL</th>
                                 <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Visits</th>
                                 <!-- <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Prev Week Calls</th> -->
                                 <th data-i18n="change">Change</th>
                               </tr>
                             </thead>
                             <tbody>
                           <?php foreach ($qry as $row) {
                             // echo "---<pre>";
                             // print_r($row);
                             // echo "</pre>";

                             ?>
                               <tr>
                                 <td><?=$row['value']?></td>
                                 <td><span><?=number_format($row['data'][3])?></span></td>
                                 <?php
                                 $diff = differ($row['data'][2], $row['data'][3] );
                                 $posi = posOrNeg2($diff);
                                 $pieces = explode(":", $posi);
                                 $diff = abs($diff);
                                 ?>
                                 <!-- <td><span>--><?//=$fieldsByGroupPW[$row[0]['Topic']]['Total topic calls'];?><!--</span></td> -->
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
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">What visitors clicked on</span></h3>
            <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

              <?php
                  uasort($activityMap, function($b, $a) {
                     if ($a["data"][3] == $b["data"][3]) {
                         return 0;
                     }
                     return ($a["data"][3] < $b["data"][3]) ? -1 : 1;
                   });

                   //$top15prevPages = array_slice($prevPages, 0, 15);
                   //$top5Decrease = array_reverse(array_slice($fieldsByGroup, -5));
                   $qry = $activityMap;

                   if (count($qry) > 0) { ?>
                     <div class="table-responsive">
                       <table class="table table-striped dataTable no-footer" data="" role="grid" id="toptask">
                         <thead>
                           <tr>
                             <th class="sorting" aria-controls="toptask" aria-label="Topic: activate to sort column" data-i18n="">Text</th>
                             <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Clicks</th>
                             <!-- <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Prev Week Calls</th> -->
                             <th data-i18n="change">Change</th>
                           </tr>
                         </thead>
                         <tbody>
                       <?php foreach ($qry as $row) {
                         // echo "---<pre>";
                         // print_r($row);
                         // echo "</pre>";

                         ?>
                           <tr>
                             <td><?=$row['value']?></td>
                             <td><span><?=number_format($row['data'][3])?></span></td>
                             <?php
                             $diff = differ($row['data'][2], $row['data'][3] );
                             $posi = posOrNeg2($diff);
                             $pieces = explode(":", $posi);
                             $diff = abs($diff);
                             ?>
                             <!-- <td><span>--><?//=$fieldsByGroupPW[$row[0]['Topic']]['Total topic calls'];?><!--</span></td> -->
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
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Visitor location</span></h3>
            <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

            </div></div><div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div></div>
          </div>
        </div>
      </div>
    </div>

    <!-- <div class="row mb-3 gx-3">
      <h4>UX Tests</h4>
    </div> -->

    <?php
    // $time_elapsed_secs = microtime(true) - $time;
    // echo "<p>Time taken: " . number_format($time_elapsed_secs, 2) . " seconds</p>";
    ?>


<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
