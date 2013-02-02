<?php

$airports = array();
if(($file = fopen("airports.csv","r")) !== false){
	while($data = fgetcsv($file)){
		$airports[] = $data[0];
	}
}
else die('unable to get companies');

$xml = file_get_contents("http://www.kayak.com/h/rss/buzz?code=".$airports[0]);
print_r($xml);


/*
foreach($airports as $airport){
	$xml = simplexml_load_file("http://www.kayak.com/h/rss/buzz?code=".$airport);
	echo $xml;
}
*/