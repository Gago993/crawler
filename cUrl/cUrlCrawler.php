<?php

ini_set('max_execution_time', 123456);

if (empty($_POST) || !isset($_POST["username"])) {
    echo json_response("Please insert username", 400);
}

$username = $_POST["username"];
$response = crawlFiverrUserGigs($username);
echo $response;
return;

function crawlFiverrUserGigs($user)
{
    $htmlResponse = curlApiWrapper("https://www.fiverr.com/", $user);

    if(!isset($htmlResponse) || is_null($htmlResponse)){
        echo json_response("User not valid", 400);
        return;
    }

    preg_match("/data-json-path=\"(.*?)\"/si", $htmlResponse, $matches);

    if(!isset($matches) || is_null($matches) || empty($matches) || !isset($matches[0])){
        echo json_response("User not valid", 400);
        return;
    }

    $match = $matches[0];
    $match = str_replace("data-json-path=\"", "", $match);
    $match = str_replace("\"", "", $match);

    if(!isset($match) || is_null($match) || empty($match)){
        echo json_response("User not valid", 400);
        return;
    }

    $pageDetailsArray = array();
    $totalQueueOrders = 0;

    $gigsResponse = curlApiWrapper("https://www.fiverr.com/", $match);

    if(!isset($gigsResponse) || is_null($gigsResponse)){
        echo json_response("User not valid", 400);
        return;
    }
    $fiverJobsJson = json_decode($gigsResponse, true);
    if(!isset($fiverJobsJson["gigs"])){
        return json_response("User has no gigs", 400);
    }

    $fiverJobsArray = $fiverJobsJson["gigs"];

    foreach ($fiverJobsArray as $job) { //foreach element in $arr
        if (isset($job["is_best_seller"])) {
            continue;
        }

        $gigTitle = $job["title"];
        $gigUrl = $job["gig_url"];


        //start timer
        $time_start = microtime(true);

        $gigResponse = curlApiWrapper("https://www.fiverr.com/", $gigUrl);
        preg_match("/<span class=\"stats-row\">[A-Z0-9 _]*<\/span>/si", $gigResponse, $gigMatches);

        //end timer
        $time_end = microtime(true);
        $time = $time_end - $time_start;


        if (!isset($gigMatches) || empty($gigMatches)) {
            $queueNumber = "0";
        } else {
            $queueNumber = preg_replace("/[^0-9 ]/", "", $gigMatches[0]);
            $queueNumber = getFirstNumberInString($queueNumber);
            $totalQueueOrders += (int)$queueNumber;
        }

        $pageDetailsArray[] = new PageDetails($gigTitle, $queueNumber, $time);
    }

    $responsePageDetailsArray = array(
        "username" => $user,
        "totalQueueOrders" => $totalQueueOrders,
        "pageDetails" => $pageDetailsArray
    );

    return json_response($responsePageDetailsArray);
}



function json_response($message = null, $code = 200)
{
    // clear the old headers
    header_remove();
    // set the actual code
    http_response_code($code);
    // set the header to make sure cache is forced
    header("Cache-Control: no-transform,public,max-age=300,s-maxage=900");
    // treat this as json
    header('Content-Type: application/json');
    $status = array(
        200 => '200 OK',
        400 => '400 Bad Request',
        422 => 'Unprocessable Entity',
        500 => '500 Internal Server Error'
    );
    // ok, validation error, or failure
    header('Status: '.$status[$code]);
    // return the encoded json

    return json_encode($message);
   /* return json_encode(array(
        'status' => $code < 300, // success or not?
        'message' => $message
    ));*/
}

function curlApiWrapper($site, $username){
    $ch = curl_init();

    $apiKey = "39a187a98ba34356b6fcf900da4a29ab";

    $url = $site . $username;
    $proxy = 'proxy.crawlera.com:8010';
    $proxy_auth = $apiKey;

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_auth);
    //curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json'
    ));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CAINFO, realpath('certificate/crawlera-ca.crt'));

    $scraped_page = curl_exec($ch);

    if(!$scraped_page) {
        $test = curl_error($ch);
        return null;
    }

    curl_close($ch);
    return $scraped_page;
}

function getFirstNumberInString($string){
    if(!isset($string) || is_null($string))
        return "";
    $string = trim($string);
    preg_match('/[0-9]*/si', $string, $m);
    return $m[0];
}


class PageDetails {
    public $title;
    public $numQueueOrders;
    public $time;

    function __construct($title, $numQueueOrders, $time) {
        $this->title = $title;
        $this->numQueueOrders = $numQueueOrders;
        $this->time = $time;
    }

    public function settitle($title){
        $this->title=$title;
    }

    public function setcount($numQueueOrders){
        $this->numQueueOrders=$numQueueOrders;
    }

    public function gettitle(){
        return $this->title;
    }

    public function getcount(){
        return $this->numQueueOrders;
    }

    public function gettime(){
        return $this->time;
    }

    public function settime($time){
        $this->time=$time;
    }
}

?>

