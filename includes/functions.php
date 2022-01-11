<?php
use Google\Service\Webmasters\ApiDimensionFilterGroup;
use Google\Service\Webmasters\SearchAnalyticsQueryRequest;
use Google\Service\Webmasters\ApiDimensionFilter;

     /**
* Initializes an Analytics Reporting API V4 service object.
*
* @return An authorized Analytics Reporting API V4 service object.
*/
function initializeAnalytics()
{

 // Use the developers console and download your service account
 // credentials in JSON format. Place them in this directory or
 // change the key file location if necessary.
 $KEY_FILE_LOCATION = './php/service-account-credentials.json';

 // Create and configure a new client object.
 $client = new Google_Client();
 $client->setAuthConfig($KEY_FILE_LOCATION);
 $client->setScopes(['https://www.googleapis.com/auth/webmasters.readonly']);

 return $client;
}


/**
* Queries the Analytics Reporting API V4.
*
* @param service An authorized Analytics Reporting API V4 service object.
* @return The Analytics Reporting API V4 response.
*/
function getReport( $start, $end, $results, $url, $t ) {

 global $data;
 $json = $data[$t];
 if ( $t == "ovrvw-smmry-totals" || $t == "ovrvw-smmry-qryAll" ) {
   $json = sprintf($json, $start, $end, $results);
 } else {
   $json = sprintf($json, $start, $end, $url, $results);
 }
 $array = json_decode( $json, true);

 return new Google_Service_Webmasters_SearchAnalyticsQueryRequest( $array );

}


/**
* Parses and prints the Analytics Reporting API V4 response.
*
* @param An Analytics Reporting API V4 response.
*/
function printResults($client, $q, $t) {

 try {

      $service = new Google_Service_Webmasters($client);
      $u = $service->searchanalytics->query('https://www.canada.ca/', $q);

      return json_encode($u);

    } catch(\Exception $e ) {
       echo $e->getMessage();
    }

}

// Recursive version of Airtable PHP client's getContent() method
// Including some built-in friendly debugging
function getContentRecursive($db_inventory, $table_name, $filters = []) {

 // Fetch the first response
 $response = $db_inventory->getContent($table_name, $filters)->getResponse();

 // If there's an error, show it and return an empty array.
 if ($response->error) {
   var_dump($response->error->type.': '.$response->error->message);
   return [];
 }

 $content = $response->records;

 return $content;
}

function parseJSON ( $content, $array ) {
 $cnt = count($array);
 $records = [];

 //echo '<BR /><br />CONTENT: <br /><br />';
 //var_dump( $content );

 foreach ($content as $key => $val) {

   //var_dump("VALUE: " . $val);
   if ( $cnt == 1 ) {
     $a = $val->{$array[0]};
   } else if ( $cnt == 2 ) {
     $a = $val->{$array[0]}->{$array[1]};
   } else if ( $cnt == 3 ) {
     $a = $val->{$array[0]}->{$array[1]}->{$array[2]};
   }

   if ( is_array($a) ) {
       $a = implode($a, '');
     }

   array_push($records, $a);
 }

 return $records;
}

function parseJSON2 ( $content, $array ) {
 $cnt = count($array);
 $records = [];

 //echo '<BR /><br />CONTENT: <br /><br />';
 //var_dump( $content );

 if ( is_array( $array[1] ) ) {

 foreach ($content as $key => $val) {
   $temp = [];
     foreach ( $array[1] as $arr ) {
       $a = $val->{$array[0]}->{$arr};
       $temp[$arr] = $a;
     }
     $records[] = $temp;
   }
 } /* else {
   //var_dump("VALUE: " . $val);
   if ( $cnt == 1 ) {
     $a = $val->{$array[0]};
   } else if ( $cnt == 2 ) {
     $a = $val->{$array[0]}->{$array[1]};
   } else if ( $cnt == 3 ) {
     $a = $val->{$array[0]}->{$array[1]}->{$array[2]};
   }

   if ( is_array($a) ) {
       $a = implode($a, '');
     }

     array_push($records, $a);
   }
 }
 */

 return $records;
}


/* FUNCTIONS - from PAGES */

/* **************************************  */
// Flatten array
/* **************************************  */
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

/* **************************************  */
// Group By - group assocative array by some key
/* **************************************  */
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



/* **************************************  */
// Single array
/* **************************************  */
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



/* **************************************  */
// Array Column Recursive
/* **************************************  */
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



/* **************************************  */
// Implode recursive
/* **************************************  */
function implode_recursive($g, $p) {
 return is_array($p) ? implode($g, array_map(__FUNCTION__, array_fill(0, count($p) , $g) , $p)) : $p;
}


/* **************************************  */
// Get monthly ranges from date interval
/* **************************************  */

// Function to split date ranges into monthly ranges
// this is useful to get the Call Centre data
function getMonthRanges($start, $end) {
   $timeStart = strtotime($start);
   $timeEnd   = strtotime($end);
   $out       = [];

   $milestones[] = $timeStart;
   $timeEndMonth = strtotime('first day of next month midnight', $timeStart);
   while ($timeEndMonth < $timeEnd) {
       $milestones[] = $timeEndMonth;
       $timeEndMonth = strtotime('+1 month', $timeEndMonth);
   }
   $milestones[] = $timeEnd;

   $count = count($milestones);

   // for ($i = 0; $i < $count; $i++) {
   //     echo date('Y-m-d', $milestones[$i]);
   //     echo "<br>";
   // }

   for ($i = 1; $i < $count; $i++) {
       if ($i == ($count-1)) {
           $out[] = [
               'start' => date('Y-m-d', $milestones[$i - 1]), // Here you can apply your formatting (like "date('Y-m-d H:i:s', $milestones[$i-1])") if you don't won't want just timestamp
               'end'   => date('Y-m-d', $milestones[$i])
           ];
       }
       else {
         $out[] = [
             'start' => date('Y-m-d', $milestones[$i - 1]), // Here you can apply your formatting (like "date('Y-m-d H:i:s', $milestones[$i-1])") if you don't won't want just timestamp
             'end'   => date('Y-m-d', $milestones[$i] - 1)
         ];
       }
   }

   return $out;
}

/* **************************************  */
// Get Page Title from URL
/* **************************************  */
function getSiteTitle( $url ){
   $doc = new DOMDocument();
   @$doc->loadHTML(file_get_contents($url));
   $title = $doc->getElementsByTagName('title')->item(0)->nodeValue;

   $pageTitle = trim(substr($title, 0, -12));

   return $pageTitle;
}


class SearchConsoleQueryBuilder
{
    private SearchAnalyticsQueryRequest $queryRequest;
    private array $filterGroups;
    private ApiDimensionFilterGroup $filterGroup;
    private array $filterGroupFilters;
    private ApiDimensionFilter $urlFilter;

    public function __construct($requestParams = array()) {
        $this->queryRequest = new SearchAnalyticsQueryRequest($requestParams);
        $this->filterGroup = new ApiDimensionFilterGroup();
        $this->filterGroups = array($this->filterGroup);
        $this->filterGroupFilters = array();
        $this->urlFilter = new ApiDimensionFilter();

        $this->queryRequest->setSearchType('web');

        // default params
        if (!array_key_exists('rowLimit', $requestParams)) {
            $this->setRowLimit(15);
        }

        if (!array_key_exists('dimensions', $requestParams)) {
            $this->setDimensions(['query']);
        }
    }

    public function setStartDate($startDate) {
        $this->queryRequest->setStartDate($startDate);
        return $this;
    }

    public function setEndDate($startDate) {
        $this->queryRequest->setEndDate($startDate);
        return $this;
    }

    public function setDimensions($dimensions) {
        $this->queryRequest->setDimensions($dimensions);
        return $this;
    }

    public function setRowLimit($rowLimit) {
        $this->queryRequest->setRowLimit($rowLimit);
        return $this;
    }

    public function setAggregationType($aggregationType) {
        $this->queryRequest->setAggregationType($aggregationType);
        return $this;
    }

    public function addDeviceFilter($device, $operator = 'equals') {
        $filter = new Google\Service\Webmasters\ApiDimensionFilter();

        $filter->setDimension('device');
        $filter->setOperator($operator);
        $filter->setExpression($device);

        $this->filterGroupFilters[] = $filter;

        return $this;
    }
    public function setUrlFilter($url, $operator = 'equals') {
        $this->urlFilter->setDimension('page');
        $this->urlFilter->setOperator($operator);
        $this->urlFilter->setExpression($url);

        return $this;
    }

    public function addFilter($dimension, $operator, $expression) {
        $filter = new Google\Service\Webmasters\ApiDimensionFilter();

        $filter->setDimension($dimension);
        $filter->setOperator($operator);
        $filter->setExpression($expression);

        $this->filterGroupFilters[] = $filter;

        return $filter;
    }


    public function build(): SearchAnalyticsQueryRequest {
        // a little funky, but this is to avoid mutating the filterGroupFilters array, that way we can keep the
        //      internal state while still being able to change the url.
        $this->filterGroup->setFilters(array_merge($this->filterGroupFilters, array($this->urlFilter)));
        $this->queryRequest->setDimensionFilterGroups($this->filterGroups);

        return $this->queryRequest;
    }
}

class SearchConsoleInterface
{
    private Google\Client $client;
    private Google\Service\Webmasters $service;
    private Google\Http\Batch $batch;
    private SearchConsoleQueryBuilder $queryBuilder;

    public function __construct() {
        // Use the developers console and download your service account
        // credentials in JSON format. Place them in this directory or
        // change the key file location if necessary.
        $KEY_FILE_LOCATION = './php/service-account-credentials.json';

        // Create and configure a new client object.
        $this->client = new Google\Client();
        $this->client->setAuthConfig($KEY_FILE_LOCATION);
        $this->client->setScopes(['https://www.googleapis.com/auth/webmasters.readonly']);

        $this->service = new Google\Service\Webmasters($this->client);
        $this->queryBuilder = new SearchConsoleQueryBuilder();

        $this->batch = $this->service->createBatch();
    }

    public function queryConfig(): SearchConsoleQueryBuilder
    {
        return $this->queryBuilder;
    }

    public function clearQuery() {
        $this->queryBuilder = new SearchConsoleQueryBuilder();
    }

    public function executeBatchQuery($urls = array()): ?array
    {
        $this->client->setUseBatch(true);
        foreach ($urls as $url) {
            $queryRequest = $this->queryBuilder->setUrlFilter($url)->build();
            $request = $this->service->searchanalytics->query('https://www.canada.ca/', $queryRequest);
            $this->batch->add($request, $url);
        }

        $results = $this->batch->execute();
        $this->client->setUseBatch(false);

        return $results;
    }

    public function executeQuery() {
        $queryRequest = $this->queryBuilder->build();

        return $this->service->searchanalytics->query('https://www.canada.ca/', $queryRequest);
    }

    public function getBatch(): \Google\Http\Batch
    {
        return $this->batch;
    }
}

?>
