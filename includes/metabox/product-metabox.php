<?php 
/**
 *  product-metabox.php
 * 
 *  create metabox in the products entry screen
 *  for setting up a product booking offer
 * 
 * 	08/06/2022	Ron Boutiier
 */

defined('ABSPATH') or die('Direct script access disallowed.');

/**
 * Register meta box.
 */
function tbooking_register_product_meta_box() {

	// $post_types = array('product', 'post', 'page');
	$post_types = array('product');

	foreach($post_types as $p_type) {
		add_meta_box( 'tbooking-product-booking-setup-box', __( 'Booking Setup'), 'tbooking_display_product_booking_setup_box', $p_type, 'normal', 'high' );
	}

}
add_action( 'add_meta_boxes', 'tbooking_register_product_meta_box' );

/**
 *  Callback to create the product venue metabox
 */
function tbooking_display_product_booking_setup_box($post_info) {
	global $wpdb;

	echo '<h3>Setup Amadeus Booking Terms</h3>';
	$booking_setup = 0;
	if (property_exists($post_info, 'ID')) {
		// need to check for current booking setup
		$booking_setup_row = $wpdb->get_results($wpdb->prepare("
			SELECT match_terms FROM {$wpdb->prefix}taste_venue_product_booking
			WHERE product_id = %d
			", $post_info->ID), ARRAY_A
		);
		if (count($booking_setup_row)) {
			$booking_setup = $booking_setup_row[0]['match_terms'];
		}
	}

	display_booking_setup($booking_setup);

}

function display_booking_setup($match_terms) {
	$match_terms_array = unserialize($match_terms);
	$rtype = isset($match_terms_array['rtype']) ? $match_terms_array['rtype'] : '';
	$rcat = isset($match_terms_array['rcat']) ? $match_terms_array['rcat'] : '';
	$bedtype = isset($match_terms_array['bedtype']) ? $match_terms_array['bedtype'] : '';
	$rdesc = isset($match_terms_array['rdesc']) ? $match_terms_array['rdesc'] : '';
	?>
		<p class="form-field">
			<label for="tb-product-booking-rtype">Room Type</label>
			<input type="text" id="tb-product-booking-rtype" name="tb-product-booking-rtype" value="<?php echo $rtype ?>" />
		</p>

		<p class="form-field">
			<label for="tb-product-booking-rcat">Room Category</label>
			<input type="text" id="tb-product-booking-rcat" name="tb-product-booking-rcat" value="<?php echo $rcat ?>" />
		</p>

		<p class="form-field">
			<label for="tb-product-booking-bedtype">Bed Type</label>
			<input type="text" id="tb-product-booking-bedtype" name="tb-product-booking-bedtype" value="<?php echo $bedtype ?>" />
		</p>

		<p class="form-field">
			<label for="tb-product-booking-rdesc">Room Description</label>
			<input type="text" id="tb-product-booking-rdesc" name="tb-product-booking-rdesc" value="<?php echo $rdesc ?>" />
		</p>
	<?php
}

/**
 *  Set up save of Booking Setup meta box
 */
function tbooking_save_booking_setup_metabox($product_id, $product) {
	global $wpdb;
	if (wp_is_post_autosave($product_id) || wp_is_post_revision( $product_id )) {
		return;
	}
	// echo '<h1><pre>POST: ', var_dump($_POST), '</pre></h1>';
	// echo '<h1><pre>REQUEST: ', var_dump($_REQUEST), '</pre></h1>';
	// die(); 
	// might be here through quick/bulk edit.  if so, just return 
	if (!count($_POST)) {
		return;
	}
	
	$rtype = isset($_POST['tb-product-booking-rtype']) ? sanitize_text_field($_POST['tb-product-booking-rtype']) : '';
	$rcat = isset($_POST['tb-product-booking-rcat']) ? sanitize_text_field($_POST['tb-product-booking-rcat']) : '';
	$bedtype = isset($_POST['tb-product-booking-bedtype']) ? sanitize_text_field($_POST['tb-product-booking-bedtype']) : '';
	$rdesc = isset($_POST['tb-product-booking-rdesc']) ? sanitize_text_field($_POST['tb-product-booking-rdesc']) : '';
	$match_terms_array = array(
		'rtype' => $rtype,
		'rcat' => $rcat,
		'bedtype' => $bedtype,
		'rdesc' => $rdesc,
	);
	$match_terms = serialize($match_terms_array);

	$sql = "
	INSERT INTO {$wpdb->prefix}taste_venue_product_booking
	(product_id, match_terms)
	VALUES (%d, %s)
	ON DUPLICATE KEY UPDATE
		product_id = %d,
		match_terms = %s
	";
	$parms = array($product_id, $match_terms, $product_id, $match_terms);

	$rows_affected = $wpdb->query(
		$wpdb->prepare($sql, $parms)
	);

}
add_action('save_post_product', 'tbooking_save_booking_setup_metabox', 10, 2);
