
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

function metKPI($num, $old)
{
    if (($num > 0.8) || (abs($old-$num)>0.2))  return 'text-success:check_circle:Met';
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


//$relatedUxTests = array_column($taskTests, 'title');


// echo "<h4>UX Test correct</h4><pre>";
// print_r($projectTests);
// echo "</pre>";


// ------------------------------------------------------------------
// $prjTasks = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Lookup_Tasks"))));
// $prjPages = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Lookup_Pages"))));
// $prjStatus = array_values(array_unique(array_flatten(array_column_recursive($weeklyRe,"Status"))));
$prjParticipants = array_sum(array_column_recursive($projectTests,"# of Users"));
//echo "# of Users: ".$prjParticipants;

# of Users
// $relatedTasks = $fullArray[0]['fields']['Lookup_Tasks'];//['records'];
// $relatedProjects = $fullArray[0]['fields']['Projects'];

// echo "<pre>";
// print_r($prjTasks);
// echo "</pre>";
// echo "-------------<br/>";
// echo "<pre>";
// print_r($prjPages);
// echo "</pre>";



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


// echo "<pre>";
// print_r($prjByGroupType);
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
// echo "<br>";
// echo "<pre>";
// print_r($compareTestDate);
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

<h2 class="h3 pt-2 pb-2" data-i18n=""><?=$projectData['title']?>
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
// // Adobe Analytics
// $time = microtime(true);
//
// if (!isset($_SESSION['CREATED']))
// {
//     $_SESSION['CREATED'] = time();
//     require_once ('./php/getToken.php');
// }
// else if (time() - $_SESSION['CREATED'] > 86400)
// {
//     session_regenerate_id(true);
//     $_SESSION['CREATED'] = time();
//     require_once ('./php/getToken.php');
// }
// if (isset($_SESSION["token"]))
// {
//     require_once ('./php/api_post.php');
//     $config = include ('./php/config-aa.php');
//     $data = include ('./php/data-aa.php');
// }

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
//$diff = differ($avgTaskSuccess, $avgCmpTaskSuccess);
$pos = posOrNeg($diff);
$pieces = explode(":", $pos);
//
$diff = abs($diff);
$kpi_pos = metKPI($avgTaskSuccess, $avgCmpTaskSuccess);
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
   <!-- <div class="col-lg-6 col-md-6 col-sm-12">
     <div class="card">
       <div class="card-body card-pad pt-2">
         <h3 class="card-title"><span class="h6" data-i18n="">Average task success from last UX test</span></h3>
           <div class="row">
             <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?//=percent($avgTaskSuccess) ?></span><span class="small"></span></div>
             <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span> </span></div>
         </div>
       </div>
     </div>
   </div> -->

   <div class="col-lg-6 col-md-6 col-sm-12">
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
  </div>


<?php

// $diff = differ($metrics[$pv + 2], $metrics[$pv + 3]);
// $pos = posOrNeg($diff);
// $pieces = explode(":", $pos);
//
// $diff = abs($diff);
?>
  <div class="col-lg-6 col-md-6 col-sm-12">
    <div class="card">
      <div class="card-body card-pad pt-2">
        <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-i18n="">Call volume</span></h3>
          <div class="row">
            <div class="col-md-8 col-sm-6"><span class="h3 text-nowrap"><?//=number_format($totalVisits) ?></span><span class="small"><?//=number_format($metrics[$visits + 2]) ?></span></div>
            <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 <?//=$pieces[0] ?> text-nowrap"><span class="material-icons"><?//=$pieces[1] ?></span> <?//=percent($diff) ?></span></div>
          </div>
          <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12"><span class="<?//=$pieces[0] ?> text-nowrap"><span class="material-icons"><?//=$pieces[1] ?></span></span><span class="text-nowrap"></span></div>
          </div>
      </div>
    </div>
  </div>

</div> -->


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
               // var_dump($prjByGroupType);
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
                           <?php foreach (array_reverse($prjByGroupType) as $type) { ?>
                             <?php $avgSR = 0; $count=0; ?>
                             <?php foreach ($type as $uxtest): ?>

                                     <?php //$t = $uxtest['id'];//$t = $uxtest['Lookup_Tasks'];
                                           //$t = $db->getTaskByUxTestId($uxtest['id'], ['"Test title"']);
                                           $tt = $db->getTaskByUxTestId($uxtest['id'], ["Task"]);
                                           $t = array_column($tt, 'Task');
                                           // echo "<pre>";
                                           // var_dump($t);
                                           // echo "</pre>";

                                           if (in_array($row, $t)) {
                                              $avgSR += $uxtest['Success Rate'];
                                              $count += 1;
                                          }
                                     ?>

                             <?php endforeach; ?>


                             <?php
                                    ($count>0) ? $avgSR_total = $avgSR/$count : $avgSR_total=0;
                                    $change[]= $avgSR_total;
                             ?>


                                <td><?=percent($avgSR_total)?></td>
                            <?php } ?>

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
                                 <td><span class="<?=$pieces[0]?>"><?=$pieces[1]?> <?=percent($diff)?></span></td>
                           <?php } ?>
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
