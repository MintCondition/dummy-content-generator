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
