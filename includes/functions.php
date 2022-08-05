<?php
/**
 * 	Common functions for thetaste-booking plugin
 * 	
 * 	7/31/2022	Ron Boutilier
 * 
 */

defined('ABSPATH') or die('Direct script access disallowed.');

function book_days($cal, $days_array) {
	$color_array = array();
	$text_array = array();
	foreach($days_array as $day) {
		$color_array[$day] = array('black', 'orange');
		$text_array[$day] = "Booked";
	}
	$cal->set_day_colors($color_array);
	$cal->set_day_text($text_array);
}

function avail_days($cal, $days_array) {
	$color_array = array();
	$text_array = array();
	foreach($days_array as $day) {
		$color_array[$day] = array('lightpink', 'blue');
		$text_array[$day] = "Avail";
	}
	$cal->set_day_colors($color_array);
	$cal->set_day_text($text_array);
}