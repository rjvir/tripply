<?php

include_once "oauth/OAuthStore.php";
include_once "oauth/OAuthRequester.php";

$key = 'dj0yJmk9MmNaTlRDakoyMjZMJmQ9WVdrOVJYTlhUVXRVTjJjbWNHbzlNVGsxTlRBNU9ETTJNZy0tJnM9Y29uc3VtZXJzZWNyZXQmeD1jYw--'; // this is your consumer key
$secret = '8edb8e5159d5dbfa97d7e2ad8ad0d3bc79415423'; // this is your secret key

$options = array( 'consumer_key' => $key, 'consumer_secret' => $secret );
OAuthStore::instance("2Leg", $options );

function parse($url) {
		$fileContents= file_get_contents($url);
		$fileContents = str_replace(array("\n", "\r", "\t","kyk:"), '', $fileContents);
		$fileContents = trim(str_replace('"', "'", $fileContents));
		$simpleXml = simplexml_load_string($fileContents);
		$json = json_encode($simpleXml, JSON_PRETTY_PRINT);
		$json = json_decode($json);
		$json = $json->channel->item;
		$json = json_encode($json, JSON_PRETTY_PRINT);
		return $json;
}

function date_compare($a, $b)
{
    $a = $a['departDate'];
    $b = $b['departDate'];
    $a = str_replace("/", "", $a);
    $b = str_replace("/", "", $b);
    return ($a > $b);
}

/*******************************************************************************************/
//Delete function begins here.
/*
$ch = curl_init("https://api.parse.com/1/classes/Deals");
$headers = array("X-Parse-Application-Id: mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL",
				"X-Parse-REST-API-Key: aRzlV8V7nuKE28llMLlX5yjkIF9tGp1NkJrosSQH",
				"Content-type: application/json");

//set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
if($return = curl_exec($ch)) curl_close($ch);
$return = json_decode($return);

$objectstodelete = array();
foreach($return->results as $result){
	$objectstodelete[] = $result->objectId;
}

$delete = array();
for($i = 0; $i<12; $i++){
	for($j = 0; $j<50; $j++){
		$num = $j+(50*$i);
		if(array_key_exists($num, $objectstodelete)){
			$delete["requests"][] = array("method" => "DELETE", "path" => "/1/classes/Deals/".$objectstodelete[$num]);
		}
	}
	$ch = curl_init("https://api.parse.com/1/batch");
	$headers = array("X-Parse-Application-Id: mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL",
					"X-Parse-REST-API-Key: aRzlV8V7nuKE28llMLlX5yjkIF9tGp1NkJrosSQH",
					"Content-type: application/json");
	
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($delete));
	
	if(curl_exec($ch)) curl_close($ch);
	else echo curl_error($ch);
}
//Delete function ends here.

/*******************************************************************************************/
set_time_limit(30);
$airports = array();
if(($file = fopen("airports.csv","r")) !== false){
	while($data = fgetcsv($file)){
		$airports[] = $data[0];
	}
}
else die('unable to get companies');

/*******************************************************************************************/
foreach($airports as $airport){
	set_time_limit(30);
	$rss = json_decode(parse("http://www.kayak.com/h/rss/buzz?code=".$airport."&tm=".date("Ym")), true);
	for($i=0;$i<count($rss);$i++){
		$url = "http://yboss.yahooapis.com/ysearch/images?q=".str_replace(",","",$rss[$i]["destLocation"]); // this is the URL of the request
		$method = "GET"; // you can also use POST instead
		$params = null;
		try{
			// Obtain a request object for the request we want to make
			$request = new OAuthRequester($url, $method, $params);
			// Sign the request, perform a curl request and return the results, 
			// throws OAuthException2 exception on an error
			// $result is an array of the form: array ('code'=>int, 'headers'=>array(), 'body'=>string)
			$result = $request->doRequest();
			$response = $result['body'];
			$response = json_decode($response, true);
			$rss[$i]["destImage"] = $response["bossresponse"]["images"]["results"][0]["url"];
		}
		catch(OAuthException2 $e){ die('Oauth error');}
	}
	usort($rss, "date_compare");
	$data = array();
	for($i = 0; $i<12;$i++){
		$data["requests"][] = array("method" => "POST", "path" => "/1/classes/Deals", "body" => $rss[$i]);
	}
	
	$ch = curl_init("https://api.parse.com/1/batch");
	$headers = array("X-Parse-Application-Id: mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL",
					"X-Parse-REST-API-Key: aRzlV8V7nuKE28llMLlX5yjkIF9tGp1NkJrosSQH",
					"Content-type: application/json");
	
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	
	if(curl_exec($ch)) curl_close($ch);
	else echo curl_error($ch);
}
/*******************************************************************************************/

