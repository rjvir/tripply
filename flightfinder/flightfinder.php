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

$airports = array();
if(($file = fopen("airports.csv","r")) !== false){
	while($data = fgetcsv($file)){
		$airports[] = $data[0];
	}
}
else die('unable to get companies');

$rss = parse("http://www.kayak.com/h/rss/buzz?code=".$airports[0]."&tm=".date("Ym"));
