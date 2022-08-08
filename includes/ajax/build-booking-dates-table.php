<?php 
/**
 * 
 *  build-booking-dates-table.php
 *  08/07/2022  Ron Boutilier
 * 
 *  build_booking_dates_table function will update the {$wpdb->prefix}taste_venue_product_booking_daets
 *  table with available dates for products that can be matched up to Amadeus (other GDS's?) offers.
 * 	This will most likely be run on a nightly basis.
 * 
 * 
 * 	TODO:  need log file for errors!!!
 * 
 */

defined('ABSPATH') or die('Direct script access disallowed.');

require_once TBOOKING_PLUGIN_INCLUDES.'/api/retrieve-amadeus-api.php';

function build_booking_dates_table($start_date) {
	global $wpdb;

	// get the venue booking id's and listof products with booking match terms
	$sql = "
		SELECT vprods.venue_id, vbook.hotel_booking_id, vprods.product_id, vprod_book.match_terms
		FROM {$wpdb->prefix}taste_venue_booking vbook
		JOIN {$wpdb->prefix}taste_venue_products vprods ON vprods.venue_id = vbook.venue_id
		JOIN {$wpdb->prefix}taste_venue_product_booking vprod_book ON vprod_book.product_id = vprods.product_id
	";

	$product_booking_rows = $wpdb->get_results($sql, ARRAY_A);

	// echo  "<pre>";

	// print_r($product_booking_rows);

	$prod_booking_info = get_prod_booking_info($product_booking_rows);

	// print_r($prod_booking_info);

	// echo  "</pre>";

	$avail_days = load_prod_booking_dates_table($prod_booking_info, $start_date);

	if (false === $avail_days) {
		return;
	}

	if ($avail_days) {
		echo "<h2>$avail_days booking dates were assigned";
	} else {
		echo "<h2>No available booking dates were found";
	}

}

function get_prod_booking_info($prod_booking_rows) {
	$prod_booking_info = array();
	foreach($prod_booking_rows as $pb_row) {
		$booking_id = $pb_row['hotel_booking_id'];
		$match_terms = unserialize($pb_row['match_terms']);
		if (isset($prod_booking_info[$booking_id])) {
			$prod_booking_info[$booking_id][] = $match_terms;
		} else {
			$prod_booking_info[$booking_id] = array ( $match_terms );
		}
	}
	return $prod_booking_info;
}

function load_prod_booking_dates_table($prod_booking_info, $start_date) {
	global $wpdb;

	$total_avail_dates = 0;
	$hotel_id_list = array_keys($prod_booking_info);
	$hotel_ids = implode(',', $hotel_id_list);

	// starting with start date, loop through the next 30 days
	$date_i = date_create($start_date);
	for ($day_cnt = 0; $day_cnt < 30; $day_cnt++) {
		$sql_date = date_format($date_i, "Y-m-d");
		echo "<p>cnt: $day_cnt  -> ", $sql_date, "</p>";
		// delete all records for this date to insert from latest api data

		$sql = "
			DELETE FROM {$wpdb->prefix}taste_venue_product_booking_dates
			WHERE room_date = %s
		";

		$sql = $wpdb->prepare($sql,$sql_date);

		$db_result = $wpdb->query($sql);

		if (false === $db_result) {
			echo "Error deleting wp_taste_venue_product_booking_dates record ";
			return false;
		}

		// run api for all the hotel ids
		$avail_dates = load_prod_booking_table_single_date($hotel_ids, $sql_date);
		if (false === $avail_dates) {
			echo "Error inserting booking data for date $sql_date";
			die;
		}
		$total_avail_dates += $avail_dates;

    $date_i = date_add($date_i, date_interval_create_from_date_string("1 day"));
	}
}

function load_prod_booking_table_single_date($hotel_ids, $sql_date) {
	global $wpdb;

	$api_data = get_hotel_shopping_by_hotel_list($hotel_ids, $sql_date);

	$hotel_rows = get_hotel_booking_data($api_data);

	print_r($hotel_rows);
	die;

	return 0;

}

function get_hotel_booking_data($api_data) {
  $hotels_data = $api_data['data'];
  $hotel_rows = array();

  foreach($hotels_data as $hotel_data) {
    $hotel_id = $hotel_data['hotel']['hotelId'];

    $offers = get_offer_info( $hotel_data['offers']);
    $hotel_offer_count = count($offers);

    $trows = array_reduce($offers, function($trows, $offer) {
      $id = $offer['id'];
      $rtype = $offer['rtype'];
      $rcategory = $offer['rcategory'];
      $rbeds = $offer['rbeds'];
      $rbedtype = $offer['rbedtype'];
      $desc = $offer['desc'];
      $adults = $offer['adults'];
      $price = $offer['price'];
  
      $trows .= "<tr>";
      $trows .= "
        <td>$id</td>
        <td>$rtype</td>
        <td>$rcategory</td>
        <td>$rbeds</td>
        <td>$rbedtype</td>
        <td>$desc</td>
        <td>$adults</td>
        <td>$price</td>
        ";
      $trows .= "</tr>";
      return $trows;
    }, "");

    $hotel_rows[$hotel_id] = array('offer_count' => $hotel_offer_count, 'trows' => $trows);
  }

	return $hotel_rows;
}

function get_offer_info($offers) {
	$offers_info = array();
	foreach ($offers as $offer) {    
		$id = $offer['id'];
		$room = $offer['room'];
		$rtype = $room['type'];
		$rcategory = $room['typeEstimated']['category'];
		$rbeds = isset($room['typeEstimated']['beds']) ? $room['typeEstimated']['beds'] : "n/a";
		$rbedtype = isset($room['typeEstimated']['bedType']) ? $room['typeEstimated']['bedType'] : "n/a";
		$desc = $room['description']['text'];
		$adults = $offer['guests']['adults'];
		$price = $offer['price']['total'];

		$offers_info[] = array(
			'id' => $id,
			'rtype' => $rtype,
			'rcategory' => $rcategory,
			'rbeds' => $rbeds,
			'rbedtype' => $rbedtype,
			'desc' => $desc,
			'adults' => $adults,
			'price' => $price,
		);
	}


	$sort_column = array_column($offers_info, 'rtype');
	array_multisort($sort_column, SORT_ASC, $offers_info);
	return $offers_info;
}
