
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

    <h2 class="h3 pt-2 pb-2" data-i18n="menu-tasks">Tasks</h2>


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
              $table = 'Tasks';

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


                    // Get just the ['fields'] array of each record -  as a separate array - $all_fields
                    $all_fields = array_column_recursive($re, 'fields');
              }

          ?>

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
            <!-- <h3 class="card-title"><span class="h6" data-i18n="">List of all tasks</span></h3> -->
              <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

               <?php

              $qry = $all_fields;

              //var_dump($qry);

                if (count($qry) > 0) { ?>
                  <div class="table-responsive">
                    <table class="table table-striped dataTable no-footer" id="pages_dt">
                      <thead>
                        <tr>
                          <th data-i18n="">Task</th>
                          <th data-i18n="">Category</th>
                          <th data-i18n="">Sub-category</th>
                          <th data-i18n="">Task survey</th>
                          <th data-i18n="">Visits</th>
                          <th data-i18n="">Calls</th>
                          <!-- <th data-i18n="avg_success_rate">Average success rate</th> -->
                        </tr>
                      </thead>
                      <tbody>
                    <?php foreach ($qry as $row) { ?>
                        <tr>
                          <td><?=$row['Task'];?></td>
                          <td><span class="badge rounded-pill bg-primary"><?=array_key_exists('Topic', $row) ? $row['Topic'] : "";?></span></td>
                          <td><span class="badge rounded-pill bg-primary"><?=array_key_exists('Sub Topic', $row) ? $row['Sub Topic'] : "";?></span></td>
                          <td></td>
                          <td></td>
                          <td></td>
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
