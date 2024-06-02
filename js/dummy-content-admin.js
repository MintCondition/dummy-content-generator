jQuery(document).ready(function ($) {
  $("#next-step").on("click", function () {
    var postType = $("#dummy_content_post_type").val();
    if (postType) {
      $.ajax({
        url: dummyContent.ajax_url,
        method: "POST",
        data: {
          action: "get_post_type_fields",
          post_type: postType,
          nonce: dummyContent.nonce,
        },
        success: function (response) {
          if (response.success) {
            var fields = response.data;
            var tbody = $("#dummy-content-fields-table tbody");
            tbody.empty();
            $.each(fields, function (index, field) {
              tbody.append(
                '<tr><td class="column-primary">' +
                  field.label +
                  '</td><td><select name="field_type"><option value="lorem-ipsum">Lorem Ipsum</option></select></td></tr>'
              );
            });
            $("#step-1").hide();
            $("#step-2").show();
          }
        },
      });
    }
  });

  $("#final-step").on("click", function () {
    $("#step-2").hide();
    $("#step-3").show();
  });
});
