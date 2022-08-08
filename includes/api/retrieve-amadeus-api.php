<?php


defined('ABSPATH') or die('Direct script access disallowed.');

require_once TBOOKING_PLUGIN_INCLUDES.'/api/amadeus-config.php';
require_once TBOOKING_PLUGIN_INCLUDES.'/api/oauth-curl.php';

function get_hotel_shopping_by_hotel_list($hotel_list, $dt) {

	$post_string = "grant_type=client_credentials";
	$cl_sec = CL_SEC1 . CL_SEC2;
	$post_string .= "&client_id=" . CLIENT_ID . "&client_secret=" . $cl_sec;
	
	$full_auth_url = ACCESS_TOKEN_URL;
	
	$return_val = call_oauth_curl($full_auth_url, $post_string);
	
	$access_info = json_decode($return_val);
	
	$access_token = $access_info->access_token;
	$api_url = API_URL;

	$api_url .= HOTEL_SHOPPING_BY_ID . "?hotelIds=$hotel_list&bestRateOnly=false&checkInDate=$dt";
		
// echo $api_url;
// die;

	$api_return_val = call_amadeus_api($api_url, $access_token);

	$api_data = json_decode($api_return_val,true);
	return $api_data;
}