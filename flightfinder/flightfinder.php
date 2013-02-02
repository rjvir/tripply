<?php

$airports = array();
if(($file = fopen("airports.csv","r")) !== false){
	while($data = fgetcsv($file)){
		$airports[] = $data[0];
	}
}
else die('unable to get companies');
echo $airports[0];

echo $xml = simplexml_load_file("http://www.kayak.com/h/rss/buzz?code=".$airports[0]);
/*
foreach($airports as $airport){
	$xml = simplexml_load_file("http://www.kayak.com/h/rss/buzz?code=".$airport);
	echo $xml;
}
*/