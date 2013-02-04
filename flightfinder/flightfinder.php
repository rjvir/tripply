<?php
$startTime = time();

include_once "oauth/OAuthStore.php";
include_once "oauth/OAuthRequester.php";

$key = 'dj0yJmk9MmNaTlRDakoyMjZMJmQ9WVdrOVJYTlhUVXRVTjJjbWNHbzlNVGsxTlRBNU9ETTJNZy0tJnM9Y29uc3VtZXJzZWNyZXQmeD1jYw--'; // this is your consumer key
$secret = '8edb8e5159d5dbfa97d7e2ad8ad0d3bc79415423'; // this is your secret key

$options = array( 'consumer_key' => $key, 'consumer_secret' => $secret );
OAuthStore::instance("2Leg", $options );

function prettyprint($obj){
	echo "<pre>";
	echo json_encode($obj, JSON_PRETTY_PRINT);
	echo "</pre>";
}

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
    $a = explode("/", $a);
    $b = explode("/", $b);
	$a = new DateTime('20'.$a[2].'-'.$a[0]."-".$a[1]);
	$b = new DateTime('20'.$b[2].'-'.$b[0]."-".$b[1]);
    return ($a > $b);
}

/*******************************************************************************************/
//Objects that need to be deleted.
/*
$ch = curl_init("https://api.parse.com/1/classes/Deals?limit=600");
$headers = array("X-Parse-Application-Id: mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL",
				"X-Parse-REST-API-Key: aRzlV8V7nuKE28llMLlX5yjkIF9tGp1NkJrosSQH",
				"Content-type: application/json");

//set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
if($return = curl_exec($ch)) curl_close($ch);
else die(curl_error($ch));

$return = json_decode($return, true);
$objectstodelete = array();
foreach($return["results"] as $result){
	$objectstodelete[] = $result["objectId"];
}

echo "Objects To Delete Loaded In: ";
echo time()-$startTime;
echo "<br>";

/*******************************************************************************************/
//Get cities that already have images.

$ch = curl_init("https://api.parse.com/1/classes/CityImages?limit=1000");
$headers = array("X-Parse-Application-Id: mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL",
				"X-Parse-REST-API-Key: aRzlV8V7nuKE28llMLlX5yjkIF9tGp1NkJrosSQH",
				"Content-type: application/json");

//set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
if($return = curl_exec($ch)) curl_close($ch);
else die(curl_error($ch));

$return = json_decode($return, true);
$citieswithimages = array();
foreach($return["results"] as $cityimage){
	$citieswithimages[] = $cityimage["airportCode"];
}


/*******************************************************************************************/
//Get airports
set_time_limit(30);
$airports = array();
if(($file = fopen("airports.csv","r")) !== false){
	while($data = fgetcsv($file)){
		$airports[] = $data[0];
	}
}
else die('unable to get companies');

/*******************************************************************************************/
//Post new data to parse.com

$postTime = time();
$airportimagestoadd = array();
foreach($airports as $airport){
	$imagestopost = array();
	set_time_limit(30);
	echo file_get_contents("http://www.kayak.com/h/rss/buzz?code=".$airport."&tm=".date("Ym"));
	$rss = json_decode(parse("http://www.kayak.com/h/rss/buzz?code=".$airport."&tm=".date("Ym")), true);
	$rss2 = json_decode(parse("http://www.kayak.com/h/rss/buzz?code=".$airport."&tm=".(date("Ym")+1)), true);
	
	
	foreach($rss2 as $extended){
		$rss[] = $extended;
	}
	
	prettyprint($rss);
	
	usort($rss, "date_compare");
	$temp = array();
	$tempkeys = array();
	foreach($rss as $key=>$deal){
		$date1 = explode("/",$deal["departDate"]);
		$date2 = explode("/",$deal["returnDate"]);
		$datetime1 = new DateTime('20'.$date1[2].'-'.$date1[0]."-".$date1[1]);
		$datetime2 = new DateTime('20'.$date2[2].'-'.$date2[0]."-".$date2[1]);
		$tomorrow = new DateTime('tomorrow');
		$interval = $datetime1->diff($datetime2, true);
		if(!in_array($deal["destCode"],$temp) && ($interval->format('%a') < 11) && ($datetime1 > $tomorrow)){
			$tempkeys[] = $key;
		}
	}
	
	$temp = array();
	foreach($tempkeys as $key){
		$temp[] = $rss[$key];
	}
	$rss = $temp;
	array_splice($rss, 12);
	
	foreach($rss as $key=>$deal){
		if(!in_array($deal['destCode'],$citieswithimages) && !in_array($deal['destCode'],$airportimagestoadd)){
			$url = "http://yboss.yahooapis.com/ysearch/images?q=".str_replace(",","",$deal["destLocation"]); // this is the URL of the request
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
				$airportimagestoadd[] = $deal['destCode'];
				$imagestopost[] = array("airportCode" => $deal['destCode'], "imageUrl" => $response["bossresponse"]["images"]["results"][0]["url"]);
			}
			catch(OAuthException2 $e){ die('Oauth error');}
		}
		$depart = explode("/",$deal['departDate']);
		$return = explode("/",$deal['returnDate']);
		$depart = new DateTime('20'.$depart[2].'-'.$depart[0]."-".$depart[1]);
		$return = new DateTime('20'.$return[2].'-'.$return[0]."-".$return[1]);
		$rss[$key]['link'] = "http://www.kayak.com/flights#/".$deal['originCode']."-".$deal['destCode']."/".$depart->format('Y-m-d')."/".$return->format('Y-m-d');
	}
	$data = array();
	foreach($rss as $deal){
		$data["requests"][] = array("method" => "POST", "path" => "/1/classes/Deals", "body" => $deal);
	}
	
	$imagepost = array();
	foreach($imagestopost as $imagedata){
		$imagepost["requests"][] = array("method" => "POST", "path" => "/1/classes/CityImages", "body" => $imagedata);
	}
	
	/*
	
	$ch = curl_init("https://api.parse.com/1/batch");
	$headers = array("X-Parse-Application-Id: mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL",
					"X-Parse-REST-API-Key: aRzlV8V7nuKE28llMLlX5yjkIF9tGp1NkJrosSQH",
					"Content-type: application/json");
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($imagepost));
	
	if(curl_exec($ch)) curl_close($ch);
	else die(curl_error($ch));
		

	$ch = curl_init("https://api.parse.com/1/batch");
	$headers = array("X-Parse-Application-Id: mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL",
					"X-Parse-REST-API-Key: aRzlV8V7nuKE28llMLlX5yjkIF9tGp1NkJrosSQH",
					"Content-type: application/json");
	//set the url, number of POST vars, POST data
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	
	if(curl_exec($ch)) curl_close($ch);
	else die(curl_error($ch));
	*/
}

echo "Objects Posted In: ";
echo time()-$postTime;
echo "<br>";

/*******************************************************************************************/
//Delete old objects.
/*
$deleteTime = time();

for($i = 0; $i<12; $i++){
$delete = array();
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
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	
	if(curl_exec($ch)) curl_close($ch);
	else die(curl_error($ch));
}
echo "Objects Deleted In: ";
echo time()-$deleteTime;
echo "<br>";

echo "Total Time: ";
echo time()-$startTime;

/*******************************************************************************************/

