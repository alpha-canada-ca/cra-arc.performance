
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
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href=# data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-pagefeedback">Page feedback</a></li>
        <li <?php if ($tab=="calldrivers") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-calldrivers">Call drivers</a></li>
        <li <?php if ($tab=="uxtests") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-uxtests">UX tests</a></li>
      </ul>
    </div>

      <?php

        ini_set('display_errors', 1);
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
            // echo "<pre>";
            // print_r($datesHeader);
            // echo "</pre>";
            //
            // echo "<pre>";
            // print_r($datesHeaderMonth);
            // echo "</pre>";

        }

        ?>

    <!-- Dropdown - date range   -->
    <div class="row mb-4 mt-1">
      <div class="dropdown">
        <button type="button" class="btn bg-white border border-1" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> <span data-i18n="dr-alltime">All time</span></button> <span class="text-secondary ps-2 text-nowrap" data-i18n="dr-alltime"> All time</span>

        <!-- <ul class="dropdown-menu" aria-labelledby="range-button" style="">
          <li><a class="dropdown-item active" href="#" aria-current="true" data-i18n="dr-lastweek">Last week</a></li>
          <li><a class="dropdown-item" href="#" data-i18n="dr-lastmonth">Last month</a></li>
        </ul> -->

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
        //echo var_dump($res);
        $metrics = $res["summaryData"]["filteredTotals"];
        //echo var_dump($metrics);

        $res2 = json_decode($result[1], true);
        $metrics2 = $res2["summaryData"]["filteredTotals"];
        //echo var_dump($metrics2);

        $aaResultTrend = json_decode($result[2], true);
        $aaMetricsTrend = $aaResultTrend["rows"];

        $aaTrendWeeks = array_slice($aaMetricsTrend, -14);
        $aaTrendLastWeek = array_slice($aaTrendWeeks, 0, 7);
        $aaTrendWeek = array_slice($aaTrendWeeks, -7);

        $aaTasks = json_decode($result[3], true);
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

        $diff = differ($metrics[$visitors + 2], $metrics[$visitors + 3]);
        $pos = posOrNeg($diff);
        $pieces = explode(":", $pos);

        $diff = abs($diff);

        $fwylfICantFindTheInfo = 0;
        $fwylfOtherReason = 4;
        $fwylfInfoHardToUnderstand = 8;
        $fwylfError = 12;
    ?>



      <?php

              function implode_recursive($g, $p)
              {
              return is_array($p) ? implode($g, array_map(__FUNCTION__, array_fill(0, count($p) , $g) , $p)) : $p;
              }

              $config = include ('./php/config-at.php');

              $start2 = microtime(true);

              $airtable = new Airtable($config);

              //var_dump($taskArray);
              // Tasks in AirTable
              // $params = array(
              //
              // //print_r($params);
              // $table = 'User testing';

              // $request = getContentRecursive($airtable, $table, $params);
              // $lo = ['fields', ['Task', 'Tasks']];
              //
              // $con = parseJSON2($request, $lo);

              //echo "<br /><br /> Connection Main: ";
              //var_dump ( $con );
              // Enquiry Lines in AirTable
              $params = array("filterByFormula" => 'LEN({Test Type})>0'); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
              //print_r($params);
              $table = 'User testing';

              $fullArray = [];
              $request = $airtable->getContent($table, $params);
              //do
              //{
              $response = $request->getResponse();

              $re = ( json_decode( $response, true ) )['records'];

              // if there's data (record exist)
              if ( count( $re ) > 0 ) {

                //var_dump($re);
                //echo "total test completed: ". count($re)."<br>";
                // //$financialyeardate = (date('m')<'04') ? date('Y-04-01',strtotime('-1 year')) : date('Y-04-01');
                // $prev_financialyear_startdate = (date('m')<'04') ? date('Y-04-01',strtotime('-2 years')) : date('Y-04-01',strtotime('-1 year'));
                // $prev_financialyear_enddate = (date('m')<'03') ? date('Y-03-31',strtotime('-1 years')) : date('Y-03-31');
                // //echo "$prev_financialyear_startdate - $prev_financialyear_enddate";


                // echo "<br><br><pre>";
                // print_r($re); //Prev WEEK
                // echo "</pre><br></br>";


                // function array_column_recursive(array $haystack, $needle) {
                //   $found = [];
                //   array_walk_recursive($haystack, function($value, $key) use (&$found, $needle) {
                //       if ($key == $needle)
                //           $found[] = $value;
                //   });
                //   return $found;
                // }
                //
                //
                // $registeredOnArray = array_column_recursive($re, '# of Users');
                // echo "<br><br><pre>";
                // print_r($registeredOnArray); //Prev WEEK
                // echo "</pre><br></br>";
                // //var_export($registeredOnArray);

                if ( ! function_exists( 'array_column_recursive' ) ) {
                    /**
                     * Returns the values recursively from columns of the input array, identified by
                     * the $columnKey.
                     *
                     * Optionally, you may provide an $indexKey to index the values in the returned
                     * array by the values from the $indexKey column in the input array.
                     *
                     * @param array $input     A multi-dimensional array (record set) from which to pull
                     *                         a column of values.
                     * @param mixed $columnKey The column of values to return. This value may be the
                     *                         integer key of the column you wish to retrieve, or it
                     *                         may be the string key name for an associative array.
                     * @param mixed $indexKey  (Optional.) The column to use as the index/keys for
                     *                         the returned array. This value may be the integer key
                     *                         of the column, or it may be the string key name.
                     *
                     * @return array
                     */
                    function array_column_recursive( $input = NULL, $columnKey = NULL, $indexKey = NULL ) {

                      // Using func_get_args() in order to check for proper number of
                      // parameters and trigger errors exactly as the built-in array_column()
                      // does in PHP 5.5.
                      $argc   = func_num_args();
                      $params = func_get_args();
                      if ( $argc < 2 ) {
                        trigger_error( "array_column_recursive() expects at least 2 parameters, {$argc} given", E_USER_WARNING );

                        return NULL;
                      }
                      if ( ! is_array( $params[ 0 ] ) ) {
                        // Because we call back to this function, check if call was made by self to
                        // prevent debug/error output for recursiveness :)
                        $callers = debug_backtrace();
                        if ( $callers[ 1 ][ 'function' ] != 'array_column_recursive' ){
                          trigger_error( 'array_column_recursive() expects parameter 1 to be array, ' . gettype( $params[ 0 ] ) . ' given', E_USER_WARNING );
                        }

                        return NULL;
                      }
                      if ( ! is_int( $params[ 1 ] )
                           && ! is_float( $params[ 1 ] )
                           && ! is_string( $params[ 1 ] )
                           && $params[ 1 ] !== NULL
                           && ! ( is_object( $params[ 1 ] ) && method_exists( $params[ 1 ], '__toString' ) )
                      ) {
                        trigger_error( 'array_column_recursive(): The column key should be either a string or an integer', E_USER_WARNING );

                        return FALSE;
                      }
                      if ( isset( $params[ 2 ] )
                           && ! is_int( $params[ 2 ] )
                           && ! is_float( $params[ 2 ] )
                           && ! is_string( $params[ 2 ] )
                           && ! ( is_object( $params[ 2 ] ) && method_exists( $params[ 2 ], '__toString' ) )
                      ) {
                        trigger_error( 'array_column_recursive(): The index key should be either a string or an integer', E_USER_WARNING );

                        return FALSE;
                      }
                      $paramsInput     = $params[ 0 ];
                      $paramsColumnKey = ( $params[ 1 ] !== NULL ) ? (string) $params[ 1 ] : NULL;
                      $paramsIndexKey  = NULL;
                      if ( isset( $params[ 2 ] ) ) {
                        if ( is_float( $params[ 2 ] ) || is_int( $params[ 2 ] ) ) {
                          $paramsIndexKey = (int) $params[ 2 ];
                        } else {
                          $paramsIndexKey = (string) $params[ 2 ];
                        }
                      }
                      $resultArray = array();
                      foreach ( $paramsInput as $row ) {
                        $key    = $value = NULL;
                        $keySet = $valueSet = FALSE;
                        if ( $paramsIndexKey !== NULL && is_array( $row ) && array_key_exists( $paramsIndexKey, $row ) ) {
                          $keySet = TRUE;
                          $key    = (string) $row[ $paramsIndexKey ];
                        }
                        if ( $paramsColumnKey === NULL ) {
                          $valueSet = TRUE;
                          $value    = $row;
                        } elseif ( is_array( $row ) && array_key_exists( $paramsColumnKey, $row ) ) {
                          $valueSet = TRUE;
                          $value    = $row[ $paramsColumnKey ];
                        }

                        // $possibleValue = array_column_recursive( $row, $paramsColumnKey, $paramsIndexKey );
                        // if ( $possibleValue ) {
                        //   $resultArray = array_merge( $possibleValue, $resultArray );
                        // }
                        if (is_array( $row )) {
                          $possibleValue = array_column_recursive( $row, $paramsColumnKey, $paramsIndexKey );
                          if ( $possibleValue ) {
                            $resultArray = array_merge( $possibleValue, $resultArray );
                          }
                        }

                        if ( $valueSet ) {
                          if ( $keySet ) {
                            $resultArray[ $key ] = $value;
                          } else {
                            $resultArray[ ] = $value;
                          }
                        }
                      }

                      return $resultArray;
                    }
                  }



                    function single_array($arr){
                        foreach($arr as $key){
                            if(is_array($key)){
                                $arr1=single_array($key);
                                foreach($arr1 as $k){
                                    $new_arr[]=$k;
                                }
                            }
                            else{
                                $new_arr[]=$key;
                            }
                        }
                        return $new_arr;
                    }
                    //


                     /**
                      * Function that groups an array of associative arrays by some key.
                      *
                      * @param {String} $key Property to sort by.
                      * @param {Array} $data Array that stores multiple associative arrays.
                      */
                     function group_by($key, $data) {
                         $result = array();

                         foreach($data as $val) {
                             if(array_key_exists($key, $val)){
                                 $result[$val[$key]][] = $val;
                             }else{
                                 $result[""][] = $val;
                             }
                         }

                         return $result;

                     }


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
                    //echo "<br><br><pre>";
                    //print_r($all_dates); //Prev WEEK
                    //print_r(array_unique(single_array($total_tasks)));
                    //echo "</pre><br></br>";

                    //echo strtotime("2011/05/21");
                    //echo date('Y-m-d',strtotime($all_dates[0]));
                    //echo "Total of unique Tasks:". count(array_unique(single_array($total_tasks)));


                    function array_flatten($array = null) {
                        $result = array();

                        if (!is_array($array)) {
                            $array = func_get_args();
                        }

                        foreach ($array as $key => $value) {
                            if (is_array($value)) {
                                $result = array_merge($result, array_flatten($value));
                            } else {
                                $result = array_merge($result, array($key => $value));
                            }
                        }

                        return $result;
                    }

                    //echo "<br><br><pre>";
                    //print_r(array_flatten($total_tasks)); //Prev WEEK
                    //print_r(array_unique(single_array($total_tasks)));
                    //echo "</pre><br></br>";

                    //var_export($registeredOnArray);


                    // Get just the ['fields'] array of each record -  as a separate array - $all_fields
                    $all_fields = array_column_recursive($re, 'fields');

                    //Sort all_fields array by Date key in descending order
                    // if we need an ascernding order, swap the $a and $b variable as function arguments
                    usort($all_fields, function($b, $a) {
                       return new DateTime($a['Date']) <=> new DateTime($b['Date']);
                     });

                     // group the all_fields by "UX Research Project Title" key
                    $fieldsByGroup = group_by('UX Research Project Title', $all_fields);

                    //
                    // echo "<br><br><pre>";
                    // print_r($fieldsByGroup); //Prev WEEK
                    // echo "</pre><br></br>";



                     // echo "--------------------------------------<br><br><pre>";
                     // print_r($fieldsByGroup); //Prev WEEK
                     // echo "</pre><br></br>";

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

                     //echo "$qStart - $qEnd <br/>";

                     // echo "$q1Start - $q1End <br/>";
                     // echo "$q2Start - $q2End <br/>";
                     // echo "$q3Start - $q3End <br/>";
                     // echo "$q4Start - $q4End <br/>";

                     //echo "current quarter is Q$yearQuarter of " . date("Y");

                     // $t1 = array_filter($all_fields, function ($val)  {
                     //     return $val['Project Lead'] == "Shaun Dhani";
                     //   });

                     //<!-- Working example -->
                     // $s1 = date("Y-m-d", strtotime("2020-01-01"));
                     // $e1 = date("Y-m-d", strtotime("2020-06-01"));
                     // echo "<br>";
                     // echo "$s1 - $e1";
                     // $t1 = array_filter($all_fields, function ($val) use ($s1,$e1) {
                     //     return (date("Y-m-d", strtotime($val["Date"])) >= $s1 && date("Y-m-d", strtotime($val["Date"])) <= $e1);
                     //   });

                     $projectsLastFiscal = count(array_filter($all_fields, function ($val) use ($prev_financialyear_startdate, $prev_financialyear_enddate) {
                        return (date("Y-m-d", strtotime($val["Date"])) >= $prev_financialyear_startdate && date("Y-m-d", strtotime($val["Date"])) <= $prev_financialyear_enddate);
                        //$value["Date"] = strtotime($value["Date"]);
                        //return ($value["Date"] >= $startdate1 && $value["Date"] <= $enddate1);
                     }));


                     $projectsLastQuarter = count(array_filter($all_fields, function ($val) use ($qStart, $qEnd) {
                        return (date("Y-m-d", strtotime($val["Date"])) >= $qStart && date("Y-m-d", strtotime($val["Date"])) <= $qEnd);
                        //$value["Date"] = strtotime($value["Date"]);
                        //return ($value["Date"] >= $startdate1 && $value["Date"] <= $enddate1);
                     }));
                     // echo "<br>";
                     // echo $all_fields[0]["Date"];
                     // echo "<br>";
                     // echo strtotime($all_fields[0]["Date"]);
                     // echo "<br>";
                     // echo count($projectsLastFiscal);
                     // echo "<br><br><pre>";
                     // print_r($projectsLastFiscal); //Prev WEEK
                     // echo "</pre><br></br>";

                      //echo $projectsLastFiscal; //Prev WEEK





              }
              //$fullArray = array_merge($fullArray, ($response->records));
              //$request = $response->$response;
              //
              // //var_dump($fullArray);
              // $m = ['fields', 'Equiry Line'];
              // $l = ['fields', 'Total Calls'];
              //
              // $con1 = parseJSON($fullArray, $l);
              // //var_dump($con1);
              // $con2 = parseJSON($fullArray, $m);
              // //var_dump($con2);
              // $arrFinal = array();
              // for ($i = 0;$i < count($con1) - 1;$i++)
              // {
              // if (isset($arrFinal[($con2[$i]) ])) $arrFinal[($con2[$i]) ] += $con1[$i];
              // else $arrFinal += array(
              // $con2[$i] => $con1[$i]
              // );
              // }
              // //var_dump($arrFinal);
              // $params = array(); // array( "filterByFormula" => 'SEARCH(Task, "'.implode($taskArray, ',').'") != ""');
              // //print_r($params);
              // $table = 'User Testing';
              //
              // $fullArray = [];
              // $request = $airtable->getContent($table, $params);
              // do
              // {
              // $response = $request->getResponse();
              // $fullArray = array_merge($fullArray, ($response->records));
              // }
              // while ($request = $response->next());
              //
              // //var_dump($fullArray);
              // $m = ['fields', '# of Users'];
              // $l = ['fields', 'Success Rate'];
              //
              // $con1 = parseJSON($fullArray, $m);
              // $con2 = parseJSON($fullArray, $l);
              //
              // $totalTasks = number_format(count($fullArray));
              // $avgSuccessRate = percent(array_sum($con2) / $totalTasks);
              // $sumNumUsers = number_format(array_sum($con1));

              //echo 'total tasks: ' . $totalTasks . "<br /><br />avg success rate: " . $avgSuccessUsers . '<br />br />sum of users: ' . $sumNumUsers;



          ?>


    <!-- 3 number charts -->
    <div class="row mb-3 gx-3">
       <div class="col-lg-4 col-md-6 col-sm-12">
         <div class="card">
           <div class="card-body card-pad pt-2">
             <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Number of all time UX test projects completed" data-i18n="tests_completed">UX test projects completed (All time)</span></h3>
               <div class="row">
                 <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=count($fieldsByGroup) ?></span><span class="small"></span></div>
                 <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span> </span></div>
             </div>
           </div>
         </div>
       </div>

       <div class="col-lg-4 col-md-6 col-sm-12">
         <div class="card">
           <div class="card-body card-pad pt-2">
             <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Number of tests completed from last fiscal year (Apr 01 - Mar 31)" data-i18n="tests_last_fiscal_year">Tests from last fiscal year</span><span class="card-tooltip h6"> (<?=date("Y M d", strtotime($prev_financialyear_startdate));?> - <?=date("Y M d",strtotime($prev_financialyear_enddate));?>)</span></h3>
               <div class="row">
                 <div class="col-md-8 col-sm-6"><span class="h3 text-nowrap"><?=$projectsLastFiscal ?></span><span class="small"></span></div>
                 <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span></span></div>
             </div>
           </div>
         </div>
       </div>

       <div class="col-lg-4 col-md-6 col-sm-12">
         <div class="card">
           <div class="card-body card-pad pt-2">
             <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Number of tests completed from last quarter" data-i18n="tests_last_quarter">Tests from last quarter</span><span class="card-tooltip h6"> (Q<?=$lastQuarter."/"?><?=($lastQuarter != 4) ? date('y') : date('y',strtotime('-1 years')); ?>)</span></h3>
               <div class="row">
                 <div class="col-sm-8"><span class="h3 text-nowrap"><?=$projectsLastQuarter ?></span><span class="small"></span></div>
                 <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span></span></div>
             </div>
           </div>
         </div>
       </div>
     </div>

    <!-- 2 number charts -->
    <div class="row mb-3 gx-3">
       <div class="col-lg-6 col-md-6 col-sm-12">
         <div class="card">
           <div class="card-body card-pad pt-2">
             <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Number of tasks tested" data-i18n="total_tasks_tested">Total tasks tested</span></h3>
               <div class="row">
                 <div class="col-lg-8 col-md-8 col-sm-8"><span class="h3 text-nowrap"><?=count($re); ?></span><span class="small"></span></div>
                 <div class="col-lg-4 col-md-4 col-sm-4 text-end"><span class="h3 text-nowrap"><span class="material-icons"></span> </span></div>
             </div>
           </div>
         </div>
       </div>
       <div class="col-lg-6 col-md-6 col-sm-12">
         <div class="card">
           <div class="card-body card-pad pt-2">
             <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="Number of participants in all tests" data-i18n="number_of_participants">Number of participants</span></h3>
               <div class="row">
                 <div class="col-md-8 col-sm-6"><span class="h3 text-nowrap"><?=$num_participants; ?></span></div>
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
            <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="top" data-bs-content="List of all UX test results, as an average success rate per project" data-bs-original-title="" title="" data-i18n="table_title_testing_results">UX test results by project</span></h3>
              <div id="toptask_wrapper" class="dataTables_wrapper dt-bootstrap5 no-footer"><div class="row"><div class="col-sm-12 col-md-6"></div><div class="col-sm-12 col-md-6"></div></div><div class="row"><div class="col-sm-12">

               <?php

              $qry = $fieldsByGroup;

              //var_dump($qry);

                if (count($qry) > 0) { ?>
                  <div class="table-responsive">
                    <table class="table table-striped dataTable no-footer">
                      <thead>
                        <tr>
                          <th data-i18n="ux_projects">UX projects</th>
                          <th data-i18n="type">Type</th>
                          <th data-i18n="date">Date</th>
                          <th data-i18n="avg_success_rate">Average success rate</th>
                        </tr>
                      </thead>
                      <tbody>
                    <?php foreach ($qry as $row) { ?>
                        <tr>
                          <td><?=$row[0]['UX Research Project Title'];?></td>
                          <td><span><?=array_key_exists('COPS', $row[0]) ? "COPS" : "N/A";     //echo ($row[0]['COPS']==1) ? "COPS" : "N/A";           //$row[0]['COPS'];?></span></td>
                          <td><span><?=array_key_exists('Date', $row[0]) ? date('m/Y',strtotime($row[0]['Date'])) : "";             //$row[0]['Date'];?></span></td>
                          <td><span><?=round((array_sum(array_column_recursive($row, "Success Rate"))/count(array_column_recursive($row, "Success Rate")))*100)."%";?></span></td>
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
