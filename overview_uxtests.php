
<?php include "./includes/upd_header.php"; ?>
<?php include "./includes/upd_sidebar.php"; ?>
<?php include "./includes/date-ranges.php"; ?>
<?php include "./includes/functions.php"; ?>
<?php //ini_set('display_errors', 1);
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

    <h2 class="h3 pt-2 pb-2" data-i18n="overview-title">Overview of CRA website</h2>

    <!-- Tabs menu -->

    <div class="tabs sticky">
      <ul>
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="./overview_summary.php" data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./overview_webtraffic.php" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="./overview_searchanalytics.php" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="./overview_pagefeedback.php" data-i18n="tab-pagefeedback">Page feedback</a></li>
        <li <?php if ($tab=="calldrivers") {echo "class='is-active'";} ?>><a href="./overview_calldrivers.php" data-i18n="tab-calldrivers">Call drivers</a></li>
        <li <?php if ($tab=="uxtests") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-uxtests">UX tests</a></li>
      </ul>
    </div>

      <?php

            ini_set('display_errors', 0);
            require 'vendor/autoload.php';
            use TANIOS\Airtable\Airtable;

            // Adobe Analytics
            $time = microtime(true);
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

    <!-- Dropdown - date range   -->
    <!-- <div class="row mb-4 mt-1">
      <div class="dropdown">
        <button type="button" class="btn bg-white border border-1" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> <span data-i18n="dr-alltime">All time</span></button> <span class="text-secondary ps-2 text-nowrap" data-i18n="dr-alltime"> All time</span>

      </div>
    </div> -->

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


    ?>



      <?php

              $config = include ('./php/config-at.php');

              $start2 = microtime(true);

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
                    // echo "<pre>";
                    // print_r($fieldsByGroup);
                    // echo "</pre>";


                     //$financialyeardate = (date('m')<'04') ? date('Y-04-01',strtotime('-1 year')) : date('Y-04-01');
                     $prev_financialyear_startdate = (date('m')<'04') ? date('Y-04-01',strtotime('-2 years')) : date('Y-04-01',strtotime('-1 year'));
                     $prev_financialyear_enddate = (date('m')<'03') ? date('Y-03-31',strtotime('-1 years')) : date('Y-03-31');
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

                     //this gives as the TASKS tested for last fiscal year (count per test)
                     $projectsLastFiscal = count(array_filter($all_fields, function ($val) use ($prev_financialyear_startdate, $prev_financialyear_enddate) {
                        return (date("Y-m-d", strtotime($val["Date"])) >= $prev_financialyear_startdate && date("Y-m-d", strtotime($val["Date"])) <= $prev_financialyear_enddate);
                     }));

                     // THIS lists all the Tests (UX Research Project Title) for the last fiscal year (count per unique UX Research Project Title)
                    //-------------------------------------------------------------------------------------------------------------------------------
                     $testsLastFiscal = array_filter($all_fields, function ($val) use ($prev_financialyear_startdate, $prev_financialyear_enddate) {
                        return (date("Y-m-d", strtotime($val["Date"])) >= $prev_financialyear_startdate && date("Y-m-d", strtotime($val["Date"])) <= $prev_financialyear_enddate);
                     });
                     $totalTestsLastFiscal = count(array_unique(array_column($testsLastFiscal, 'UX Research Project Title')));


                     //this gives as the TASKS tested for last quarter (count per test)
                     $projectsLastQuarter = count(array_filter($all_fields, function ($val) use ($qStart, $qEnd) {
                        return (date("Y-m-d", strtotime($val["Date"])) >= $qStart && date("Y-m-d", strtotime($val["Date"])) <= $qEnd);
                     }));

                     // THIS lists all the Tests (UX Research Project Title) for the last QUARTER (count per unique UX Research Project Title)
                     //-------------------------------------------------------------------------------------------------------------------------------
                     $testsLastQuarter = array_filter($all_fields, function ($val) use ($qStart, $qEnd) {
                        return (date("Y-m-d", strtotime($val["Date"])) >= $qStart && date("Y-m-d", strtotime($val["Date"])) <= $qEnd);
                     });
                     $totalTestsLastQuarter = count(array_unique(array_column($testsLastQuarter, 'UX Research Project Title')));
                     // echo "<pre>";
                     // print_r($projectsLastFiscal2);
                     // echo "</pre>";


                     // COPS tested tested since  2018
                    //-------------------------------------------------------------------------------------------------------------------------------
                     $testCops = array_filter($all_fields, function ($val) {
                        return (array_key_exists('COPS', $val));
                     });
                     $totalTestsCops = count(array_unique(array_column($testCops, 'UX Research Project Title')));
                     //echo $totalTestsCops;
              }

          ?>


    <!-- 3 number charts -->
    <div class="row mb-3 gx-3">
       <div class="col-lg-4 col-md-6 col-sm-12">
         <div class="card">
           <div class="card-body card-pad pt-2">
             <h3 class="card-title"><span class="h6" data-i18n="">Tests completed since 2018</span></h3>
               <div class="row">
                 <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=number_format(count($fieldsByGroup)) ?></span><span class="small"></span></div>
                 <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span> </span></div>
             </div>
           </div>
         </div>
       </div>

       <div class="col-lg-4 col-md-6 col-sm-12">
         <div class="card">
           <div class="card-body card-pad pt-2">
             <h3 class="card-title"><span class="h6" data-i18n="">Tasks tested since 2018</span></h3>
               <div class="row">
                 <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=number_format(count($re)); ?></span><span class="small"></span></div>
                 <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span> </span></div>
             </div>
           </div>
         </div>
       </div>

       <div class="col-lg-4 col-md-6 col-sm-12">
         <div class="card">
           <div class="card-body card-pad pt-2">
             <h3 class="card-title"><span class="h6" data-i18n="">Participants tested since 2018</span></h3>
               <div class="row">
                 <div class="col-md-8 col-sm-6"><span class="h3 text-nowrap"><?=number_format($num_participants); ?></span></div>
                 <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span></span></div>
             </div>
           </div>
         </div>
       </div>
    </div>

    <!-- 2 number charts -->
    <div class="row mb-3 gx-3">

        <div class="col-lg-4 col-md-6 col-sm-12">
          <div class="card">
            <div class="card-body card-pad pt-2">
              <h3 class="card-title"><span class="h6" data-i18n="">Tests conducted last fiscal year</span><span class="h6"> (<?=date("M d", strtotime($prev_financialyear_startdate));?> - <?=date("M d",strtotime($prev_financialyear_enddate));?>)</span></h3>
                <div class="row">
                  <!-- <div class="col-md-8 col-sm-6"><span class="h3 text-nowrap"><?//=number_format($projectsLastFiscal) ?></span><span class="small"></span></div> -->
                  <div class="col-md-8 col-sm-6"><span class="h3 text-nowrap"><?=number_format($totalTestsLastFiscal) ?></span><span class="small"></span></div>
                  <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span></span></div>
              </div>
            </div>
          </div>
        </div>

        <div class="col-lg-4 col-md-6 col-sm-12">
          <div class="card">
            <div class="card-body card-pad pt-2">
              <h3 class="card-title"><span class="h6" data-i18n="">Tests conducted last quarter</span><span class="h6"> (Q<?=$lastQuarter."/"?><?=($lastQuarter != 4) ? date('y') : date('y',strtotime('-1 years')); ?>)</span></h3>
                <div class="row">
                  <!-- <div class="col-sm-8"><span class="h3 text-nowrap"><?//=number_format($projectsLastQuarter) ?></span><span class="small"></span></div> -->
                  <div class="col-sm-8"><span class="h3 text-nowrap"><?=number_format($totalTestsLastQuarter) ?></span><span class="small"></span></div>
                  <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span></span></div>
              </div>
            </div>
          </div>
        </div>

       <div class="col-lg-4 col-md-6 col-sm-12">
         <div class="card">
           <div class="card-body card-pad pt-2">
             <h3 class="card-title"><span class="h6" data-i18n="">COPS tests completed since 2018</span></h3>
               <div class="row">
                 <div class="col-md-8 col-sm-6"><span class="h3 text-nowrap"><?=number_format($totalTestsCops); ?></span></div>
                 <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span></span></div>
             </div>
           </div>
         </div>
       </div>
    </div>

    <!-- Recent US test results by page - currently listed ALL instead of recent only -->
    <div class="row mb-4">
      <div class="col-lg-12 col-md-12">
        <div class="card">
          <div class="card-body pt-2">
            <h3 class="card-title"><span class="h6" data-i18n="">Recent testing scores by project</span></h3>
              <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

               <?php

              $qry = $fieldsByGroup;

              //var_dump($qry);

                if (count($qry) > 0) { ?>
                  <div class="table-responsive">
                    <!-- <table class="table table-striped dataTable no-footer" id="pages_dt"> -->
                    <table class="table table-striped dataTable no-footer">
                      <caption>Recent testing scores by project</caption>
                      <thead>
                        <tr>
                          <th data-i18n="ux_projects" scope="col">UX projects</th>
                          <th data-i18n="" scope="col">Test</th>
                          <th data-i18n="date" scope="col">Date</th>
                          <!-- <th data-i18n="avg_success_rate" scope="col">Average success rate</th> -->
                          <th data-i18n="" scope="col">Score</th>
                          <th data-i18n="" scope="col">Participants</th>
                        </tr>
                      </thead>
                      <tbody>
                    <?php foreach ($qry as $row) { ?>
                        <tr>
                          <td><?=$row[0]['UX Research Project Title'];?></td>
                          <!-- <td><span><?//=array_key_exists('COPS', $row[0]) ? "COPS" : "N/A";     //echo ($row[0]['COPS']==1) ? "COPS" : "N/A";           //$row[0]['COPS'];?></span></td> -->
                          <td><span><?=array_key_exists('Test Type', $row[0]) ? $row[0]["Test Type"] : "N/A";     //echo ($row[0]['COPS']==1) ? "COPS" : "N/A";           //$row[0]['COPS'];?></span></td>
                          <td><span><?=array_key_exists('Date', $row[0]) ? date('m/Y',strtotime($row[0]['Date'])) : "";             //$row[0]['Date'];?></span></td>
                          <td><span><?=round((array_sum(array_column_recursive($row, "Success Rate"))/count(array_column_recursive($row, "Success Rate")))*100)."%";?></span></td>
                          <td><span><?=array_sum(array_column_recursive($row, "# of Users"));?></span></td>
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
