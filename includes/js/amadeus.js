(function ($) {
  $(document).ready(function () {
    tBookingLoadAPITypeSelection();
    tBookingLoadApiFormSubmit();
  });

  const tBookingLoadAPITypeSelection = () => {
    $("#amadeus-api-settings-form input[name='api-type']").change(function (e) {
      const ApiType = $(this).filter(":checked").val();
      $(".api-inputs-div").hide();
      $(`#${ApiType}-api-inputs`).show();
    });
  };

  const tBookingLoadApiFormSubmit = () => {
    const $form = $("#amadeus-api-settings-form");
    $form.submit(function (e) {
      e.preventDefault();
      const prodFlag = $("#amadeus-api-settings-form input[name='prod-flag']")
        .filter(":checked")
        .val();
      const apiType = $("#amadeus-api-settings-form input[name='api-type']")
        .filter(":checked")
        .val();

      let apiSettings = {};
      switch (apiType) {
        case "city":
          apiSettings.cityCode = $("#city-api-city-code").val();
          if (!apiSettings.cityCode) {
            alert("City Code is required");
            return;
          }
          break;
        case "rating":
          apiSettings.hotelIds = $("#rating-api-hotel-ids").val();
          if (!apiSettings.hotelIds) {
            alert("Hotel Id is required");
            return;
          }
          break;
        case "offers":
          apiSettings.hotelIds = $("#offers-api-hotel-ids").val();
          if (!apiSettings.hotelIds) {
            alert("Hotel Id is required");
            return;
          }
          apiSettings.offerDate = $("#offers-api-date").val();
          apiSettings.adults = $("#offers-api-adults").val();
          apiSettings.nights = $("#offers-api-nights").val();
          apiSettings.rawFlag = $(
            "#amadeus-api-settings-form input[name='raw-data-flag']"
          )
            .filter(":checked")
            .val();
      }
      let formData = {
        prodFlag,
        apiType,
        apiSettings,
      };
      tBookingRunApi(formData);
    });
  };

  const tBookingRunApi = (apiFormData) => {
    $("#results").html("Calling Amadeus API..");
    $.ajax({
      url: tasteBooking.ajaxurl,
      type: "POST",
      datatype: "html",
      data: {
        action: "run_amadeus_api",
        security: tasteBooking.security,
        api_form_data: apiFormData,
      },
      success: function (responseText) {
        console.log(responseText);
        //const parseResponse = JSON.parse(responseText);
        $("#results").html(responseText);
      },
      error: function (xhr, status, errorThrown) {
        console.log(errorThrown);
        alert(
          "Error accessing Amadeus API's. Your login may have timed out. Please refresh the page and try again."
        );
      },
    });
  };
})(jQuery);
