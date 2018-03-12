<?php

 header('Access-Control-Allow-Origin: *');  

/**
 * Yelp Fusion API code sample.
 *
 * This program demonstrates the capability of the Yelp Fusion API
 * by using the Business Search API to query for businesses by a 
 * search term and location, and the Business API to query additional 
 * information about the top result from the search query.
 * 
 * Please refer to http://www.yelp.com/developers/v3/documentation 
 * for the API documentation.
 * 
 * Sample usage of the program:
 * `php sample.php --term="dinner" --location="San Francisco, CA"`
 */
// API key placeholders that must be filled in by users.
// You can find it on
// https://www.yelp.com/developers/v3/manage_app
// $API_KEY = "PKodD9PMKeu7-zukSEFfjk2fS0bPnK96AMRVrMoU8R6DqrVNRtJNXOxcdLbKK9fOQaz11q22tEk_Eb40U3GPFcMbu5N3b7CbUVL5XhUvFoyfEWmJWoDohGv3igmmWnYx";

// Complain if credentials haven't been filled out.
// assert($API_KEY, "Please supply your API key.");
// OAuth credential placeholders that must be filled in by users.
// You can find them on
// https://www.yelp.com/developers/v3/manage_app
// $CLIENT_ID = "OT81e2rs4oN4WBk6WbUAZw";
// $CLIENT_SECRET = "XW6-PF8vfuV9vXArvtp9jUr_sXZ0Ml982Nn3B87rEDhOklO1sb3Zvsa8Sd-nDOiyDszlECF9b8GmjKe0NTnczJR1R0cGA3dSZYhsQRmO7IQIbalk6s2ovaoo4dOlWnYx";

// // Complain if credentials haven't been filled out.
// assert($CLIENT_ID, "Please supply your client_id.");
// assert($CLIENT_SECRET, "Please supply your client_secret.");

// API constants, you shouldn't have to change these.
$API_HOST = "https://api.yelp.com";
$SEARCH_PATH = "/v3/businesses/search";
$BUSINESS_PATH = "/v3/businesses/";  // Business ID will come after slash.
// $TOKEN_PATH = "/oauth2/token";
// $GRANT_TYPE = "client_credentials";

// Defaults for our simple example.
$DEFAULT_TERM = "dinner";
$DEFAULT_LOCATION = "San Francisco, CA";
$SEARCH_LIMIT = 50;
$SEARCH_SORTBY = "rating";
$DEFAULT_RADIUS = 8064;

/**
 * Given a bearer token, send a GET request to the API.
 * 
 */

// function obtain_bearer_token() {
//     try {
//         # Using the built-in cURL library for easiest installation.
//         # Extension library HttpRequest would also work here.
//         $curl = curl_init();
//         if (FALSE === $curl)
//             throw new Exception('Failed to initialize');

//         $postfields = "client_id=" . $GLOBALS['CLIENT_ID'] .
//             "&client_secret=" . $GLOBALS['CLIENT_SECRET'] .
//             "&grant_type=" . $GLOBALS['GRANT_TYPE'];

//         curl_setopt_array($curl, array(
//             CURLOPT_URL => $GLOBALS['API_HOST'] . $GLOBALS['TOKEN_PATH'],
//             CURLOPT_RETURNTRANSFER => true,  // Capture response.
//             CURLOPT_ENCODING => "",  // Accept gzip/deflate/whatever.
//             CURLOPT_MAXREDIRS => 10,
//             CURLOPT_TIMEOUT => 30,
//             CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
//             CURLOPT_CUSTOMREQUEST => "POST",
//             CURLOPT_POSTFIELDS => $postfields,
//             CURLOPT_HTTPHEADER => array(
//                 "cache-control: no-cache",
//                 "content-type: application/x-www-form-urlencoded",
//             ),
//         ));

//         $response = curl_exec($curl);

//         if (FALSE === $response)
//             throw new Exception(curl_error($curl), curl_errno($curl));
//         $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//         if (200 != $http_status)
//             throw new Exception($response, $http_status);

//         curl_close($curl);
//     } catch(Exception $e) {
//         trigger_error(sprintf(
//             'Curl failed with error #%d: %s',
//             $e->getCode(), $e->getMessage()),
//             E_USER_ERROR);
//     }

//     $body = json_decode($response);
//     $bearer_token = $body->access_token;
//     return $bearer_token;
// }


/** 
 * Makes a request to the Yelp API and returns the response
 * 
 * @param    $host    The domain host of the API 
 * @param    $path    The path of the API after the domain.
 * @param    $url_params    Array of query-string parameters.
 * @return   The JSON response from the request      
 */
function request($host, $path, $url_params = array()) {
    // Send Yelp API Call
    echo var_dump($host);
    try {
        $curl = curl_init();
        if (FALSE === $curl)
            throw new Exception('Failed to initialize');

        $url = $host . $path . "?" . http_build_query($url_params);
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,  // Capture response.
            CURLOPT_ENCODING => "",  // Accept gzip/deflate/whatever.
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer " . "PKodD9PMKeu7-zukSEFfjk2fS0bPnK96AMRVrMoU8R6DqrVNRtJNXOxcdLbKK9fOQaz11q22tEk_Eb40U3GPFcMbu5N3b7CbUVL5XhUvFoyfEWmJWoDohGv3igmmWnYx",
                "cache-control: no-cache",
            ),
        ));

        $response = curl_exec($curl);

        if (FALSE === $response)
            throw new Exception(curl_error($curl), curl_errno($curl));
        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if (200 != $http_status)
            throw new Exception($response, $http_status);

        curl_close($curl);
    } catch(Exception $e) {
        trigger_error(sprintf(
            'Curl failed with error #%d: %s',
            $e->getCode(), $e->getMessage()),
            E_USER_ERROR);
    }

    return $response;
}

/**
 * Query the Search API by a search term and location 
 * 
 * @param    $term        The search term passed to the API 
 * @param    $location    The search location passed to the API 
 * @return   The JSON response from the request 
 */
function search($term, $location, $radius) {
    $url_params = array();
    
    $url_params['term'] = $term;
    $url_params['location'] = $location;
    $url_params['radius'] = $radius;
    $url_params['limit'] = $GLOBALS['SEARCH_LIMIT'];
    $url_params['sort_by'] = $GLOBALS['SEARCH_SORTBY'];
    
    return request($GLOBALS['API_HOST'], $GLOBALS['SEARCH_PATH'], $url_params);
}

/**
 * Query the Business API by business_id
 * 
 * @param    $business_id    The ID of the business to query
 * @return   The JSON response from the request 
 */
function get_business($business_id) {
    $business_path = $GLOBALS['BUSINESS_PATH'] . urlencode($business_id);
    
    return request($GLOBALS['API_HOST'], $business_path);
}

/**
 * Queries the API by the input values from the user 
 * 
 * @param    $term        The search term to query
 * @param    $location    The location of the business to query
 */
function query_api($term, $location, $radius) {     
    // $bearer_token = obtain_bearer_token();

    $response = json_decode(search($term, $location, $radius));
    

    //CODE I ADDED 
    $pretty_response = json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    print "$pretty_response\n";
   


    // COMMENTED OUT
    // $business_id = $response->businesses[0]->id;
    
    // print sprintf(
    //     "%d businesses found, querying business info for the top result \"%s\"\n\n",         
    //     count($response->businesses),
    //     $business_id
    // );
    
    // $response = get_business($bearer_token, $business_id);
    
    // print sprintf("Result for business \"%s\" found:\n", $business_id);
    // $pretty_response = json_encode(json_decode($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
    // print "$pretty_response\n";
}

/**
 * User input is handled here 
 */
$longopts  = array(
    "term::",
    "location::",
    "radius::",
);
    
//$options = getopt("", $longopts);

$term = $_GET['term'] ?: $GLOBALS['DEFAULT_TERM'];
$location = $_GET['location'] ?: $GLOBALS['DEFAULT_LOCATION'];
$radius = $_GET['radius'] ?: $GLOBALS['DEFAULT_RADIUS'];
//echo $_GET['term'] . "\n";
//echo $_GET['location'] . "\n";
query_api($term, $location, $radius);

?>