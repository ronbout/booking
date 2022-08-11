<?php 

defined('ABSPATH') or die('Direct script access disallowed.');


function tb_ajax_build_booking_dates() {

	if (!check_ajax_referer('tb-admin-ajax-nonce','security', false)) {
		echo '<h2>Security error loading data.  <br>Please Refresh the page and try again.</h2>';
		wp_die();
	}

	$start_date = $_POST['start_date'];

	require_once TBOOKING_PLUGIN_INCLUDES.'/ajax/build-booking-dates-table.php';
	build_booking_dates_table($start_date);

	wp_die();
}

function tb_ajax_run_amadeus_api() {

	if (!check_ajax_referer('taste-booking-nonce','security', false)) {
		echo '<h2>Security error loading data.  <br>Please Refresh the page and try again.</h2>';
		wp_die();
	}
	if (!isset($_POST['api_form_data']) ) {
		echo 'Missing api settings info';
		wp_die();
	}

	$api_form_data = $_POST['api_form_data'];

	require_once TBOOKING_PLUGIN_INCLUDES.'/api/run-amadeus-api.php';
	run_amadeus_api($api_form_data);

	wp_die();
}

if ( is_admin() ) {
	add_action('wp_ajax_build_booking_dates','tb_ajax_build_booking_dates');
	add_action('wp_ajax_run_amadeus_api','tb_ajax_run_amadeus_api');
}