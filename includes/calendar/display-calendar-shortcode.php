<?php


function display_calendar_shortcode() {

	$cal = new BasicCalendar(array());
	$cal->set_default_day_colors("black", "yellow");

	$product_id = get_the_ID();

	// get the available dates from db
	$date_list = get_available_dates($product_id);

	// book_days($cal, array(14,15));
	avail_days($cal, $date_list);

	echo "<h2>Booking Availability Calendar</h2>";
	$cal->display();

}

function get_available_dates($product_id) {
	global $wpdb;

	$sql = "
		SELECT room_date 
		FROM {$wpdb->prefix}taste_venue_product_booking_dates
		WHERE available_flag = 1
			AND product_id = %d
	";

	$sql = $wpdb->prepare($sql, $product_id);
	$avail_booking_rows = $wpdb->get_results($sql, ARRAY_A);

	if (!$avail_booking_rows || !count($avail_booking_rows)) {
		return array();
	}

	$avail_dates = array_column($avail_booking_rows, 'room_date');
	return $avail_dates;

}

