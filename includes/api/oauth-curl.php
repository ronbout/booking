<?php

function call_oauth_curl( $url, $post_string='' ) {
	// create curl resource
 $ch = curl_init();

	// set url
	curl_setopt($ch, CURLOPT_URL, $url);

	//return the transfer as a string
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	curl_setopt($ch, CURLOPT_TIMEOUT, 180); 

	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

	curl_setopt($ch, CURLOPT_USERAGENT, 'Hotels');

	curl_setopt($ch, CURLOPT_POST, 1);

	if ($post_string) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string );
	}

	// set up http header fields
	$headers = array(
	'Accept: *',
	'Cache-Control: no-cache',
	'Content-Type: application/x-www-form-urlencoded',
	);


	curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


 // add code to accept https certificate
 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

 // $output contains the output string
 $output = curl_exec($ch);
 // close curl resource to free up system resources
 curl_close($ch); 
 return $output;
}

function call_amadeus_api( $url, $access_token ) {
	// create curl resource
 $ch = curl_init();

 // set url
 curl_setopt($ch, CURLOPT_URL, $url);

 //return the transfer as a string
 curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

	curl_setopt($ch, CURLOPT_TIMEOUT, 180); 

	curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);

	curl_setopt($ch, CURLOPT_USERAGENT, 'booking');
 
	//  curl_setopt($ch, CURLOPT_POST, 1);
	//  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string );
	 // set up http header fields
	 $headers = array(
		 'Cache-Control: no-cache',
		 'Content-Type: application/json',
		 'Authorization: Bearer ' . $access_token
	 );


	 curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);


	// add code to accept https certificate
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

	// $output contains the output string
	$output = curl_exec($ch);
	// close curl resource to free up system resources
	curl_close($ch); 
	return $output;
}

function build_post_string( $post_array ) {
	if( ! is_array($post_array) ) {
		return false;
	}
	
	$out_string = '';
	foreach( $post_array as $key => $value ) {
		if ( $out_string != '' ) {
			$out_string .= '&';
		}
		$out_string .= urlencode($key) . '=' . urlencode($value);
	}
	
	return $out_string;
}

