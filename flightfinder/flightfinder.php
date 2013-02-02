<?php

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
	/*
	for($i=0;$i<count($rss);$i++){
		$url = "http://images.google.com/search?tbm=isch&q=beautiful+".str_replace(" ","+",str_replace(",","",$rss[$i]["destLocation"]));
		$page = file_get_contents($url);
		preg_match_all('/<img[^>]+>/i',$page, $result); 
		$pos = strpos($result[0][0], 'src="');
		$src = substr($result[0][0], $pos);
		$src = str_replace(array('src="','"','>'), "", $src);
		echo $rss[$i]["destImage"] = $src;
		die();
	}
	*/
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

