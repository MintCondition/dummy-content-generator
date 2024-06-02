jQuery(document).ready(function ($) {
  $("#next-step").on("click", function () {
    var postType = $("#dummy_content_post_type").val();
    var postCount = $("#dummy_content_post_count").val();
    if (postType && postCount >= 1 && postCount <= 20) {
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
      });
    } else {
      alert(
        "Please select a valid post type and number of posts between 1 and 20."
      );
    }
  });

  $("#dummy-content-fields-table").on(
    "change",
    ".data-type-select",
    function () {
      var dataType = $(this).val();
      var field = $(this).data("field");
      var $generatorWrapper = $(this)
        .closest("td")
        .find(".generator-select-wrapper");
      var $parametersColumn = $(this).closest("tr").find(".parameters-column");

      $generatorWrapper.empty();
      $parametersColumn.empty();

      if (dataType) {
        var generatorOptions =
          '<select class="generator-select" data-field="' +
          field +
          '"><option value="">--Select a Generator--</option>';
        var generators = dummyContent.data_types[dataType].generators;
        $.each(generators, function (index, generator) {
          generatorOptions +=
            '<option value="' + index + '">' + generator.label + "</option>";
        });
        generatorOptions += "</select>";
        $generatorWrapper.html(generatorOptions);
      }
    }
  );

  $("#dummy-content-fields-table").on(
    "change",
    ".generator-select",
    function () {
      var dataType = $(this).closest("td").find(".data-type-select").val();
      var generatorIndex = $(this).val();
      var $parametersColumn = $(this).closest("tr").find(".parameters-column");

      if (dataType && generatorIndex !== "") {
        var parameters =
          dummyContent.data_types[dataType].generators[generatorIndex]
            .parameters;
        $parametersColumn.html(renderParameters(parameters));
      } else {
        $parametersColumn.empty();
      }
    }
  );

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
        if (response.success) {
          var postsPreview = "";
          $.each(response.data, function (index, post) {
            postsPreview +=
              '<div class="dc-post-review"><h2 class="dc-post-review-title">Post ' +
              (index + 1) +
              "</h2>";
            postsPreview += '<table class="dc-post-review-table">';
            $.each(post, function (field, content) {
              postsPreview +=
                "<tr><td>" + field + "</td><td>" + content + "</td></tr>";
            });
            postsPreview += "</table></div>";
          });
          $("#step-2").hide();
          $("#step-3").html(
            postsPreview +
              '<button id="generate-dummy-content" class="button button-primary">Create Dummy Content</button>'
          );
          $("#step-3").show();
        } else {
          alert("Error generating preview.");
        }
      },
    });
  });

  function renderParameters(parameters) {
    var html = "";
    $.each(parameters, function (name, config) {
      var controlType = config.type;
      var label = config.label || "";
      var cssClass = config.class || "";
      var instructions = config.instructions || "";
      var controlHtml = "";

      switch (controlType) {
        case "text":
          controlHtml =
            '<input type="text" name="' +
            name +
            '" class="' +
            cssClass +
            '" placeholder="' +
            name +
            '">';
          break;
        case "textarea":
          controlHtml =
            '<textarea name="' +
            name +
            '" class="' +
            cssClass +
            '" placeholder="' +
            name +
            '"></textarea>';
          break;
        case "select":
          controlHtml = '<select name="' + name + '" class="' + cssClass + '">';
          $.each(config.options, function (index, option) {
            controlHtml +=
              '<option value="' + option + '">' + option + "</option>";
          });
          controlHtml += "</select>";
          break;
        case "number":
          controlHtml =
            '<input type="number" name="' +
            name +
            '" class="' +
            cssClass +
            '" placeholder="' +
            name +
            '">';
          break;
        case "checkbox":
          $.each(config.options, function (index, option) {
            controlHtml +=
              '<label><input type="checkbox" name="' +
              name +
              '[]" value="' +
              option +
              '" class="' +
              cssClass +
              '"> ' +
              escapeHtml(option) +
              "</label>";
          });
          break;
        case "radio":
          $.each(config.options, function (index, option) {
            controlHtml +=
              '<label><input type="radio" name="' +
              name +
              '" value="' +
              option +
              '" class="' +
              cssClass +
              '"> ' +
              escapeHtml(option) +
              "</label>";
          });
          break;
        default:
          controlHtml =
            '<input type="text" name="' +
            name +
            '" class="' +
            cssClass +
            '" placeholder="' +
            name +
            '">';
          break;
      }

      html += '<div class="dc-datatype-param dc-param-' + cssClass + '">';
      html +=
        '<label class="dc-label dc-label-' +
        cssClass +
        '">' +
        label +
        "</label>";
      html += controlHtml;
      html +=
        '<span class="dc-subtext dc-subtext-' +
        cssClass +
        '">' +
        instructions +
        "</span>";
      html += "</div>";
    });
    return html;
  }

  function escapeHtml(text) {
    var map = {
      "&": "&amp;",
      "<": "&lt;",
      ">": "&gt;",
      '"': "&quot;",
      "'": "&#039;",
    };
    return text.replace(/[&<>"']/g, function (m) {
      return map[m];
    });
  }
});
