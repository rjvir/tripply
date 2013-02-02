<?php

$data = array();
for($i = 0; $i<count($rss);$i++){
	$data["requests"][] = array("method" => "POST", "path" => "/1/classes/Deals", "body" => $rss[$i]);
}

echo "<pre>";
echo json_encode($data, JSON_PRETTY_PRINT);
echo "</pre>";

/*
$ch = curl_init("https://api.parse.com/1/classes/Deals");
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

foreach($airports as $airport){
	$xml = simplexml_load_file("http://www.kayak.com/h/rss/buzz?code=".$airport);
	echo $xml;
}
*/

