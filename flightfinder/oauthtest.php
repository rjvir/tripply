<?php

include_once "oauth/OAuthStore.php";
include_once "oauth/OAuthRequester.php";

$key = 'dj0yJmk9MmNaTlRDakoyMjZMJmQ9WVdrOVJYTlhUVXRVTjJjbWNHbzlNVGsxTlRBNU9ETTJNZy0tJnM9Y29uc3VtZXJzZWNyZXQmeD1jYw--'; // this is your consumer key
$secret = '8edb8e5159d5dbfa97d7e2ad8ad0d3bc79415423'; // this is your secret key

$options = array( 'consumer_key' => $key, 'consumer_secret' => $secret );
OAuthStore::instance("2Leg", $options );

$url = "http://yboss.yahooapis.com/ysearch/images?q=detroit"; // this is the URL of the request
$method = "GET"; // you can also use POST instead
$params = null;

try
{
        // Obtain a request object for the request we want to make
        $request = new OAuthRequester($url, $method, $params);

        // Sign the request, perform a curl request and return the results, 
        // throws OAuthException2 exception on an error
        // $result is an array of the form: array ('code'=>int, 'headers'=>array(), 'body'=>string)
        $result = $request->doRequest();
        
        $response = $result['body'];
		echo $response;
		
}
catch(OAuthException2 $e)
{

}