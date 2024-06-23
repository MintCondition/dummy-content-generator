// TODO: move to dummy-content-admin.js

jQuery(document).ready(function ($) {
  $("#wp-admin-bar-dcg_clear_debug_log a").on("click", function (e) {
    e.preventDefault();

    if (confirm("Are you sure you want to clear the debug log?")) {
      $.post(
        dcgAjax.ajax_url,
        {
          action: "dcg_clear_debug_log",
          _ajax_nonce: dcgAjax.nonce,
        },
        function (response) {
          if (response.success) {
            alert(response.data);
          } else {
            alert(response.data);
          }
        }
      );
    }
  });
});

jQuery(document).ready(function ($) {
  let updateCheckInProgress = false;

  $("#dcg_check_for_updates").on("click", function () {
    if (updateCheckInProgress) return;

    updateCheckInProgress = true;
    console.log("Button Clicked in dcg-admin.js");

    $.post(
      dcgAjax.ajax_url,
      {
        action: "dcg_check_for_updates",
        _ajax_nonce: dcgAjax.nonce,
      },
      function (response) {
        console.log("AJAX Response received");
        console.log(response);
        updateCheckInProgress = false;

        if (response.success) {
          $("#dcg_update_feedback").text(response.data.message);
          console.log("Update check completed successfully");
          location.reload();
        } else {
          $("#dcg_update_feedback").text(dcgAjax.update_failed_message);
          console.log("Update check failed");
        }
      }
    ).fail(function (jqXHR, textStatus, errorThrown) {
      console.log("AJAX request failed");
      console.log("Status: " + textStatus);
      console.log("Error: " + errorThrown);
      updateCheckInProgress = false;
    });
  });
});
