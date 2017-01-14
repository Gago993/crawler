<?php
/**
 * Created by PhpStorm.
 * User: gognj_000
 * Date: 1/14/2017
 * Time: 1:54 PM
 */
/*require('lib\vendor\autoload.php');
use PHPHtmlParser\Dom;*/

/*
$testCase = array(
    "totalQueueOrders" => 6,
    "username" => "gigblast",
    "pageDetails" => array(
        new PageDetails("first title", 3),
        new PageDetails("second title", 5),
        new PageDetails("third title", 7),
        new PageDetails("forth title", 1),
        new PageDetails("fifth title", 5),
        new PageDetails("sixth title", 7)
    )
);

echo json_response($testCase);

return;

// if you are doing ajax with application-json headers
if (empty($_POST) || isset($_POST["username"])) {
    echo json_response("Input not valid", 400);
}*/

/*$t = "234 Orders in Queue\t";
$b = getFirstNumberInString($t);
var_dump($t);
var_dump($b);
return;*/

var_dump(curlApiWrapper("https://www.fiverr.com/", "gigblast/design-highly-unique-conceptual-logo?funnel=b2132309-bd20-415f-8643-e341fc9208d4"));
return;


ini_set('max_execution_time', 123456);
$htmlResponse = curlApiWrapper("https://www.fiverr.com/", "gigblast");



/*$dom = new Dom;
$dom->load($htmlResponse);
$a = $dom->find('div[data-json-path]')[0];
var_dump($a);
echo $a->text; // "click here"
//var_dump($dom);

return;*/

preg_match("/data-json-path=\"(.*?)\"/si", $htmlResponse, $matches);

if(!empty($matches)){
    $match = $matches[0];
    $match = str_replace("data-json-path=\"","",$match);
    $match = str_replace("\"","", $match);

    $ageDetailsArray = array();

    $jobResponse = curlApiWrapper("https://www.fiverr.com/", $match);

    $fiverJobsArray = json_decode($jobResponse,true)["gigs"];

    foreach($fiverJobsArray as $job) { //foreach element in $arr
        if(isset($job["is_best_seller"])){ continue;}

        $gitTitle = $job["title"];
        $gigUrl = $job["gig_url"];

        $gigResponse = curlApiWrapper("https://www.fiverr.com/", $gigUrl);

        var_dump($gigResponse);
        preg_match("/<span class=\"stats-row\">[A-Z0-9 _]*<\/span>/si", $gigResponse, $gigMatches);


       /* return;
        if(!isset($gigMatches) || empty($gigMatches)){
            var_dump("errror");
            continue;
        }

        $queueNumber = preg_replace("/[^0-9 ]/", "", $gigMatches[0]);
        $queueNumber = getFirstNumberInString($queueNumber);

        $pageDetailsArray[] = new PageDetails($gitTitle, $queueNumber);*/
    }

    echo json_encode($pageDetailsArray);
}




function getFirstNumberInString($string){
    if(!isset($string) || is_null($string))
        return "";
    $string = trim($string);
    preg_match('/[0-9]*/si', $string, $m);
    return $m[0];
}



function getGigQuery($gigId, $userId){

    "/gigs/other_gigs_by?gig_id=2207529&limit=2&type=endless&user_id=1199599";

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
    //$url = 'https://www.fiverr.com/' . $username;

    $url = $site . $username;
    $proxy = 'proxy.crawlera.com:8010';
    //$proxy_auth = '<API KEY>:';
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
        var_dump($test);
    }

    curl_close($ch);
    return $scraped_page;
}


class PageDetails {
    public $title;
    public $numQueueOrders;

    function __construct($title, $numQueueOrders) {
        $this->title = $title;
        $this->numQueueOrders = $numQueueOrders;
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
}

?>

