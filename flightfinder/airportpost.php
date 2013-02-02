<?php
	
$airports = array();
if(($file = fopen("airports.csv","r")) !== false){
	while($data = fgetcsv($file)){
		$airports[] = $data;
	}
}
else die('unable to get companies');

$data = array();
foreach($airports as $airport){
	$data["requests"][] = array("method" => "POST", "path" => "/1/classes/Cities", "body" => array("airport_code" => $airport[0], "city" => $airport[1], "state" => $airport[2]));
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

