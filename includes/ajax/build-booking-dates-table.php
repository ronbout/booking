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
		echo "<h2>No available booking dates were found";
		return;
	}

	if ($avail_days) {
		echo "<h2>$avail_days booking dates were assigned";
	} else {
		echo "<h2>No available booking dates were found";
	}

	return;
}

function get_prod_booking_info($prod_booking_rows) {
	$prod_booking_info = array();
	foreach($prod_booking_rows as $pb_row) {
		$booking_id = $pb_row['hotel_booking_id'];
		$match_terms = unserialize($pb_row['match_terms']);
		if (isset($prod_booking_info[$booking_id])) {
			$prod_booking_info[$booking_id][] = array(
				'product_id' => $pb_row['product_id'],
				'match_terms' => $match_terms,
			);
		} else {
			$prod_booking_info[$booking_id] = array(array(
				'product_id' => $pb_row['product_id'],
				'match_terms' => $match_terms,
			));
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
	for ($day_cnt = 0; $day_cnt < 7; $day_cnt++) {
		$sql_date = date_format($date_i, "Y-m-d");
		echo "<p>Day count: $day_cnt  -> ", $sql_date, "</p>";
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

		// run api for all the hotel ids and load table for this date
		$avail_dates = load_prod_booking_table_single_date($prod_booking_info, $hotel_ids, $sql_date);
		if (false === $avail_dates) {
			echo "Error inserting booking data for date $sql_date";
			die;
		}
		$total_avail_dates += $avail_dates;

    $date_i = date_add($date_i, date_interval_create_from_date_string("1 day"));
	}

	return $total_avail_dates;
}

function load_prod_booking_table_single_date($prod_booking_info, $hotel_ids, $sql_date) {
	global $wpdb;

	$api_data = get_hotel_shopping_by_hotel_list($hotel_ids, $sql_date);

	$hotel_rows = get_hotel_booking_data($api_data, $sql_date);

	// echo '<pre>';
	// print_r($hotel_rows);
	
	// echo '<pre>';
	// die;

	$match_cnt = 0;

	foreach($prod_booking_info as $hotel_id => $prod_rows) {
		foreach ($prod_rows as $prod_row) {
			$product_id = $prod_row['product_id'];
			$match_terms = $prod_row['match_terms'];
      $rtype = $match_terms['rtype'];
      $rcat = $match_terms['rcat'];
      $bedtype = $match_terms['bedtype'];
      $rdesc = $match_terms['rdesc'];
			if (!$rtype && !$rcat && !$bedtype && !rdesc) {
				continue;
			}
			$hotel_offers = $hotel_rows[$hotel_id];
			foreach($hotel_offers as $hotel_offer) {
				if ($rtype) {
					if ($rtype != $hotel_offer['rtype']) {
						continue;
					}
				}
				if ($rcat) {
					if ($rcat != $hotel_offer['rcat']) {
						continue;
					}
				}
				if ($bedtype) {
					if ($bedtype != $hotel_offer['bedtype']) {
						continue;
					}
				}
				if ($rdesc) {
					if (false === strpos($hotel_offer['bedtype'], $bedtype)) {
						continue;
					}
				}
				// must have been matched
				$db_result = insert_prod_booking_date_table($product_id, $hotel_offer);
				if (false === $db_result) {
					echo "Error insert product booking date info";
					die;
				}
				$match_cnt++;
				break;
			}
		}
	}
	return $match_cnt;

}

function get_hotel_booking_data($api_data, $sql_date) {
  $hotels_data = $api_data['data'];
  $hotel_rows = array();

  foreach($hotels_data as $hotel_data) {
    $hotel_id = $hotel_data['hotel']['hotelId'];
		$available = $hotel_data['available'];
    $offers = get_offer_info( $hotel_data['offers'], $available, $sql_date );

    $hotel_rows[$hotel_id] = $offers;
  }

	return $hotel_rows;
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
			'rcat' => $rcategory,
			'rbeds' => $rbeds,
			'bedtype' => $rbedtype,
			'rdesc' => $desc,
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

function insert_prod_booking_date_table($product_id, $hotel_offer) {
	global $wpdb;
	// echo "<p>", $product_id, "</p>";
	// echo "<pre>";
	// print_r($hotel_offer);
	// echo "</pre>";
	$insert_table = "{$wpdb->prefix}taste_venue_product_booking_dates";

	$insert_data = array( 
		'product_id' => $product_id,
		'room_date' => $hotel_offer['offer_date'],
		'available_flag' => $hotel_offer['available'],
		'booking_date_id' => $hotel_offer['id'],
		'offer_api' => $hotel_offer['offer_api'],
	);

	$insert_format = array('%d', '%s', '%d', '%s', '%s');
	return $wpdb->insert($insert_table, $insert_data, $insert_format);
}
