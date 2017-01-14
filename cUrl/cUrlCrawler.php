<?php
/**
 * Created by PhpStorm.
 * User: gognj_000
 * Date: 1/14/2017
 * Time: 1:54 PM
 */
require('lib/simple_html_dom.php');

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


$htmlResponse = curlApiWrapper("https://www.fiverr.com/", "gigblast");

/*$htmlResponse = preg_replace("/<script[\s\S]*?>[\s\S]*?<\/script>/", "", $htmlResponse);
$htmlResponse = preg_replace("/<link[\s\S]*?>/", "", $htmlResponse);
$htmlResponse = preg_replace("/<meta[\s\S]*?>/", "", $htmlResponse);*/
preg_match("/data-json-path=\"(.*?)\"/si", $htmlResponse, $matches);

if(!empty($matches)){
    $match = $matches[0];
    $match = str_replace("data-json-path=\"","",$match);
    $match = str_replace("\"","", $match);

    $test = curlApiWrapper("https://www.fiverr.com/", $match);

    $fiverJobsArray = json_decode($test,true)["gigs"];

    foreach($fiverJobsArray as $job) { //foreach element in $arr
        if(isset($job["is_best_seller"])){ continue;}
        var_dump($job["title"]);
        var_dump($job["gig_url"]);
        echo "<br/>";
    }


    return;
}

return;
//$htmlResponse = preg_replace("/<html>/", "", $htmlResponse);
$html = str_get_html($htmlResponse);

//var_dump("" .$htmlResponse ."");
$ret = $html->find('*[data-json-path]',0);



echo $tag;
//echo json_encode($html);

return;



//function getDataFrom


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

