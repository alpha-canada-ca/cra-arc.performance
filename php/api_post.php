<?php

class ApiClient
{
    const CURL_TIMEOUT = 3600;
    const CONNECT_TIMEOUT = 30;
    const HOST = 'analytics.adobe.io/api/';

    /*
    const APIKEY = $config[0]['ADOBE_API_KEY'];
    const COMPANY = $config[0]['COMPANY_ID'];
    const TOKEN = $_SESSION['token'];
    */

    /** @var resource CURL handler. Reused every time for optimization purposes */
    private $ch;
    /** @var string URL for API. Calculated at creating object for optimization purposes */
    private $url;

    public function __construct( $apiKey, $company_id, $token )
    {
        $this->url = 'https://' . self::HOST . $company_id . '/reports';
                                // Micro-optimization: every concat operation takes several milliseconds
                                // But for millions sequential requests it can save a few seconds
        $host = [implode(':', [ // $host stores information for domain names resolving (like /etc/hosts file)
            self::HOST, // Host that will be stored in our "DNS-cache"
            443, // Default port for HTTPS, can be 80 for HTTP
            gethostbyname(self::HOST), // IPv4-address where to point our domain name (Host)
        ])];

        $headers = array(
           "Authorization: Bearer " . $token,
           "X-Api-Key: " . $apiKey,
           "X-Proxy-Global-Company-Id: " . $company_id,
           "Accept: application/json",
           "Content-Type: application/json",
        );

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_ENCODING, '');  // This will use server's gzip (compress data)
                                                       // Depends on server. On some servers can not work
        curl_setopt($this->ch, CURLOPT_RESOLVE, $host); // This will cut all requests for domain name resolving
        curl_setopt($this->ch, CURLOPT_TIMEOUT, self::CURL_TIMEOUT); // To not wait extra time if we know
                                                            // that api-call cannot be longer than CURL_TIMEOUT
        //curl_setopt($this->ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($this->ch, CURLOPT_CONNECTTIMEOUT, self::CONNECT_TIMEOUT); // Close connection if server doesn't response after CONNECT_TIMEOUT
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true); // To return output in `curl_exec`
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->ch, CURLOPT_URL, $this->url);
    }

    /** @throws \Exception */
    public function requestEntity( $data )
    {
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);

        $request = curl_exec($this->ch);

        if (curl_error($this->ch)) {
            throw new \Exception('cURL error (' . curl_errno($this->ch) . '): ' . curl_error($this->ch));
        }

        return $request;
    }

    public function getResponseCode() {
        return curl_getinfo($this->ch, CURLINFO_RESPONSE_CODE);
    }

    public function __destruct()
    {
        curl_close($this->ch);
    }
}

/*
function api_post( $apiKey, $company_id, $token, $arrayData )
{
    // array of curl handles
    $multiCurl = array();
    // data to be returned
    $result = array();
    // multi handle
    $mh = curl_multi_init();
    //$ch = curl_init();

    $headers = array();
    $headers[] = "Authorization: Bearer $token";
    $headers[] = "X-Api-Key: " . $apiKey;
    $headers[] = "X-Proxy-Global-Company-Id: " . $company_id;
    $headers[] = "Accept: application/json";
    $headers[] = "Content-Type: application/json";

    $fetchUrl = 'https://analytics.adobe.io/api/' . $company_id . '/reports';

    foreach ($arrayData as $i => $data) {
        // URL from which data will be fetched
        $multiCurl[$i] = curl_init();
        curl_setopt($multiCurl[$i], CURLOPT_URL, $fetchUrl);
        curl_setopt($multiCurl[$i], CURLOPT_POST, 1);
        curl_setopt($multiCurl[$i], CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($multiCurl[$i], CURLOPT_HTTPHEADER, $headers);
        curl_setopt($multiCurl[$i], CURLOPT_POSTFIELDS, $data[0]);
        curl_setopt($multiCurl[$i], CURLOPT_RETURNTRANSFER, true);

        curl_multi_add_handle($mh, $multiCurl[$i]);
    }

    $index=null;
    do {
      curl_multi_exec($mh,$index);
    } while($index > 0);
    // get content and remove handles
    foreach($multiCurl as $k => $ch) {
      $result[$k] = curl_multi_getcontent($ch);
      curl_multi_remove_handle($mh, $ch);
    }
    // close
    curl_multi_close($mh);

    /*
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        $err = 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    */

   /***********
    return $result; 

}
*/

?>
