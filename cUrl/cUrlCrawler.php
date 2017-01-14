<?php
/**
 * Created by PhpStorm.
 * User: gognj_000
 * Date: 1/14/2017
 * Time: 1:54 PM
 */
require('lib/simple_html_dom.php');

// if you are doing ajax with application-json headers
if (empty($_POST) || isset($_POST["username"])) {
    return json_response("Input not valid", 400);
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
    return json_encode(array(
        'status' => $code < 300, // success or not?
        'message' => $message
    ));
}

function curlApiWrapper(){
    $ch = curl_init();

    $url = 'https://twitter.com/';
    $proxy = 'proxy.crawlera.com:8010';
    $proxy_auth = '<API KEY>:';

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_auth);
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CAINFO, '/path/to/crawlera-ca.crt');

    $scraped_page = curl_exec($ch);
    curl_close($ch);
    echo $scraped_page;
}


?>

