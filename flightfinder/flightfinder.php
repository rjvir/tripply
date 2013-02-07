<?php
$startTime = time();	//Used to track runtimes.
set_time_limit(0);		//Set infinite time limit so script doesn't time out gathering data.

include_once "oauth/OAuthStore.php";
include_once "oauth/OAuthRequester.php";

//Function used to print arrays out as JSON strings using PRETTY_PRINT and <pre> tags to make them easy to read.
function prettyprint($obj){
	echo "<pre>";
	echo json_encode($obj, JSON_PRETTY_PRINT);
	echo "</pre>";
}

//Function converts XML input files into arrays.
function xmlToArray($url) {
		$fileContents = file_get_contents($url);
		$fileContents = str_replace(array("\n", "\r", "\t","kyk:"), '', $fileContents);
		$fileContents = trim(str_replace('"', "'", $fileContents));
		$simpleXml = simplexml_load_string($fileContents);
		$temp = array();
		foreach($simpleXml->channel->item as $flight){
			$temp[] = $flight;
		}
		$json = json_encode($temp);
		$json = json_decode($json, true);
		return $json;
}

//Date compare for usort() to sort flights by date.
function date_compare($a, $b){
    $a = $a['departDate'];
    $b = $b['departDate'];
    $a = explode("/", $a);
    $b = explode("/", $b);
	$a = new DateTime('20'.$a[2].'-'.$a[0]."-".$a[1]);
	$b = new DateTime('20'.$b[2].'-'.$b[0]."-".$b[1]);
    return ($a > $b);
}

//OAuth Keys
$key = 'dj0yJmk9MmNaTlRDakoyMjZMJmQ9WVdrOVJYTlhUVXRVTjJjbWNHbzlNVGsxTlRBNU9ETTJNZy0tJnM9Y29uc3VtZXJzZWNyZXQmeD1jYw--'; //Consumer key
$secret = '8edb8e5159d5dbfa97d7e2ad8ad0d3bc79415423'; //Secret key

$options = array( 'consumer_key' => $key, 'consumer_secret' => $secret );
OAuthStore::instance("2Leg", $options );

/****  Curl used to grab objects that need to be deleted from Parse  *************************/
//Initialize curl to REST API URL
$ch = curl_init("https://api.parse.com/1/classes/Deals?limit=600");
//Set parse keys into curl headers using array.
$headers = array("X-Parse-Application-Id: mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL",
				"X-Parse-REST-API-Key: aRzlV8V7nuKE28llMLlX5yjkIF9tGp1NkJrosSQH",
				"Content-type: application/json");

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);		//Set Headers
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);	//Disable SSL verification (because we aren't using certificates with Parse)
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);	//Allow return data to be placed in variable and not just printed to screen.
if($return = curl_exec($ch)) curl_close($ch);		
else die(curl_error($ch));

$return = json_decode($return, true);
$objectstodelete = array();
foreach($return["results"] as $result){
	$objectstodelete[] = $result["objectId"];	//Only storing objectids to delete.
}

//Print out runtime for debugging.
echo "Objects To Delete Loaded In: ";
echo time()-$startTime;
echo "<br>";

/*******   Get list of cities that already have images    ***************************************/
$ch = curl_init("https://api.parse.com/1/classes/CityImages?limit=1000");
$headers = array("X-Parse-Application-Id: mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL",
				"X-Parse-REST-API-Key: aRzlV8V7nuKE28llMLlX5yjkIF9tGp1NkJrosSQH",
				"Content-type: application/json");

curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
if($return = curl_exec($ch)) curl_close($ch);
else die(curl_error($ch));

$return = json_decode($return, true);
$citieswithimages = array();

foreach($return["results"] as $cityimage){
	$citieswithimages[] = $cityimage["airportCode"];	//Just store the airport codes for images we have.
}

/*******   Get Airports from CSV file   *****************************************************/

$airports = array();
if(($file = fopen("airports.csv","r")) !== false){
	while($data = fgetcsv($file)){
		$airports[] = $data[0];
	}
}
else die('unable to get companies');

/*******   Organize new flights and post them to Parse.com    ******************************************/

$postTime = time();					//Used to calculate post run time.
$airportimagestoadd = array();		//List of airport codes getting new images posted to server.

$count = 0;
//Run through all airports that are listed in CSV file.
foreach($airports as $airport){
	if($count == 50) break;
	$imagestopost = array(); 		//Used to store list of images being pushed to parse.com
	
	//Get data from Kayak RSS feeds within 2 months.
	$rss = xmlToArray("http://www.kayak.com/h/rss/buzz?code=".$airport."&tm=".date("Ym"));
	$rss2 = xmlToArray("http://www.kayak.com/h/rss/buzz?code=".$airport."&tm=".(date("Ym")+1));
	$rss = array_merge($rss, $rss2); 	//Merge 2 results
	usort($rss, "date_compare");		//Sort according to date, earliest first
	
	//Loop through deals and get rid of deals longer than 11 days, repeat deals and deals leaving before tomorrow.
	$tempkeys = array();
	foreach($rss as $key=>$deal){
		$date1 = explode("/",$deal["departDate"]);	//Turn date into array
		$date2 = explode("/",$deal["returnDate"]);
		$datetime1 = new DateTime('20'.$date1[2].'-'.$date1[0]."-".$date1[1]);	//Turn date into DateTime object.
		$datetime2 = new DateTime('20'.$date2[2].'-'.$date2[0]."-".$date2[1]);
		$tomorrow = new DateTime('tomorrow');	//Get tomorrow's date.
		$interval = $datetime1->diff($datetime2, true);		//Find the difference between 2 days
		//Push deals to temp array if they meet criteria.
		if(!in_array($key,$tempkeys) && ($interval->format('%a') < 11) && ($interval->format('%a') > 1) && ($datetime1 > $tomorrow)){
			$tempkeys[] = $key;
		}
	}
	$temp = array();
	foreach($tempkeys as $key){
		$temp[] = $rss[$key];
	}
	$rss = $temp;
	array_splice($rss, 9);
	//Check if the deal's destination has an image url stored in Parse.
	//If not then find an image using Yahoo.
	foreach($rss as $key=>$deal){
		//Check if in parse.com or already just got the image.
		if(!in_array($deal['destCode'],$citieswithimages) && !in_array($deal['destCode'],$airportimagestoadd)){
			$url = "http://yboss.yahooapis.com/ysearch/images?q=".str_replace(",","",$deal["destLocation"]); //URL of request
			$method = "GET"; 	//Set GET method
			$params = null;		//Not sending any data
			try{
				//Use Oauth library to set OAuth Keys.
				$request = new OAuthRequester($url, $method, $params);
				$result = $request->doRequest();	//Run request.
				$response = $result['body'];
				$response = json_decode($response, true);	//Get response as array object.
				$airportimagestoadd[] = $deal['destCode'];	//Add destination as an image we just got.
				//Setup array ready for curl post to parse.com with destination airport code and image URL.
				$imagestopost[] = array("airportCode" => $deal['destCode'], "imageUrl" => $response["bossresponse"]["images"]["results"][0]["url"]);
			}
			catch(OAuthException2 $e){ die('Oauth error');} //Die if OAuth fails.
		}
		
		//Setup Kayak link for buynow button for deal.		
		$depart = explode("/",$deal['departDate']);
		$return = explode("/",$deal['returnDate']);
		//Have to convert dates to correct format for URL.
		//Convert dates to DateTime object to make formatting easier.
		$depart = new DateTime('20'.$depart[2].'-'.$depart[0]."-".$depart[1]);
		$return = new DateTime('20'.$return[2].'-'.$return[0]."-".$return[1]);
		$rss[$key]['kayak_link'] = "http://www.kayak.com/flights#/".$deal['originCode']."-".$deal['destCode']."/".$depart->format('Y-m-d')."/".$return->format('Y-m-d');
		$location = explode(',', $deal['destLocation']);
		$hotel_query_url = "http://api.ean.com/ean-services/rs/hotel/v3/list?cid=55505&minorRev=16&apiKey=bynsqz35cd6qjr9yncw7njb6&locale=en_US&currencyCode=USD&";
		$hotel_query_url .= "xml=<HotelListRequest><arrivalDate>".$depart->format('m/d/Y')."</arrivalDate><departureDate>".$return->format('m/d/Y')."</departureDate><RoomGroup><Room><numberOfAdults>1</numberOfAdults></Room></RoomGroup>";
		$hotel_query_url .= "<city>".str_replace(" ", "%20", $location[0])."</city><stateProvinceCode>".str_replace(" ", "", $location[1])."</stateProvinceCode><numberOfResults>20</numberOfResults></HotelListRequest>";
		
		$hotelData = json_decode(file_get_contents($hotel_query_url), true);
		if(!$hotelData) die("No data captured");
		sleep(1);
		$hotelData = $hotelData['HotelListResponse']['HotelList']['HotelSummary'];
		$minPrice = $hotelData[0]['lowRate'];
		foreach($hotelData as $hotel_deal) {
			$thisPrice = $hotel_deal['lowRate'];
			if ($thisPrice < $minPrice) {
				$minPrice = $thisPrice;
			}
		}
		
		$rss[$key]['hotel_price'] =	$minPrice; 
		$rss[$key]['hotel_link'] = "http://www.expedia.com/Hotel-Search#destination=".$deal['destLocation']."&startDate=".$depart->format('m/d/Y')."&endDate=".$return->format('m/d/Y')."&adults=1&star=0";
		$rss[$key]['link'] = "http://www.expedia.com/Flights-Search?trip=roundtrip&leg1=from:".$deal['originCode'].",to:".$deal['destCode'].",departure:".$depart->format('m/d/Y')."TANYT&leg2=from:".$deal['destCode'].",to:".$deal['originCode'].",departure:".$return->format('m/d/Y')."TANYT&passengers=children:0,adults:1,seniors:0,infantinlap:Y&options=cabinclass:coach,nopenalty:N,sortby:price&mode=search";

	}
	
	//Setup object to be batch posted to parse.com
	$data = array();
	foreach($rss as $deal){
		//Batch post object.
		$data["requests"][] = array("method" => "POST", "path" => "/1/classes/Deals", "body" => $deal);
	}
	//Same thing for images. Batch post object with image URLs
	$imagepost = array();
	foreach($imagestopost as $imagedata){
		$imagepost["requests"][] = array("method" => "POST", "path" => "/1/classes/CityImages", "body" => $imagedata);
	}

	//Run curl post to post to parse.com
	//We are running a batch post to reduce the number of posts we make and speed up script.
	//We can only run 50 requests per batch so we are doing the requests on an airport basis (max requests will be 12 deals).
	$ch = curl_init("https://api.parse.com/1/batch");
	$headers = array("X-Parse-Application-Id: mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL",
					"X-Parse-REST-API-Key: aRzlV8V7nuKE28llMLlX5yjkIF9tGp1NkJrosSQH",
					"Content-type: application/json");
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, 1);								//Set request type as POST
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($imagepost));	//Set post fields as JSON
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	if(curl_exec($ch)) curl_close($ch);
	else die(curl_error($ch));
		
	//Same thing for new image URLs. Curl POST.
	$ch = curl_init("https://api.parse.com/1/batch");
	$headers = array("X-Parse-Application-Id: mfn8KBuLDmeUenYE1VGUYQr2x5YDFJQ669TZ7HSL",
					"X-Parse-REST-API-Key: aRzlV8V7nuKE28llMLlX5yjkIF9tGp1NkJrosSQH",
					"Content-type: application/json");
	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	
	if(curl_exec($ch)) curl_close($ch);
	else die(curl_error($ch));
	$count++;
}
//Print runtime for the Post section.
echo "Objects Posted In: ";
echo time()-$postTime;
echo "<br>";

/*******      Delete old objects      **************************************************************/
//Delete old objects from parse.com database.
//We do this at the end because if the script runs while someone
//is using the site, they will still get something.

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

//Print total time.
echo "Total Time: ";
echo time()-$startTime;

/*******************************************************************************************/

