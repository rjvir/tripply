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
    $a = $a[departDate];
    $b = $b[departDate];
    $a = preg_replace('\/', '', $a);
    $b = preg_replace('\/', '', $b);
    return ($a < $b);
}



$airports = array();
if(($file = fopen("airports.csv","r")) !== false){
	while($data = fgetcsv($file)){
		$airports[] = $data[0];
	}
}
else die('unable to get companies');

$rss = json_decode(parse("http://www.kayak.com/h/rss/buzz?code=".$airports[0]."&tm=".date("Ym")));
uksort($rss, "date_compare");
print json_encode($rss);