<?php

/*
    Plugin Name: TheTaste Booking Plugin
    Plugin URI: http://thetaste.ie
    Description: Various functionalities for theTaste.ie hotel booking system
		Version: 1.0.0
		Date: 7/31/2022
    Author: Ron Boutilier
    Text Domain: taste-plugin
 */

defined('ABSPATH') or die('Direct script access disallowed.');

define('TBOOKING_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TBOOKING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('TBOOKING_PLUGIN_INCLUDES', TBOOKING_PLUGIN_PATH.'includes');
define('TBOOKING_PLUGIN_INCLUDES_URL', TBOOKING_PLUGIN_URL.'includes');


require_once TBOOKING_PLUGIN_INCLUDES.'/activation-deactivation.php';

register_activation_hook( __FILE__, 'tbooking_activation' );
register_deactivation_hook( __FILE__, 'tbooking_activation' );

require_once TBOOKING_PLUGIN_INCLUDES.'/calendar/display-calendar-shortcode.php';
require_once TBOOKING_PLUGIN_INCLUDES.'/calendar/BasicCalendar.php';

// add shortcode definitions
function tb_add_shortcodes() {
	add_shortcode("BOOKING-CALENDAR", "display_calendar_shortcode");
}
add_action("init", "tb_add_shortcodes");

if (is_admin()) {
	require_once TBOOKING_PLUGIN_INCLUDES.'/metabox/product-metabox.php';
// 	require_once TBOOKING_PLUGIN_INCLUDES.'/admin/admin-enqueues.php';
// 	require_once TBOOKING_PLUGIN_INCLUDES.'/admin/list-pages/Taste_list_table.php';
// 	require_once TBOOKING_PLUGIN_INCLUDES.'/admin/list-pages/transactions/tf-view-order-trans-page.php';
// 	require_once TBOOKING_PLUGIN_INCLUDES.'/admin/list-pages/venues/tf-view-venues-page.php';
// 	require_once TBOOKING_PLUGIN_INCLUDES.'/admin/list-pages/payments/tf-view-payments-page.php';
// 	require_once TBOOKING_PLUGIN_INCLUDES.'/admin/tf-admin-menus.php';
// 	require_once TBOOKING_PLUGIN_INCLUDES.'/admin/wc-settings/wc-settings.php';
}

// // enqueues 
require_once TBOOKING_PLUGIN_INCLUDES.'/enqueues.php';
require_once TBOOKING_PLUGIN_INCLUDES.'/ajax/ajax-functions.php';
require_once TBOOKING_PLUGIN_INCLUDES.'/functions.php';

// /* some helpful CONSTANTS */
// define('TASTE_TRANS_CRON_HOOK', 'taste_trans_cron_event');


/**
 * Page Templates setup code
 */
// set up page templates
function tbooking_add_template ($templates) {
	$templates['test-build-booking-dates.php'] = 'Build Booking 30 Day Calendar';
	$templates['deals.php'] = 'TheTaste Deals';
	$templates['amadeus.php'] = 'Amadeus API Data';
	return $templates;
	}
add_filter ('theme_page_templates', 'tbooking_add_template');

function tbooking_redirect_page_template ($template) {
	if (is_page_template('test-build-booking-dates.php')) {
		$template = plugin_dir_path( __FILE__ ).'page-templates/test-build-booking-dates.php';
	}
	if (is_page_template('deals.php')) {
		$template = plugin_dir_path( __FILE__ ).'page-templates/deals.php';
	}
	if (is_page_template('amadeus.php')) {
		$template = plugin_dir_path( __FILE__ ).'page-templates/amadeus.php';
	}
	return $template;
}
add_filter ('page_template', 'tbooking_redirect_page_template');






//  /**********************************************
//  * Code for hooks/filters goes here
//  **********************************************/

// function taste_hide_giftcert_price($price, $product) {

// 	if ($product->get_meta('hide_reg_price')) {
// 		$price_dom = new DOMDocument();
// 		if ( !$price_dom->loadHTML(mb_convert_encoding($price, 'HTML-ENTITIES'))) {
// 			return $price;
// 		}
// 		$del_price = $price_dom->getElementsByTagName('ins')->item(0);
// 		return $price_dom->saveXML($del_price);
		
// 	} else {
// 		return $price;
// 	}

// }
// add_filter( 'woocommerce_get_price_html', 'taste_hide_giftcert_price', 10, 2 );

  
//  /*************************************************
//  * Set up nightly cron job to update trans table
//  *************************************************/

// // // need to set up the cron job that will create the jobs-sitemap.xml above
// add_action(TASTE_TRANS_CRON_HOOK, 'tf_update_trans_table');

// add_filter( 'cron_schedules', 'taste_add_cron_interval' );
// function taste_add_cron_interval( $schedules ) {
//     $schedules['two_hours'] = array(
//             'interval'  => 7200, // time in seconds
//             'display'   => 'Every Two Hours',
//     );
//     return $schedules;
// }


// // function taste_trans_cron_activation() {
// // 	// build start time for 12:01am
// // 	$start_time = strtotime(date('Y-m-d 00:01'));
	
// // 	if ( !wp_next_scheduled( TASTE_TRANS_CRON_HOOK ) ) {
// // 		wp_schedule_event( time(), 'daily', TASTE_TRANS_CRON_HOOK);
// // 	}
// // }
// // add_action('wp', 'taste_trans_cron_activation');

// function tf_update_trans_table() {
	
// 	require_once TBOOKING_PLUGIN_INCLUDES.'/build-trans-bulk-cron.php';
	
// }
