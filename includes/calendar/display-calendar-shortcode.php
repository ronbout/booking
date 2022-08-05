<?php


function display_calendar_shortcode() {

	$cal = new BasicCalendar(array());
	$cal->set_default_day_colors("black", "yellow");

	book_days($cal, array(14,15));
	avail_days($cal, array(1, 8, 31));

	$cal->display();

}

