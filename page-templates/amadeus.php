<?php
/*
Template Name: Test Amadeus API's
*/

/**
 *  Date:  8/10/2022
 * 	Author: Ron Boutilier
 */
defined('ABSPATH') or die('Direct script access disallowed.');



?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	
	<link rel="stylesheet" href="<?php echo TBOOKING_PLUGIN_URL ?>/assets/css/font-awesome.min.css">
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
	<script src="<?php echo TBOOKING_PLUGIN_INCLUDES_URL . "/js/amadeus.js"?>"></script>

	<?php
	echo "
			<script>
				let tasteBooking = {}
				tasteBooking.ajaxurl = '". admin_url( 'admin-ajax.php' ) . "'
				tasteBooking.security = '" . wp_create_nonce('taste-booking-nonce') . "'
			</script>
		";
		?>

	<title>Amadeus API Data</title>
</head>
<body>
	<main class="container-fluid my-3 p-4 text-center">
		<h1>Run Amadeus API's</h1>
		<div class="row" style="height: 700px;">
		<section class="col-sm-4" style="border:1px solid black">
			<?php display_api_form() ?>
		</section>
		<section class="col-sm-8 p-3 text-left" id="results" style="border:1px solid black; width: 50%;"> </section>
		</div>
	</main>
</body>
</html>

<?php

function display_api_form() {
	?>
	<form id="amadeus-api-settings-form" class="px-2 py-4">
		<div class="form-group">
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="radio" name="prod-flag" id="prod-flag-test" value="test" checked>
				<label class="form-check-label" for="prod-flag-test">Test Data</label>
			</div>
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="radio" name="prod-flag" id="prod-flag-prod" value="prod">
				<label class="form-check-label" for="prod-flag-prod">Production Data</label>
			</div>
		</div>
		<hr>
		<div class="form-group">
			<h5>API Type:</h5>
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="radio" name="api-type" id="api-type-city" value="city" checked>
				<label class="form-check-label" for="api-type-city">Hotels by City</label>
			</div>
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="radio" name="api-type" id="api-type-rating" value="rating" >
				<label class="form-check-label" for="api-type-rating">Rating by Hotel(s)</label>
			</div>
			<div class="form-check form-check-inline">
				<input class="form-check-input" type="radio" name="api-type" id="api-type-offers" value="offers" >
				<label class="form-check-label" for="api-type-offers">Offers by Hotel(s)</label>
			</div>
		</div>
		<hr>
		<?php
		// the following will display based on the api selection type
		?>
		<div id="city-api-inputs" class="api-inputs-div">
			<div class="form-group row">
				<label for="city-api-city-code" class="col-sm-4 col-form-label">City Code: *</label>
				<div class="col-sm-4">
					<input type="text" class="form-control" name="city-api-city-code" id="city-api-city-code" value="DUB">
				</div>
				<div class="col-sm-4"></div>
			</div>
		</div>

		<div id="rating-api-inputs" class="api-inputs-div" style="display:none;">
			<div class="form-group row">
				<label for="rating-api-hotel-ids" class="col-sm-4 col-form-label">Hotel ID(s): *<br>(comma-separated)</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="rating-api-hotel-ids" id="rating-api-hotel-ids" placeholder="ex. LWDUB556,LWDUB430">
				</div>
			</div>
		</div>

		<div id="offers-api-inputs" class="api-inputs-div" style="display:none;">
			<div class="form-group row">
				<label for="offers-api-hotel-ids" class="col-sm-4 col-form-label">Hotel ID(s): *<br>(comma-separated)</label>
				<div class="col-sm-8">
					<input type="text" class="form-control" name="offers-api-hotel-ids" id="offers-api-hotel-ids" placeholder="ex. LWDUB556,LWDUB430">
				</div>
			</div>
			<div class="form-group row">
				<label for="offers-api-date" class="col-sm-4 col-form-label">Date:</label>
				<div class="col-sm-4">
					<input type="date" class="form-control" name="offers-api-date" id="offers-api-date" value="<?php echo date("Y-m-d")?>">
				</div>
				<div class="col-sm-4"></div>
			</div>
			<div class="form-group row">
				<label for="offers-api-adults" class="col-sm-4 col-form-label">Adults</label>
				<div class="col-sm-4">
					<select class="form-control" id="offers-api-adults">
						<option value="1">1</option>
						<option value="2" selected>2</option>
						<option value="3">3</option>
					</select>
				</div>
				<div class="col-sm-4"></div>
			</div>
			<div class="form-group row">
				<label for="offers-api-nights" class="col-sm-4 col-form-label">Nights</label>
				<div class="col-sm-4">
					<input type="number" min="1" max="7" step="1" class="form-control" name="offers-api-nights" id="offers-api-nights" value="1">
				</div>
				<div class="col-sm-4"></div>
			</div>

			<div class="form-group row">
				<div class="col-sm-3">Data Display</div>
				<div class="col-sm-9">
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="raw-data-flag" id="raw-data-flag-table" value="table" checked>
						<label class="form-check-label" for="prod-flag-test">Tabular Data</label>
					</div>
					<div class="form-check form-check-inline">
						<input class="form-check-input" type="radio" name="raw-data-flag" id="raw-data-flag-raw" value="raw">
						<label class="form-check-label" for="raw-data-flag-raw">Raw Data</label>
					</div>
				</div>
			</div>
		</div>
		<div class="form-group">
			<button class="btn btn-primary" type="submit">Run API</button>
		</div>
	</form>
	<?php
}