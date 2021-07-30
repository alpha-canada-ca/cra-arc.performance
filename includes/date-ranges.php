<?php

  $startDate_lastweek = date("Y-m-d",strtotime("last week monday")-1); //echo "Start Date Last week: ".$startDate_lastweek;
  $endDate_lastweek = date("Y-m-d",strtotime("last sunday")-1); //echo "End Date Last week: ".$endDate_lastweek;

  $startDate_last2weeks = date("Y-m-d",strtotime("-3 week monday")-1); //echo "Start Date 2 weeks ago: ".$startDate_last2weeks;
  $endDate_last2weeks = date("Y-m-d",strtotime("-2 week sunday")-1); //echo "End Date 2 weeks ago: ".$endDate_last2weeks;

  $startDate_lastmonth = date("Y-m-d",strtotime("first day of last month")); //echo "Start Date Last month: ".$startDate_lastmonth;
  $endDate_lastmonth = date("Y-m-d",strtotime("last day of last month")); //echo "End Date Last month: ".$endDate_lastmonth;

  $startDate_last2months = date("Y-m-d",strtotime("first day of -2 month")); //echo "Start Date 2 months ago: ".$startDate_last2months;
  $endDate_last2months = date("Y-m-d",strtotime("last day of -2 month")); //echo "End Date 2 months ago: ".$endDate_last2months;

?>
