
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

          //ini_set('display_errors', 1);
          require 'vendor/autoload.php';
          use TANIOS\Airtable\Airtable;

          // Adobe Analytics
          // $time = microtime(true);
          $succ = 0;

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


      ?>



    <?php
        $urls = "";
        $url = "";


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


    ?>



      <?php

              $config = include ('./php/config-at.php');

              //$start2 = microtime(true);

              $airtable = new Airtable($config["tasks"]);

              //$params = array("filterByFormula" => 'LEN({Test Type})>0'); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
              $params = array(); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
              //print_r($params);
              $table = 'User testing';

              $fullArray = [];

              $request = $airtable->getContent($table, $params);
              do
              {
                  $response = $request->getResponse();
                  $fullArray = array_merge($fullArray, ($response->records));
              }
              while ($request = $response->next());



              //$re = ( json_decode( $fullArray, true ) )['records'];
              $re = ( json_decode(json_encode($fullArray), true));//['records'];

              // if there's data (record exist)
              if ( count( $re ) > 0 ) {



                    $sub_tasks = array_column_recursive($re, 'Sub-Task');
                    //echo "<br><br><pre>";
                    //print_r($sub_tasks); //Prev WEEK
                    //echo "</pre><br></br>";

                    //echo "Total of Sub-tasks:". count($sub_tasks);

                    $num_participants = array_sum(array_column_recursive($re, '# of Users'));
                    //echo "<br><br><pre>";
                    //print_r($sub_tasks); //Prev WEEK
                    //echo "</pre><br></br>";

                    //echo "Total of participants:". array_sum($num_participants);


                    $total_tasks = array_column_recursive($re, 'Task');
                    //echo "<br><br><pre>";
                    //print_r(single_array($total_tasks)); //Prev WEEK
                    //print_r(array_unique(single_array($total_tasks)));
                    //echo "</pre><br></br>";

                    //echo "Total of unique Tasks:". count(array_unique(single_array($total_tasks)));


                    $all_dates = array_column_recursive($re, 'Date');


                    // Get just the ['fields'] array of each record -  as a separate array - $all_fields
                    $all_fields = array_column_recursive($re, 'fields');

                    //Sort all_fields array by Date key in descending order
                    // if we need an ascernding order, swap the $a and $b variable as function arguments
                    usort($all_fields, function($b, $a) {
                       return new DateTime($a['Date']) <=> new DateTime($b['Date']);
                     });

                     // group the all_fields by "UX Research Project Title" key
                    $fieldsByGroup = group_by('UX Research Project Title', $all_fields);


                     //$financialyeardate = (date('m')<'04') ? date('Y-04-01',strtotime('-1 year')) : date('Y-04-01');
                     $prev_financialyear_startdate = (date('m')<'04') ? date('Y-04-01',strtotime('-2 years')) : date('Y-04-01',strtotime('-1 year'));
                     $prev_financialyear_enddate = (date('m')<'03') ? date('Y-03-31',strtotime('-1 years')) : date('Y-03-31');

                     //$financialyeardate = (date('m')<'04') ? date('Y-04-01',strtotime('-1 year')) : date('Y-04-01');
                     $last6Months_startdate = date('Y-m-d',strtotime ("first day of this month", strtotime('-6 months')));
                     $last6Months_enddate = date('Y-m-d',strtotime('first day of this month'));
                     //$last6Months_enddate = date('Y-m-d',strtotime ("last day of this month", strtotime('-1 months')));


                     // echo $last6Months_startdate;
                     // echo "<br>";
                     // echo $last6Months_enddate;

                     //echo "$prev_financialyear_startdate - $prev_financialyear_enddate";

                     //The "n" format character gives us
                     //the month number without any leading zeros
                     $month = date("n");
                     //Calculate the year quarter.
                     $currentQuarter = ceil($month / 3);

                     if ($currentQuarter == 1) {
                       $lastQuarter = 4;
                       $qStart = date('Y-10-01',strtotime('-1 year'));
                       $qEnd = date('Y-12-31',strtotime('-1 year'));
                     }
                     elseif ($currentQuarter == 2) {
                       $lastQuarter = $currentQuarter-1;
                       $qStart = date("Y-01-01");
                       $qEnd = date("Y-03-31");
                     }
                     elseif ($currentQuarter == 3) {
                       $lastQuarter = $currentQuarter-1;
                       $qStart = date("Y-04-01");
                       $qEnd = date("Y-06-30");
                     }
                     elseif ($currentQuarter == 4) {
                       $lastQuarter = $currentQuarter-1;
                       $qStart = date("Y-07-01");
                       $qEnd = date("Y-09-30");
                     }


                     $projectsLastFiscal = count(array_filter($all_fields, function ($val) use ($prev_financialyear_startdate, $prev_financialyear_enddate) {
                        return (date("Y-m-d", strtotime($val["Date"])) >= $prev_financialyear_startdate && date("Y-m-d", strtotime($val["Date"])) <= $prev_financialyear_enddate);
                     }));


                     $projectsLastQuarter = count(array_filter($all_fields, function ($val) use ($qStart, $qEnd) {
                        return (date("Y-m-d", strtotime($val["Date"])) >= $qStart && date("Y-m-d", strtotime($val["Date"])) <= $qEnd);
                     }));



                     $fieldsByGroupStatus = group_by('Status', $all_fields);

                     // echo "<pre>";
                     // print_r($fieldsByGroupStatus);
                     // echo "</pre>";

                     if (array_key_exists('Complete', $fieldsByGroupStatus)) {
                          $completedProjects = array_values(array_unique(array_column_recursive($fieldsByGroupStatus['Complete'], "UX Research Project Title")));
                          //$completedProjects = array_values(array_column_recursive($fieldsByGroupStatus['Complete'], "UX Research Project Title"));
                      }
                      else {
                          $completedProjects = array();
                      }

                     if (array_key_exists('In Progress', $fieldsByGroupStatus)) {
                          $inProgressProjects = array_values(array_unique(array_column_recursive($fieldsByGroupStatus['In Progress'], "UX Research Project Title")));
                      }
                      else {
                          $inProgressProjects = array();
                      }

                     if (array_key_exists('Delayed', $fieldsByGroupStatus)) {
                          $delayedProjects = array_values(array_unique(array_column_recursive($fieldsByGroupStatus['Delayed'], "UX Research Project Title")));
                     }
                     else {
                         $delayedProjects = array();
                     }
                     //$completedProjects = array_values(array_column_recursive($fieldsByGroup, "UX Research Project Title"));

                     $completedProjectsLast6Months = array_filter($fieldsByGroupStatus['Complete'], function ($val) use ($last6Months_startdate, $last6Months_enddate) {
                        return (date("Y-m-d", strtotime($val["Date"])) >= $last6Months_startdate && date("Y-m-d", strtotime($val["Date"])) <= $last6Months_enddate);
                     });
                     $projectsLast6Months = array_values(array_unique(array_column_recursive($completedProjectsLast6Months, "UX Research Project Title")));


                     // echo count($projectsLast6Months);
                     // echo "<pre>";
                     // print_r($projectsLast6Months);
                     // echo "</pre>";
              }

          ?>


    <!-- 3 number charts -->
    <div class="row mb-4 gx-4">
       <div class="col-lg-3 col-md-6 col-sm-12">
         <div class="card">
           <div class="card-body card-pad pt-2">
             <h3 class="card-title"><span class="h6" data-i18n="">Projects in progress</span></h3>
               <div class="row">
                 <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=number_format(count($inProgressProjects)) ?></span><span class="small"></span></div>
                 <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span> </span></div>
             </div>
           </div>
         </div>
       </div>

       <div class="col-lg-3 col-md-6 col-sm-12">
         <div class="card">
           <div class="card-body card-pad pt-2">
             <h3 class="card-title"><span class="h6" data-i18n="">Projects completed (last 6 months)</h3>
               <div class="row">
                 <div class="col-md-8 col-sm-6"><span class="h3 text-nowrap"><?=number_format(count($projectsLast6Months)) ?></span><span class="small"></span></div>
                 <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span></span></div>
             </div>
           </div>
         </div>
       </div>

       <div class="col-lg-3 col-md-6 col-sm-12">
         <div class="card">
           <div class="card-body card-pad pt-2">
             <h3 class="card-title"><span class="h6" data-i18n="">Total projects completed</h3>
               <div class="row">
                 <div class="col-md-8 col-sm-6"><span class="h3 text-nowrap"><?=number_format(count($completedProjects)) ?></span><span class="small"></span></div>
                 <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span></span></div>
             </div>
           </div>
         </div>
       </div>

       <div class="col-lg-3 col-md-6 col-sm-12">
         <div class="card">
           <div class="card-body card-pad pt-2">
             <h3 class="card-title"><span class="h6" data-i18n="">Projects delayed</h3>
               <div class="row">
                 <div class="col-sm-8"><span class="h3 text-nowrap"><?=number_format(count($delayedProjects)) ?></span><span class="small"></span></div>
                 <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span></span></div>
             </div>
           </div>
         </div>
       </div>
     </div>


     <!-- Dropdown - date range   -->
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

    <!-- Recent US test results by page - currently listed ALL instead of recent only -->
    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="h6" data-i18n="">List of all projects</span></h3>
              <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

               <?php

              $qry = $fieldsByGroup;

              //var_dump($qry);

                if (count($qry) > 0) { ?>
                  <div class="table-responsive">
                    <!-- <table class="table table-striped dataTable no-footer" id="pages_dt"> -->
                    <table class="table table-striped dataTable no-footer">
                      <thead>
                        <tr>
                          <th data-i18n="">Name</th>
                          <th data-i18n="type">Type</th>
                          <th data-i18n="">Status</th>
                          <th data-i18n="date">Date</th>
                          <!-- <th data-i18n="avg_success_rate">Average success rate</th> -->
                        </tr>
                      </thead>
                      <tbody>
                    <?php foreach ($qry as $row) { ?>
                        <tr>
                          <td><a href="./projects_pagefeedback.php?prj=<?= $row[0]['UX Research Project Title'] ?>" alt="<?=$row[0]['UX Research Project Title'];?>"><?=$row[0]['UX Research Project Title'];?></a></td>
                          <td><span><?=array_key_exists('COPS', $row[0]) ? "COPS" : "N/A";     //echo ($row[0]['COPS']==1) ? "COPS" : "N/A";           //$row[0]['COPS'];?></span></td>
                          <td><span><?=$row[0]['Status'];             //$row[0]['Date'];?></span></td>
                          <td><span><?=array_key_exists('Date', $row[0]) ? date('m/Y',strtotime($row[0]['Date'])) : "";             //$row[0]['Date'];?></span></td>
                          <!-- <td><span><?//=round((array_sum(array_column_recursive($row, "Success Rate"))/count(array_column_recursive($row, "Success Rate")))*100)."%";?></span></td> -->
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

<?php
// $time_elapsed_secs = microtime(true) - $time;
// echo "<p>Time taken: " . number_format($time_elapsed_secs, 2) . " seconds</p>";
?>


<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
