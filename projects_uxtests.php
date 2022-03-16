
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

function metKPI($old, $new)
{
    if (($new > 0.8) || (($new-$old)>0.2))  return 'text-success:check_circle:Met';
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
//$taskTests = $db->getUxTestsByTaskId($taskId, $uxTestSelectedFields);
//$relatedUxTests = array_column($projectTests, "Success Rate");

// echo "<pre>";
// print_r($projectTests);
// echo "</pre>";
//$relatedUxTests = array_column($taskTests, 'title');


// Add Task for each UX Test
foreach ($projectTests as $key => $value) {
    $tsk = $db->getTaskByUxTestId($value["id"],["Task"]);
    $relatedTsk = array_column($tsk, "Task");
    $projectTests[$key]["Task"] = $relatedTsk[0];
    // echo $test["Test title"]."<br>";
    // echo $;
}

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

// -------------------------------------------------------------------------------------------------------------------
//GET the list of DUPLICATE SUCCESS RATE values - so we map them correctly on the D3 chart (so they do not overlap (dots))
// -------------------------------------------------------------------------------------------------------------------
$arr = array_column_recursive($prjData,"Success Rate");

$duplicateRates = array_values(array_intersect($arr, array_unique(array_diff_key($arr, array_unique($arr)))));
function times100($n)
{
    return ($n * 100);
}

$duplicateRates = array_map('times100', $duplicateRates);
// -------------------------------------------------------------------------------------------------------------------


// echo "<pre>";
// print_r($duplicateRates);
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
    }

    $avgTaskSuccess = (array_sum(array_column_recursive($latestTest, "Success Rate")))/(count($latestTest));
    $avgCmpTaskSuccess = $avgTaskSuccess;

}



//-------------------------------------------------------------------







?>

<h1 class="visually-hidden">Usability Performance Dashboard</h1>
<div class="back_link"><span class="material-icons align-top">west</span> <a href="./projects_home.php" alt="Back to Projects home page">Projects</a></div>

<h2 class="h3 pt-2 pb-2" data-i18n=""><span>Project: </span><?=$projectData['title']?>
    <span class="h5 d-inline-block mb-0 align-top ms-1">
        <?=$projectStatusBadges[$projectStatus]?>
    </span>
</h2>


<div class="tabs sticky">
    <ul>
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="./projects_summary.php?projectId=<?=$projectId?>" data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./projects_webtraffic.php?projectId=<?=$projectId?>" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="./projects_searchanalytics.php?projectId=<?=$projectId?>" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="./projects_pagefeedback.php?projectId=<?=$projectId?>" data-i18n="tab-pagefeedback">Page feedback</a></li>
        <li <?php if ($tab=="calldrivers") {echo "class='is-active'";} ?>><a href="./projects_calldrivers.php?projectId=<?=$projectId?>" data-i18n="tab-calldrivers">Call drivers</a></li>
        <li <?php if ($tab=="uxtests") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-uxtests">UX tests</a></li>
        <li <?php if ($tab=="details") {echo "class='is-active'";} ?>><a href="./projects_details.php?projectId=<?=$projectId?>" data-i18n="tab-details">Details</a></li>
    </ul>
</div>

<?php

$dateUtils = new DateUtils();

$weeklyDatesHeader = $dateUtils->getWeeklyDates('header');
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
$diff = differ($avgCmpTaskSuccess, $avgTaskSuccess);
// echo $avgCmpTaskSuccess;
// echo "<br>";
// echo $avgTaskSuccess;
// echo "<br>";
// echo $diff;
//$diff = differ($avgTaskSuccess, $avgCmpTaskSuccess);
$pos = posOrNeg($diff);
$pieces = explode(":", $pos);
//
$diff = abs($diff);

//$kpi_pos = metKPI($avgTaskSuccess, $avgCmpTaskSuccess);
$kpi_pos = metKPI($avgCmpTaskSuccess, $avgTaskSuccess);
$kpi_pieces = explode(":", $kpi_pos);
?>

<div class="row mb-2 gx-2">
  <div class="col-lg-6 col-md-6 col-sm-12">
    <div class="card">
      <div class="card-body card-pad pt-2">
        <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-i18n="">Average task success from last UX test</span></h3>
          <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=percent($avgTaskSuccess); ?></span><span class="small"><?//=number_format($metrics[$visitors + 2]) ?></span></div>
            <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?php if (count($prjDatesUnique)>1) {echo percent($diff);}  ?></span></div>
          </div>
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12"><span class="<?=$kpi_pieces[0] ?> text-nowrap"><span class="material-icons"><?=$kpi_pieces[1] ?></span></span><span class="text-nowrap"> <?=$kpi_pieces[2]?> objective of 80% task success or 20 point increase</span></div>
          </div>
      </div>
    </div>
  </div>

   <div class="col-lg-6 col-md-6 col-sm-12 extend_height">
     <div class="card">
       <div class="card-body card-pad pt-2">
         <h3 class="card-title"><span class="h6" data-i18n="">Total participants</span></h3>
           <div class="row">
             <div class="col-sm-8"><span class="h3 text-nowrap"><?=number_format($prjParticipants) ?></span><span class="small"></span></div>
             <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span></span></div>
         </div>
       </div>
     </div>
   </div>
 </div>



<!-- <div class="row mb-2 gx-2">
  <div class="col-lg-6 col-md-6 col-sm-12">
    <div class="card">
      <div class="card-body card-pad pt-2">
        <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-i18n="">Average task success from last UX test</span></h3>
          <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=percent($avgTaskSuccess); ?></span><span class="small"><?//=number_format($metrics[$visitors + 2]) ?></span></div>
            <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?=$pieces[0] ?> text-nowrap"><span class="material-icons"><?=$pieces[1] ?></span> <?php if (count($prjDatesUnique)>1) {echo percent($diff);}  ?></span></div>
          </div>
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12"><span class="<?=$kpi_pieces[0] ?> text-nowrap"><span class="material-icons"><?=$kpi_pieces[1] ?></span></span><span class="text-nowrap"> <?=$kpi_pieces[2]?> objectve of 80% task success or 20 point increase</span></div>
          </div>
      </div>
    </div>
  </div> -->




<!-- D3 VISUALIZATION -->

<div class="row mb-4">
  <div class="col-lg-12 col-md-12">
    <div class="card">
      <div class="card-body pt-2">
        <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="Tasks success by Test" data-bs-original-title="" title="" data-i18n="">Tasks success by Test</span></h3>
          <div class="card-body pt-2" id="d3_uxtests"></div>
            <div id="d3_www_legend"></div>
              <!-- Task Success by Test D3 bar chart -->
              <script src="https://d3js.org/d3-scale-chromatic.v1.min.js"></script>


               <?php
               foreach ($prjData as $key => $value) {
                 //For the UX test names to be unique, we add "Test:i-" for every test
                 // we can also ADD multiple "white spaces" in front of the name (cause the Test name is aligned right on this axis_)
                 // (code for white spaces - str_repeat("", i))
                 //OR
                 //we can use the dates (in brackets) to make all same Ux tests unique (maybe add extra second or minute), but keep the date correct.
                 $d3["test"] = "UX Test:".($key+1)."-".$value["Test title"];
                 $d3["rate"] = $value["Success Rate"];
                 $d3Data[] = $d3;
               }

               $allGroups = array_column($d3Data, "test");
               // echo "<pre>";
               // print_r($prjData);
               // echo "</pre>";
               ?>



              <?php

              //$prjByGroupType = group_by('Test Type', $prjData);
              $testTypes = [];
              foreach (array_reverse($prjByGroupType) as $key => $value) {
               $testTypes[] = $key." (".date("Y-m-d", strtotime($value[0]['Date'])).")";
              }


              // echo "Data (not JSON)<pre>";
              // print_r($prjData);
              // echo "</pre>";
              // ------------------------------------------------------------------------------
              // ---- IMPORTANT! - dataReady php array, converted to json----------
              // with this, we don't need the dataReady mapping done in JS, we directly provide the
              // json we need for the D3 visualization
              // ------------------------------------------------------------------------------
              $f =[];
              foreach ($prjData as $k) {
                  //$fJson["name"] = $k["id"];

                  // if (!(array_key_exists($k["Task"],$fJson)))
                  //   {
                  //   $fJson["name"] = $k["Task"];
                  //   }

                  $fJson["name"] = $k["Task"];
                  $fJ=[];
                  $fJ2=[];
                  foreach (array_reverse($prjByGroupType) as $key => $value) {
                      // if ($key === $k["Test Type"]) {
                      //     $fJ["test"] = $key;
                      //     $fJ["rate"] = $k["Success Rate"];
                      //     $fJ2[] = $fJ;
                      // }

                      // foreach ($value as $tst2) {
                      //   if (($tst2["Task"] === $k["Task"]) && (!empty($tst2["Success Rate"]))) {
                      //       $fJ["test"] = $key;
                      //       $fJ["rate"] = $tst2["Success Rate"];
                      //       $fJ2[] = $fJ;
                      //   }
                      // }

                      foreach ($value as $tst2) {
                        if (($tst2["Task"] === $k["Task"])) {

                                if (!empty($tst2["Success Rate"])) {
                                  $fJ["test"] = $key;
                                  $fJ["rate"] = $tst2["Success Rate"]*100;
                                }
                                // else {
                                //   $fJ["rate"] = 0;
                                // }

                            $fJ2[] = $fJ;
                        }
                      }

                  }
                  $fJson["values"] = $fJ2;
                  $f[] = $fJson;
              }
              //echo count($f);

              function unique_multidim_array($array, $key) {
                  $temp_array = array();
                  $i = 0;
                  $key_array = array();

                  foreach($array as $val) {
                      if (!in_array($val[$key], $key_array)) {
                          $key_array[$i] = $val[$key];
                          $temp_array[$i] = $val;
                      }
                      $i++;
                  }
                  return $temp_array;
              }

              $f = unique_multidim_array($f,'name');

              // echo "<pre>";
              // print_r($f);
              // echo "</pre>";

              //$f = array_slice($f,0,(count($f)/2));


              //echo "Data (not JSON)<pre>";
              // print_r($f);
              // echo "</pre>";
              // ------------------------------------------------------------------------------
              // END------ IMPORTANT! - dataReady php array, converted to json----------
              // ------------------------------------------------------------------------------



              // NEW CODE FOR JSON file
              foreach (array_reverse($prjByGroupType) as $tst) {
                $j=[];
                $j["type"] = $tst[0]["Test Type"];
                foreach ($tst as $t) {

                    if (!empty($t["Success Rate"])) {
                      //echo $value["Test title"]."<br>";
                      $j[$t["Task"]] = $t["Success Rate"]*100;
                    }
                    //print_r($a);
                    //echo count($a["Baseline"]);
                }
                $jA[]=$j;
              }
              //$jA = array_slice($jA, 0, (count($jA)/2));
              // echo "Data (not JSON)<pre>";
              // print_r($f);
              // echo "</pre>";

              $myNewData3 = json_encode($jA);

              $uxTestsTasks = array_values(array_unique(array_flatten(array_column_recursive($prjData,"Task"))));
              $uxTestsTypes = array_values(array_unique(array_flatten(array_column_recursive($prjData,"Test Type"))));
              // echo "Ids<pre>";
              // print_r($jA);
              // echo "</pre>";
              //
              ?>

              <script>
              //
              // set the dimensions and margins of the graph
              width = parseInt(d3.select('#d3_uxtests').style('width'), 10)
              height = width / 3;
              //alert("hellp");
              var margin = {top: 10, right: 30, bottom: 100, left: 100},
                  width = width - margin.left - margin.right,
                  height = height - margin.top - margin.bottom,
                  legendHeight = 0,
                  //dualaxisWidth = 120;
                  dualaxisWidth = 0;


              // append the svg object to the body of the page
              var svg1 = d3.select("#d3_uxtests")
                .append("svg")
                  .attr("width", width + margin.left + margin.right)
                  .attr("height", height + margin.top + margin.bottom + legendHeight)
                .append("g")
                  .attr("transform",
                        "translate(" + margin.left + "," + margin.top + ")");


              // Parse the Data
                //var data = <?//=$mydata?>;

                //var data = <?//=$mydata;?>;
                // var data = <?//=$myNewData3;?>;
                // console.log("data");
                // console.log(data);



                // List of groups (here I have one group per column)
                //var allGroup = ["valueA", "valueB", "valueC"]
                //var allGroup = ["Test 1","Test 2","Test 3","Test 4"]
                var allGroup = <?=json_encode($uxTestsTasks)?>;
                console.log("Tasks:")
                console.log(allGroup);
                //var allGroup = ["CRSB  - Post-launch test 1"]
                //var testTypes = ["Baseline","Validation"]
                var testTypes = <?=json_encode(array_reverse($uxTestsTypes))?>;
                console.log("Test types:")
                console.log(testTypes);

                // Reformat the data: we need an array of arrays of {x, y} tuples
                // var dataReady = allGroup.map( function(grpName) { // .map allows to do something for each element of the list
                //   return {
                //     name: grpName,
                //     values: data.map(function(d) {
                //       //return {time: d.Type, value: +d[grpName]};
                //       return {test: d.type, rate: +d[grpName]};
                //     })
                //   };
                // });

                var dataReady = <?=json_encode($f)?>;
                // I strongly advise to have a look to dataReady with
                //console.log(data)
                console.log("dataReady JSON data")
                console.log(dataReady)

                // A color scale: one color for each group
                var myColor = d3.scaleOrdinal()
                  .domain(allGroup)
                  .range(['#345EA5','#6CB5F3','#36A69A','#F8C040']);
                  //.range(d3.schemeSet2);


                // Add Y axis
                var y = d3.scaleLinear()
                  .domain( [0,100])
                  .range([ height, 0 ]);
                svg1.append("g")
                  .call(d3.axisLeft(y).ticks(5));

                // grid lines on Y axis
                var yGrid = d3.axisLeft(y).tickSize(-width).tickFormat('').ticks(10);

                //create  yGrid
                svg1.append('g')
                    .attr('class', 'axis-grid')
                    //.attr('transform', 'translate(0,' + height + ')')
                    .call(yGrid);


                // Add X axis --> it is a date format
                var x = d3.scaleBand()
                  .domain(testTypes)
                  .range([ 0 , width ])
                  .padding(1);
                svg1.append("g")
                  .attr("transform", "translate(0," + height + ")")
                  .call(d3.axisBottom(x));


              // if there's more than one test types, add the lines between the tests, otherwise  do not draw the lines
              <?php if (count($prjByGroupType)>1) {?>

                // Add the lines
                var line = d3.line()
                  .x(function(d) { return x(d.test) })
                  .y(function(d) { return y(d.rate) })
                svg1.selectAll("myLines")
                  .data(dataReady)
                  .enter()
                  .append("path")
                    .attr("d", function(d){ return line(d.values) } )
                    .attr("stroke", function(d){ return myColor(d.name) })
                    .style("stroke-width", 4)
                    .style("fill", "none")

                <?php } ?>
                //const repeats = [75,75,75];
                //const repeats = <?//=json_encode($duplicateRates)?>;
                const repeats = [<?=implode(",",$duplicateRates)?>];
                console.log(repeats);
                // Add the points
                svg1
                  // First we need to enter in a group
                  .selectAll("myDots")
                  .data(dataReady)
                  .enter()
                    .append('g')
                    .style("fill", function(d){ return myColor(d.name) })
                  // Second we need to enter in the 'values' part of this group
                  .selectAll("myPoints")
                  .data(function(d){ return d.values })
                  .enter()
                  .append("circle")
                    //.attr("cx", function(d) { return x(d.test) } )
                    .attr("cx", function(d) {
                    <?php if (count($prjByGroupType)>1) { ?>
                          return x(d.test) } )
                    <?php }
                    else { ?>
                        //if(repeats.indexOf(d.rate) !== -1){ includes("Banana")
                       if(repeats.indexOf(d.rate) !== -1){
                          repeats.pop()
                          console.log(d.rate)
                          return x(d.test)+20*repeats.length
                          //console.log(repeats);
                        } else{
                          return x(d.test);
                        }

                      } )
                    <?php } ?>

                    .attr("cy", function(d) { return y(d.rate) } )
                    .attr("r", 7)
                    .attr("stroke", "white")

                // Add a LABEL/LEGEND at the end of each line
                // svg1
                //   .selectAll("myLabels")
                //   .data(dataReady)
                //   .enter()
                //     .append('g')
                //     .append("text")
                //       //.datum(function(d) { return {name: d.name, value: d.values[d.values.length - 1]}; }) // keep only the last value of each time series
                //       <?php //if (count($prjByGroupType)>1) {?>
                //           .datum(function(d) { return {name: d.name, value: d.values[1]}; }) // keep only the last value of each time series
                //       <?php //} else { ?>
                //             .datum(function(d) { return {name: d.name, value: d.values[0]}; }) // keep only the last value of each time series
                //       <?php //} ?>
                //
                //       .attr("transform", function(d,i) { return "translate(" + x(d.value.test) + "," + y(d.value.rate) + ")"; }) // Put the text at the position of the last point
                //       .attr("x", 12) // shift the text a bit more right
                //       .text(function(d) { return d.name; })
                //       .style("fill", function(d){ return myColor(d.name) })
                //       .style("font-size", 15);


              // LABEL y axis
              svg1.append("text")
                  .attr("transform", "rotate(-90)")
                  .attr("y", 0 - margin.left)
                  .attr("x", 0 - (height / 2))
                  .attr("dy", "1em")
                  .style("text-anchor", "middle")
                  .text("Task success rate (%)");

              svg1.append("text")
                  .attr("transform", "rotate(0)")
                  .attr("y", height + 50)
                  .attr("x", width/2)
                  .attr("dy", "1em")
                  .style("text-anchor", "middle")
                  .text("UX Test types");

              // tick text size
              svg1.selectAll(".tick text")
                  //.attr("class","axis_labels")
                  .style("font-size","14px")
                  .style("fill","#666");


                  // LEGEND
                  var legend = d3.select('#d3_www_legend').selectAll("legend")
                      .data(allGroup);

                  var legend_cells = legend.enter().append("div")
                      .attr("class","legend");

                  var p = legend_cells.append("p").attr("class","legend_field");
                  p.append("span").attr("class","legend_color").style("background",function(d,i) { return myColor(i) } );
                  p.insert("text").text(function(d,i) { return d } );




              </script>



              <details class="details-chart">
                   <summary data-i18n="view-data-table">View table data</summary>
                       <div class="table-responsive">
                           <table class="table">
                             <caption><!--Last Week--></caption>
                             <thead>
                               <th data-i18n="" scope="col">Task</th>
                               <th data-i18n="" scope="col">Test Type</th>
                               <th data-i18n="" scope="col">Task Success Rate</th>
                             </thead>
                             <tbody>

                               <?php
                                   //foreach ($aaTrendLastWeek as $trend)
                                   foreach ($prjData as $key=>$value)
                                   {

                                   ?>

                                           <tr>
                                             <td><?=$value['Task']; ?></td>
                                             <td><?=$value["Test Type"]; ?></td>
                                             <td><?=percent($value['Success Rate']) ?></td>
                                           </tr>

                                           <?php
                                   }

                                   ?>


                             </tbody>
                           </table>
                     </div>
             </details>
          </div>
        </div>
      </div>
    </div>

<!-- end D3 VISUALIZATION-->



<div class="row mb-4">
  <div class="col-lg-12 col-md-12">
    <div class="card">
      <div class="card-body pt-2">
        <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Participant tasks</span></h3>
        <div class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

           <?php

          $qry = $projectTasks;
          // echo "<pre>";
          // print_r($qry);
          // echo "</pre>";

            if (count($qry) > 0) { ?>
              <div class="table-responsive">
                <table class="table table-striped dataTable no-footer">
                  <caption></caption>
                  <!-- <thead>
                    <tr>
                      <th data-i18n="task">Task</th>
                      <th data-i18n="change">>Change</th>
                      <th data-i18n="">Task Success Survey Completed</th>
                    </tr>
                  </thead> -->
                  <tbody>
                <?php foreach ($qry as $row) { ?>
                  <tr>
                      <td><a href="./tasks_summary.php?taskId=<?=$row['id']?>"><?=$row['Task']?></a></td>
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

        </div></div><div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div></div>
      </div>
    </div>
  </div>
</div>


<div class="row mb-4">
  <div class="col-lg-12 col-md-12">
    <div class="card">
      <div class="card-body pt-2">
        <h3 class="card-title"><span class="h6" data-i18n="">Task Success by UX Test </span></h3>
          <div class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

            <?php

               $qry = $prjTasks;
               //$prjTypes =

               //echo gettype($t5t['Total topic calls'][0]);
               // echo "<pre>";
               // print_r($prjByGroupType);
               // echo "</pre>";
              //var_dump($qry);

                 if (count($qry) > 0) { ?>
                   <div class="table-responsive">
                     <table class="table table-striped dataTable no-footer" id="toptask" data="" role="grid">
                       <caption>Task Success by UX Test </caption>
                       <thead>
                         <tr>
                           <th class="sorting" aria-controls="toptask" aria-label="Topic: activate to sort column" data-i18n="" scope="col">Task</th>
                           <?php foreach (array_reverse($prjByGroupType) as $key => $value) { ?>
                            <th class="sorting" aria-controls="toptask" aria-label="Topic: activate to sort column" data-i18n="" scope="col"><?=$key?> (<?=date("Y-m-d", strtotime($value[0]['Date']))?>)</th>
                            <?php } ?>
                           <!-- <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="# of calls" >Number of calls</th> -->
                           <!-- <th class="sorting" aria-controls="toptask" aria-label="Change: activate to sort column" data-i18n="" >Prev Week Calls</th> -->
                           <?php if (count($prjByGroupType)>1) {?>
                            <th class="sorting" aria-controls="toptask" aria-label="Topic: activate to sort column" data-i18n="comparison" scope="col">Comparison</th>
                           <?php } ?>
                         </tr>
                       </thead>
                       <tbody>

                     <?php foreach ($qry as $row) { ?>
                       <?php $change = array(); ?>
                         <tr>
                            <td><?=$row;?></td>
                            <?php $lastKey = array_key_last(array_reverse($prjByGroupType));
                              //echo $lastKey;
                            ?>
                            <?php foreach (array_reverse($prjByGroupType) as $key => $value) {
                                      $avgSR = 0;
                                      $count=0;

                                      //$lastKey2 = array_key_last($value);

                                      foreach ($value as $uxtest):

                                               $tt = $db->getTaskByUxTestId($uxtest['id'], ["Task"]);
                                               $t = array_column($tt, 'Task');
                                               // echo "<pre>";
                                               // var_dump($t);
                                               // echo "</pre>";

                                               if (in_array($row, $t)) {
                                                  $avgSR += $uxtest['Success Rate'];
                                                  $count += 1;
                                              }

                                      endforeach;
                                      // foreach ($value as $key2 => $value2):
                                      //
                                      //          $tt = $db->getTaskByUxTestId($value2['id'], ["Task"]);
                                      //          $t = array_column($tt, 'Task');
                                      //          // echo "<pre>";
                                      //          // var_dump($t);
                                      //          // echo "</pre>";
                                      //
                                      //          if (in_array($row, $t)) {
                                      //             $avgSR += $value2['Success Rate'];
                                      //             $count += 1;
                                      //         }
                                      //
                                      //
                                      //         if (($key2 == $lastKey2) && ($value2['Test type']==$key)) {
                                      //               echo "Last key is here";  // 'you can do something here as this condition states it just entered last element of an array';
                                      //         }
                                      //
                                      // endforeach;

                                    ($count>0) ? $avgSR_total = $avgSR/$count : $avgSR_total=0;
                                    $change[]= $avgSR_total;


                                  //if its the last Test, mark the test that met the KPI
                                  if ($key == $lastKey) { ?>
                                      <td <?php if ($avgSR_total>=0.8) { echo "style='background:#90EE90'"; } else { echo ""; }?> ><?=percent($avgSR_total)?></td>
                                 <?php  }
                                 else { ?>
                                      <td><?=percent($avgSR_total)?></td>
                                 <?php } ?>



                            <?php } //foreach prjByGroupType ?>

                           <?php if (count($prjByGroupType)>1) { ?>
                                 <?php
                                 //print_r($change);
                                 $last2tests = array_slice($change, -2, 2);
                                 $diff = numDiffer($last2tests[0], $last2tests[1]);
                                 $posi = posOrNeg2($diff);
                                 $pieces = explode(":", $posi);
                                 $diff = abs($diff);

                                 ?>
                                 <!-- <td><span>--><?//=$fieldsByGroupPW[$row[0]['Topic']]['Total topic calls'];?><!--</span></td> -->
                                 <td <?php if (($diff>=0.2) && ($pieces[1]=="+")) { echo "style='background:#90EE90'"; } else { echo ""; }?>><span class="<?=$pieces[0]?>"><?=$pieces[1]?> <?=percent($diff)?></span></td>
                           <?php } ?>
                         </tr>
                     <?php } ?>
                       </tbody>
                     </table>
                        <div>
                          <div class="legend"><p class="legend_field"><span class="legend_color" style="background: #90EE90;"></span><text>Met objective of 80% success rate or 20 point increase</text></p></div>
                        </div>
                   </div>
               <?php } ?>

        </div></div><div class="row"><div class="col-sm-12 col-md-5"></div><div class="col-sm-12 col-md-7"></div></div></div>
      </div>
    </div>
  </div>
</div>



<!--Main content end-->

<?php include "includes/upd_footer.php"; ?>
