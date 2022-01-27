
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
// function getSiteTitle( $url ){
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

//echo $url;
$sUrl = substr($url, 8, strlen($url));
//echo $sUrl;

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
        <li <?php if ($tab=="summary") {echo "class='is-active'";} ?>><a href="./pages_summary.php?url=<?=$url?>" data-i18n="tab-summary">Summary</a></li>
        <li <?php if ($tab=="webtraffic") {echo "class='is-active'";} ?>><a href="./pages_webtraffic.php?url=<?=$url?>" data-i18n="tab-webtraffic">Web traffic</a></li>
        <li <?php if ($tab=="searchanalytics") {echo "class='is-active'";} ?>><a href="./pages_searchanalytics.php?url=<?=$url?>" data-i18n="tab-searchanalytics">Search analytics</a></li>
        <li <?php if ($tab=="pagefeedback") {echo "class='is-active'";} ?>><a href="#" data-i18n="tab-pagefeedback">Page feedback</a></li>
      </ul>
    </div>

 <?php


// if ($url == null) {
// 	$url = "https://www.canada.ca/en/revenue-agency/services/benefits/recovery-benefit/crb-how-apply.html";
// }

//$url_components = parse_url($url);
// echo "<pre>";
// print_r($url_components);
// echo "</pre>";
// echo $url;
// echo "<br>".$dr;
// echo "<br>".$lang;

 // Use parse_str() function to parse the string passed via URL
//parse_str($url_components['query'], $params);

// Display result
// echo "<pre>";
// print_r($params);
// echo "</pre>";

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
        <button type="button" class="btn bg-white border border-1 dropdown-toggle" id="range-button" data-bs-toggle="dropdown" aria-expanded="false"><span class="material-icons align-top">calendar_today</span> <span data-i18n="dr-lastweek">Last week</span></button>
            <span class="text-secondary ps-2 text-nowrap dates-header-week"><strong><?=$datesHeader[1][0] ?> - <?=$datesHeader[1][1] ?></strong></span>
            <span class="text-secondary ps-2 text-nowrap dates-header-week" data-i18n="compared_to">compared to</span>
            <span class="text-secondary ps-2 text-nowrap dates-header-week"><strong><?=$datesHeader[0][0] ?> - <?=$datesHeader[0][1] ?></strong></span>

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

        //echo "URLS is ".$urls;

        $r = new ApiClient($config[0]['ADOBE_API_KEY'], $config[0]['COMPANY_ID'], $_SESSION['token']);

        $temp = ['aa-pages-smmry-metrics', 'aa-pages-smmry-fwylf', 'aa-ovrvw-smmry-trnd', 'aa-ovrvw-smmry-tsks']; //, 'fwylf' ];
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
//            $result[] = $r->requestEntity($json);
            $result[] = get_aa_data($json, $r);
            $j[] = $json;

        }

        //echo var_dump($result[0]);
        foreach ($result as $r)
        {

        }

        $res = json_decode($result[0], true);
        $metrics = $res["summaryData"]["filteredTotals"];

        $tmp = array_filter(array_slice($metrics, 0, 8));


        //DOES THIS PAGE HAS PAGE FEEDBACK TOOL OR NOT
        if (empty($tmp)) {
          echo "This page doesn't have a Page feedback tool!";
        }
        else {

                // echo "<pre>";
                // print_r($metrics);
                // echo "</pre>";

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

                //echo $url;
                $params = array(
                    //"filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')))",
                    //"filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')), (URL = 'https://www.canada.ca/en/revenue-agency/services/benefits/recovery-benefit/crb-how-apply.html'))",
                    "filterByFormula" => "AND(IS_AFTER({Date}, DATEADD('$s',-1,'days')), IS_BEFORE({Date}, DATEADD('$e1',1,'days')), (URL = '$url'))",
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

                // echo "ALL DATA<pre>";
                // print_r($allData);
                // echo "</pre>";

                // if there's data (record exist)
                if ( count( $allData ) > 0 ) {
                      // do things here
                      // echo "total pagefedbacks for this page: ";
                      // echo count($allData);
                      $re = $allData;
                      // echo "<pre>";
                      // print_r($re);
                      // echo "</pre>";

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

                            // echo count($all_fields);
                            // echo "<br><br><pre>";
                            // print_r($all_fields);
                            // echo "</pre><br></br>";


                            $fieldsByGroupTag = group_by('Tag', $all_fields);
                            $fieldsByGroupTagPW = group_by('Tag', $all_fieldsPW);

                            //

                            foreach ( $fieldsByGroupTagPW as &$item ) {
                              $item["Total tag comments"] = count($item);
                            }
                            foreach ( $fieldsByGroupTag as &$item ) {
                              $item["Total tag comments"] = count($item);
                            }

                            // echo count($fieldsByGroupTag);
                            // echo "<br><br><pre>";
                            // print_r($fieldsByGroupTag);
                            // echo "</pre><br></br>";
                            $d3TotalFeedbackByPageSuccess = 1;

                      } //if (( count( $WeeklyData ) > 0 ) && ( count( $PWeeklyData ) > 0 ))


                } //if count($allData) > 0

                else {
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
                                <caption></caption>
                                <thead>
                                  <th scope="col">Metrics</th>
                                  <th scope="col">Previous Month</th>
                                  <th scope="col">Month</th>
                                  <th scope="col">Previous Week</th>
                                  <th scope="col">Week</th>
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
                              <caption></caption>
                              <thead>
                                <th scope="col">Metrics</th>
                                <th scope="col">Previous Month</th>
                                <th scope="col">Month</th>
                                <th scope="col">Previous Week</th>
                                <th scope="col">Week</th>
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
                    <h3 class="card-title"><span class="card-tooltip h6" data-bs-toggle="popover" data-bs-trigger="hover focus" data-bs-placement="right" data-bs-content="" data-bs-original-title="" title="" data-i18n="">Total feedback by page</span></h3>

                    <div class="card-body pt-2" id="d3_tcbil"></div>
                    <div id="d3_www_legend3"></div>
                    <!-- Total calls by Enquiry_line D3 bar chart  -->

                    <?php
                      if ( $d3TotalFeedbackByPageSuccess == 0 ) { ?>

                          <div class="no-data">
                              <p>No Page feedback comments/tags for selected date range</p>
                          </div>

                      <?php
                      }
                      else {
                              // echo "--------------------------------------<br><br><pre>";
                              // print_r($el); //Prev WEEK
                              // echo "</pre><br></br>";

                              //USE THIS - THESE ARE WELL DEFINED DATE RANGES
                              //-------------------------------------------------------
                              //$d3DateRanges = array($datesHeaderMonth[0][0].'-'.$datesHeaderMonth[0][1],$datesHeaderMonth[1][0].'-'.$datesHeaderMonth[1][1],$datesHeader[0][0].'-'.$datesHeader[0][1],$datesHeader[1][0].'-'.$datesHeader[1][1]); // previous $a1
                              //$groups = json_encode(array($d3DateRanges[2],$d3DateRanges[3]));
                              //----------------------------------------------------


                              //$d3DateRanges = array($s.'-'.$e,$s1.'-'.$e1); // previous $a1

                              //$d3DateRanges = array("June 06-12", "June 13-19");

                              // $s = $startLastGSC;
                              // $e = $endLastGSC;
                              // $s1 = $startGSC;
                              // $e1 = $endGSC;

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
                                            $final_tag_array[$el[$k]] = $fieldsByGroupTagPW[$el[$k]]["Total tag comments"];
                                          }
                                      }
                                      else {
                                        for ($k = 0; $k < count($el); ++$k) {
                                          $final_tag_array[$el[$k]] = $fieldsByGroupTag[$el[$k]]["Total tag comments"];
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
                                        <td><?=number_format($fieldsByGroupTagPW[$row]["Total tag comments"]) ?></td>
                                        <td><?=number_format($fieldsByGroupTag[$row]["Total tag comments"]) ?></td>
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

            <a name="comments"></a>
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
                                       <th data-i18n="" scope="col">Comment</th>
                                     </tr>
                                   </thead>
                                   <tbody>
                                       <?php foreach ($qry as $row) { ?>
                                           <tr>
                                             <td><?=array_key_exists('Date', $row) ? $row['Date'] : "";?></td>
                                             <td><?=array_key_exists('Tag', $row) ? $row['Tag'] : "";?></td>
                                             <td><?=array_key_exists("What's wrong", $row) ? $row["What's wrong"] : "";?></td>
                                             <td><?//=array_key_exists('Comment', $row) ? $row['Comment'] : "";?></td>
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

      <?php  } //if (empty($tmp))  ?>
<!--Main content end-->
<?php include "includes/upd_footer.php"; ?>
