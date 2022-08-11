<?php


defined('ABSPATH') or die('Direct script access disallowed.');

require_once TBOOKING_PLUGIN_INCLUDES.'/api/amadeus-config.php';
require_once TBOOKING_PLUGIN_INCLUDES.'/api/oauth-curl.php';

function get_hotel_shopping_by_hotel_list($hotel_list, $dt) {

	$post_string = "grant_type=client_credentials";
	$post_string .= "&client_id=" . AMADEUS_CLIENT_ID . "&client_secret=" . AMADEUS_CLIENT_SECRET;
	
	$full_auth_url = AMADEUS_ACCESS_TOKEN_URL;
	
	$return_val = call_oauth_curl($full_auth_url, $post_string);
	
	$access_info = json_decode($return_val);
	
	$access_token = $access_info->access_token;
	$api_url = AMADEUS_API_URL;

	$api_url .= HOTEL_SHOPPING_BY_ID . "?hotelIds=$hotel_list&bestRateOnly=false&checkInDate=$dt";

	$api_return_val = call_amadeus_api($api_url, $access_token);

	$api_data = json_decode($api_return_val,true);
	return $api_data;
}