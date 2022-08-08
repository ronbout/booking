jQuery(document).ready(function () {
  console.log("good");
  jQuery("#run-build-booking-dates").length && tfLoadRunBuildBookingButton();
});

const tfRunBuildBookingDates = (startDate) => {
  jQuery("#results").html("Building bookable dates..");
  jQuery.ajax({
    url: tasteBooking.ajaxurl,
    type: "POST",
    datatype: "html",
    data: {
      action: "build_booking_dates",
      security: tasteBooking.security,
      start_date: startDate,
    },
    success: function (responseText) {
      console.log(responseText);
      //const parseResponse = JSON.parse(responseText);
      jQuery("#results").html(responseText);
    },
    error: function (xhr, status, errorThrown) {
      console.log(errorThrown);
      alert(
        "Error building booking dates table. Your login may have timed out. Please refresh the page and try again."
      );
    },
  });
};

const tfLoadRunBuildBookingButton = () => {
  jQuery("#run-build-booking-dates")
    .off("click")
    .click(function (e) {
      e.preventDefault();
      let startDate = jQuery("#start-date").val();
      tfRunBuildBookingDates(startDate);
    });
};
