<?php

defined('ABSPATH') or die('Direct script access disallowed.');

require_once TBOOKING_PLUGIN_INCLUDES.'/api/amadeus-config.php';
require_once TBOOKING_PLUGIN_INCLUDES.'/api/oauth-curl.php';

function run_amadeus_api($api_form_data) {

	$prod_flag = ("prod" == $api_form_data['prodFlag']) ? true : false;
	$api_type = $api_form_data['apiType'];
	$api_settings = $api_form_data['apiSettings'];
	$raw_flag = true;

	if ($prod_flag) {
    $full_auth_url = AMADEUS_ACCESS_TOKEN_URL_PROD;
    $api_url = AMADEUS_API_URL_PROD;
    $client_id = AMADEUS_CLIENT_ID_PROD;
    $client_secret = AMADEUS_CLIENT_SECRET_PROD;
  } else {
    $full_auth_url = AMADEUS_ACCESS_TOKEN_URL;
    $api_url = AMADEUS_API_URL;
    $client_id = AMADEUS_CLIENT_ID;
    $client_secret = AMADEUS_CLIENT_SECRET;
  }
	
  $post_string = "grant_type=client_credentials";
  $post_string .= "&client_id=" . $client_id . "&client_secret=" . $client_secret;

  $return_val = call_oauth_curl($full_auth_url, $post_string);
  $access_info = json_decode($return_val);
  $access_token = $access_info->access_token;

	switch($api_type) {
		case "city": 
			$full_api_url = $api_url . get_city_url($api_settings);
			break;
		case "rating":
			$full_api_url = $api_url . get_rating_url($api_settings);
			break;
		case "offers":
			$offers_info = get_offers_url($api_settings);
			$full_api_url = $api_url . $offers_info['url'];
			$check_in_date = $offers_info['check_in_date'];
			$depart_date = $offers_info['depart_date'];
			$raw_flag =("raw"  == $api_settings['rawFlag']) ? true : false;
	}

	$api_return_val = call_amadeus_api($full_api_url, $access_token);
  $api_data = json_decode($api_return_val,true);
	
  echo "<h3>API: $full_api_url </h3>";

  if ($raw_flag) {
    echo "<pre>";
    print_r($api_data);
    echo "</pre>";
    return;
  }

	if (!isset($api_data['data'])) {
		echo "<p>No Offers data returned. </p>";
		echo "<pre>";
		print_r($api_data);
		echo "</pre>";
		return;
	}

  $hotels_data = $api_data['data'];

  $hotel_rows = build_display_info($hotels_data, $dt);
	
	if (!count($hotel_rows)) {
		echo "<h2>No data present</h2>";
	} else {  
		foreach($hotel_rows as $hotel_id => $hotel_info) {
			$trows = $hotel_info['trows'];
			$offer_count = $hotel_info['offer_count'];
			$hotel_name = $hotel_info['hotel_name'];
			?>
			<h2>Hotel: <?php echo $hotel_name?></h2>
			<div>
				<h2>Offer count: <?php echo $offer_count ?></h2>
			</div>
			<table style="font-size:14px;">
				<thead>
					<tr>
						<th scope="col">Room Type</th>
						<th scope="col">Category</th>
						<th scope="col">Beds</th>
						<th scope="col">Bed Type</th>
						<th scope="col">Desc</th>
						<th scope="col">Guests:</th>
						<th scope="col">Price:</th>
						<th scope="col">Comm %:</th>
					</tr>
				</thead>
				<tbody>
					<?php echo $trows ?>  
				</tbody>
			</table>

			<?php
		}
	}
	return;
}

function get_city_url($api_settings) {
	$city_code = $api_settings['cityCode'];
	return HOTEL_LIST_BY_CITY . "?cityCode=$city_code";
}

function get_rating_url($api_settings) {
	$hotels = $api_settings['hotelIds'];
	return HOTEL_RATING_BY_ID . "?hotelIds=$hotels";
}

function get_offers_url($api_settings) {
	$hotels = $api_settings['hotelIds'];
	$offer_date = $api_settings['offerDate'];
	$adults = $api_settings['adults'];
	$nights = $api_settings['nights'];
	$tmp_date = date_create($offer_date);
	date_add($tmp_date, date_interval_create_from_date_string("$nights days"));
	$depart_date = date_format($tmp_date, "Y-m-d");

	$url = HOTEL_SHOPPING_BY_ID . "?hotelIds=$hotels&bestRateOnly=false&adults=$adults&checkInDate=$offer_date&checkOutDate=$depart_date";
	return array(
		'check_in_date' => $offer_date,
		'check_out_date' => $depart_date,
		'url' => $url,
	);
}

function get_offer_info($offers, $available, $sql_date) {
	$offers_info = array();
	foreach ($offers as $offer) {    
		$id = $offer['id'];
		$room = $offer['room'];
		$rtype = $room['type'];
		$rcategory = isset($room['typeEstimated']['category']) ? $room['typeEstimated']['category'] : "n/a";
		$rbeds = isset($room['typeEstimated']['beds']) ? $room['typeEstimated']['beds'] : "n/a";
		$rbedtype = isset($room['typeEstimated']['bedType']) ? $room['typeEstimated']['bedType'] : "n/a";
		$desc = $room['description']['text'];
		$adults = $offer['guests']['adults'];
		$price = $offer['price']['total'];
		$offer_api = $offer['self'];
		$commission = isset($offer['commission']['percentage']) ? $offer['commission']['percentage'] : "no % found";

		$offers_info[] = array(
			'id' => $id,
			'available' => $available,
			'rtype' => $rtype,
			'rcategory' => $rcategory,
			'rbeds' => $rbeds,
			'rbedtype' => $rbedtype,
			'desc' => $desc,
			'adults' => $adults,
			'price' => $price,
			'offer_api' => $offer_api,
			'offer_date' => $sql_date,
			'commission' => $commission,
		);
	}


	$sort_column = array_column($offers_info, 'rtype');
	array_multisort($sort_column, SORT_ASC, $offers_info);
	return $offers_info;
}

function build_display_info($hotels_data, $dt) {
	$hotel_rows = array();
	foreach($hotels_data as $hotel_data) {
		$hotel_id = $hotel_data['hotel']['hotelId'];
		$hotel_name = $hotel_data['hotel']['name'];
		$available = $hotel_data['available'];

		$offers = get_offer_info( $hotel_data['offers'], $available, $dt);
		$hotel_offer_count = count($offers);

		$trows = array_reduce($offers, function($trows, $offer) use ($dt) {
			$id = $offer['id'];
			$rtype = $offer['rtype'];
			$rcategory = $offer['rcategory'];
			$rbeds = $offer['rbeds'];
			$rbedtype = $offer['rbedtype'];
			$desc = $offer['desc'];
			$adults = $offer['adults'];
			$price = $offer['price'];
			$commission = $offer['commission'];
	
			$trows .= "<tr>";
			$trows .= "
				<td>$rtype</td>
				<td>$rcategory</td>
				<td>$rbeds</td>
				<td>$rbedtype</td>
				<td>$desc</td>
				<td>$adults</td>
				<td>$price</td>
				<td>$commission</td>
				";
			$trows .= "</tr>";
			return $trows;
		}, "");

		$hotel_rows[$hotel_id] = array(
			'offer_count' => $hotel_offer_count, 
			'hotel_name' => $hotel_name, 
			'trows' => $trows
		);
	}
	return $hotel_rows;
}