 <?php


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
?>