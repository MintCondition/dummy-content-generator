jQuery(document).ready(function ($) {
  $("#final-step").on("click", function () {
    var postType = $("#dummy_content_post_type").val();
    var postCount = $("#dummy_content_post_count").val();
    var fields = [];

    $("#dummy-content-fields-table tbody tr").each(function () {
      var field = $(this).find(".data-type-select").data("field");
      var dataType = $(this).find(".data-type-select").val();
      var generator = $(this).find(".generator-select").val();
      var parameters = {};

      $(this)
        .find(".parameters-column .dc-datatype-param")
        .each(function () {
          var paramName = $(this).find("input, select, textarea").attr("name");
          var paramValue = $(this).find("input, select, textarea").val();
          parameters[paramName] = paramValue;
        });

      fields.push({
        field: field,
        dataType: dataType,
        generator: generator,
        parameters: parameters,
      });
    });

    $("#working-indicator").show(); // Show the indicator before the request

    $.ajax({
      url: dummyContent.ajax_url,
      method: "POST",
      data: {
        action: "preview_dummy_content",
        post_type: postType,
        post_count: postCount,
        fields: fields,
        nonce: dummyContent.nonce,
      },
      success: function (response) {
        $("#working-indicator").hide(); // Hide the indicator after the request completes
        if (response.success) {
          var postsPreview = "";
          $.each(response.data, function (index, post) {
            postsPreview +=
              '<div class="dc-post-review"><h2 class="dc-post-review-title">Post ' +
              (index + 1) +
              "</h2>";
            postsPreview += '<table class="widefat fixed striped">';
            postsPreview +=
              '<thead><tr><th scope="col" style="width: 20%;">Field</th><th scope="col" style="width: 80%;">Generated Content</th></tr></thead>';
            postsPreview += "<tbody>";
            $.each(post, function (field, content) {
              postsPreview +=
                "<tr><td>" + field + "</td><td>" + content + "</td></tr>";
            });
            postsPreview += "</tbody></table></div>";
          });
          $("#step-2").hide();
          $("#step-3").html(
            postsPreview +
              '<button id="generate-dummy-content" class="button button-primary">Create Dummy Content</button>' +
              '<button id="cancel-generation" class="button">Cancel</button>'
          );
          $("#step-3").show();

          // Handle cancel generation
          $("#cancel-generation").on("click", function () {
            // Hide all steps
            $("#step-1, #step-2, #step-3").hide();

            // Reset form fields
            $("#dummy_content_post_type").val("");
            $("#dummy_content_post_count").val(1);
            $("#dummy-content-fields-table tbody").empty();

            // Show the initial step
            $("#step-1").show();
          });
        } else {
          alert("Error generating preview.");
        }
      },
      error: function () {
        $("#working-indicator").hide(); // Hide the indicator on error
        alert("An error occurred while generating the preview.");
      },
    });
  });
});
