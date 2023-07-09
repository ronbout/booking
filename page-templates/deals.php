<?php
/*
Template Name: TheTaste Deals
*/

/**
 *  Date:  8/09/2022
 * 	Author: Ron Boutilier
 */
defined('ABSPATH') or die('Direct script access disallowed.');

$hotel_rows = get_hotel_rows();

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link rel="stylesheet" href="<?php echo TBOOKING_PLUGIN_URL ?>assets/css/font-awesome.min.css">
	<link rel="preconnect" href="https://fonts.gstatic.com">
	<link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;700&display=swap" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;700&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="//code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
	<link rel="stylesheet" href="<?php echo TBOOKING_PLUGIN_INCLUDES_URL ?>/style/css/tbooking-deals.css">

<script
  src="https://code.jquery.com/jquery-3.5.1.min.js"
  integrity="sha256-9/aliU8dGd2tb6OSsuzixeV4y/faTqgFtohetphbbj0="
  crossorigin="anonymous"></script>
	<script	script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx" crossorigin="anonymous"></script>
	<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
	<script src="https://kit.fontawesome.com/90e4bc8c6b.js" crossorigin="anonymous"></script>

	<title>TheTaste Deals Test Page</title>
</head>
<body>
	<main class="container my-3">
		<h2 class="my-3 text-center">Super Duper Amazing Deals from TheTaste's Partners</h2>
		<section class="my-4">
			<?php
				foreach($hotel_rows as $hotel_row) {
					?>
						<div class="row">
							<div class="col-md-2"></div>
							<div class="col-md-8">
								<?php display_hotel_card($hotel_row) ?>
							</div>
							<div class="col-md-2"></div>
						</div>
					<?php
				}
			?>
		</section>
	</main>
</body>
</html>

<?php

// function display_hotel_card($hotel_info) {
// 	$rating = $hotel_info['Rating'];
// 	$ven_name = $hotel_info['name'];
// 	$desc = $hotel_info['desc'];
// 	$thumb_src = $hotel_info['thumbnail_src'];
// 	
/*
 		<div class="card text-center">
 			<div class="card-header">
 				Rating: <?php echo $rating ?>
 			</div>
   		<img width="200" height="200" src="<?php echo $thumb_src ?>" class="card-img-top" alt="Hotel Thumbnail Image">
 			<div class="card-body">
 				<h4 class="card-title"><?php echo $ven_name ?></h4>
 				<p class="card-text"><?php echo $desc ?></p>
 				<a href="#" class="btn btn-primary">See Deals</a>
 			</div>
 			<div class="card-footer text-muted">
 				Deals starting at &euro;240
 			</div>
 		</div>
 	<?php
// }
*/

function display_hotel_card($hotel_info) {
	$rating = $hotel_info['Rating'];
	$ven_name = $hotel_info['name'];
	$desc = $hotel_info['desc'];
	$desc_disp = substr($desc, 0, 180) . "...";
	$thumb_src = $hotel_info['thumbnail_src'];
	?>
		<div class="card mb-3" style="max-width: 800px;">
			<div class="card-header">
				Rating: <?php echo $rating ?>
			</div>
			<div class="row no-gutters">
				<div class="col-md-4">
					<img src="<?php echo $thumb_src ?>" class="card-img" alt="Hotel Thumbnail Image">
				</div>
				<div class="col-md-8">
					<div class="card-body">
						<h5 class="card-title"><?php echo $ven_name ?></h5>
						<p class="card-text" title="<?php echo $desc ?>"><?php echo $desc_disp ?></p>
						<a href="#" class="btn btn-primary">See Deals</a>
					</div>
					<div class="card-footer text-muted">
						Deals starting at &euro;240
					</div>
				</div>
			</div>
		</div>
	<?php
}

function get_hotel_rows() {
	global $wpdb;

	$sql = "
		SELECT ven.venue_id, ven.`name`, venbook.hotel_booking_id, venbook.`desc`, venbook.Rating,
						venbook.image_src, venbook.thumbnail_src
		FROM {$wpdb->prefix}taste_venue_booking venbook
		LEFT JOIN {$wpdb->prefix}taste_venue ven ON ven.venue_id = venbook.venue_id
	";

	$hotel_rows = $wpdb->get_results($sql, ARRAY_A);
	return $hotel_rows;
}