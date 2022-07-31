<?php 

defined('ABSPATH') or die('Direct script access disallowed.');


// function tf_ajax_set_trans_cron() {

// 	if (!check_ajax_referer('tf-admin-ajax-nonce','security', false)) {
// 		echo '<h2>Security error loading data.  <br>Please Refresh the page and try again.</h2>';
// 		wp_die();
// 	}
// 	if (!isset($_POST['cron_on_off']) || !isset($_POST['frequency'])) {
// 		echo 'Missing cron info';
// 		wp_die();
// 	}

// 	$cron_on_off = $_POST['cron_on_off'];
// 	$frequency = $_POST['frequency'];

// 	require_once TFINANCIAL_PLUGIN_INCLUDES.'/ajax/set-trans-cron.php';
// 	set_trans_cron($cron_on_off, $frequency);

// 	wp_die();
// }

// if ( is_admin() ) {
// 	add_action('wp_ajax_set_trans_cron','tf_ajax_set_trans_cron');
// }