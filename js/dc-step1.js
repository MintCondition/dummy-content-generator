jQuery(document).ready(function ($) {
  $("#next-step").on("click", function () {
    var postType = $("#dummy_content_post_type").val();
    var postCount = $("#dummy_content_post_count").val();
    if (postType && postCount >= 1 && postCount <= 20) {
      $("#working-indicator").show(); // Show the indicator
      $.ajax({
        url: dummyContent.ajax_url,
        method: "POST",
        data: {
          action: "get_post_type_fields",
          post_type: postType,
          nonce: dummyContent.nonce,
        },
        success: function (response) {
          $("#working-indicator").hide(); // Hide the indicator
          if (response.success) {
            var fields = response.data;
            var tbody = $("#dummy-content-fields-table tbody");
            tbody.empty();
            $.each(fields, function (index, field) {
              var dataTypeOptions =
                '<select class="data-type-select" data-field="' +
                field.name +
                '"><option value="">--Select Data Type--</option>';
              $.each(dummyContent.data_types, function (type, dataType) {
                dataTypeOptions +=
                  '<option value="' +
                  type +
                  '">' +
                  dataType.label +
                  "</option>";
              });
              dataTypeOptions += "</select>";
              tbody.append(
                '<tr><td class="column-primary">' +
                  field.label +
                  "</td><td>" +
                  dataTypeOptions +
                  '<div class="generator-select-wrapper"></div></td><td class="parameters-column"></td></tr>'
              );
            });
            $("#step-1").hide();
            $("#step-2").show();
          }
        },
        error: function () {
          $("#working-indicator").hide(); // Hide the indicator on error
          alert("An error occurred while fetching the fields.");
        },
      });
    } else {
      alert(
        "Please select a valid post type and number of posts between 1 and 20."
      );
    }
  });
});
