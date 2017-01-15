<?php
// Start of the script
ini_set('max_execution_time', 123456);

if (empty($_GET) || !isset($_GET["username"])) {
	exitWithError("HTTP/1.1 400 Invalid data", "Empty query param: username");
}

$username = $_GET["username"];
$response = crawlFiverrUserGigs($username);

die(json_encode($response));
// End of the script

// Helper functions
function crawlFiverrUserGigs($user)
{
	// Fetch the user profile web page
    $htmlResponse = curlApiWrapper("https://www.fiverr.com/", $user);

    if(!isset($htmlResponse) || is_null($htmlResponse)){
    	exitWithError("HTTP/1.1 400 Invalid data", "User not valid 1");
    }

    // Find the api url which is hidden in the html of the user profile web page. 
    // This api returns json which includes the list of all gigs for the user and a link to each gig web page
    preg_match("/data-json-path=\"(.*?)\"/si", $htmlResponse, $matches);

    if(!isset($matches) || is_null($matches) || empty($matches) || !isset($matches[0])){
    	exitWithError("HTTP/1.1 400 Invalid data", "User not valid 2");
    }

    $match = $matches[0];
    $match = str_replace("data-json-path=\"", "", $match);
    $match = str_replace("\"", "", $match);

    if(!isset($match) || is_null($match) || empty($match)){
    	exitWithError("HTTP/1.1 400 Invalid data", "User not valid 3");
    }

    $pageDetailsArray = array();
    $totalQueueOrders = 0;

    // Call the previously fetched api from the web page in order to get the json for the list of all gigs for the user
    $gigsResponse = curlApiWrapper("https://www.fiverr.com/", $match);

    if(!isset($gigsResponse) || is_null($gigsResponse)){
    	exitWithError("HTTP/1.1 400 Invalid data", "User not valid 4");
    }

    $fiverJobsJson = json_decode($gigsResponse, true);
    if(!isset($fiverJobsJson["gigs"])){
    	exitWithError("HTTP/1.1 400 Invalid data", "We could not find any gigs records for this user");
    }

    $fiverJobsArray = $fiverJobsJson["gigs"];
    
    // Setup multithreaded curl requests
    $mh = curl_multi_init();
    $chs = array();

    // Foreach gig prepare the curl request that will be send in order to get each gig web page. Add the curl request
    // to an array for later retreival of the response
    foreach ($fiverJobsArray as $job) {
    	// If the user is a best seller we don't need to include that gig entry since it is a duplicate. It is included on the other gigs
    	if (isset($job["is_best_seller"])) {
            continue;
        }
    	$gigTitle = $job["title"];
        $gigUrl = $job["gig_url"];

        $ch = curlMultiApiWrapper("https://www.fiverr.com/", $gigUrl);
        array_push($chs, array('ch' => $ch, 'title'=>$gigTitle));
        curl_multi_add_handle($mh, $ch);
    }

      // Execute all requests simultaneously, and continue when all are complete
	  $running = null;
	  do {
	    	curl_multi_exec($mh, $running);
	  } while ($running);

	  // Close all curl connections
	  foreach ($chs as $ch) {
	  		curl_multi_remove_handle($mh, $ch['ch']);
	  }
	  curl_multi_close($mh);

	  // Iterate over the curl connections and get the response out of each
	  foreach ($chs as $ch) {
	  	 $gigResponse = curl_multi_getcontent($ch['ch']);
	  	 preg_match("/<span class=\"stats-row\">[A-Z0-9 _]*<\/span>/si", $gigResponse, $gigMatches);

        if (!isset($gigMatches) || empty($gigMatches)) {
            $queueNumber = "0";
        } else {
            $queueNumber = preg_replace("/[^0-9 ]/", "", $gigMatches[0]);
            $queueNumber = getFirstNumberInString($queueNumber);
            $totalQueueOrders += (int)$queueNumber;
        }

        $gigTitle = $ch['title'];
        $pageDetailsArray[] = new PageDetails($gigTitle, $queueNumber);
	  }


    $responsePageDetailsArray = array(
        "username" => $user,
        "totalQueueOrders" => $totalQueueOrders,
        "pageDetails" => $pageDetailsArray
    );

    return $responsePageDetailsArray;
}

function curlMultiApiWrapper($site, $username){
	$ch = curl_init();

    $apiKey = "39a187a98ba34356b6fcf900da4a29ab";

    $url = $site . $username;
    $proxy = 'proxy.crawlera.com:8010';
    $proxy_auth = $apiKey;

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_PROXY, $proxy);
    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxy_auth);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json'
    ));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CAINFO, realpath('certificate/crawlera-ca.crt'));

    return $ch;
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

function exitWithError($header, $exitMessage)
{
  header($header);
  exit($exitMessage);
}


class PageDetails {
    public $title;
    public $numQueueOrders;
    public $time;

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

    public function gettime(){
        return $this->time;
    }

    public function settime($time){
        $this->time=$time;
    }
}

?>

