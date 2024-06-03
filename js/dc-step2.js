jQuery(document).ready(function ($) {
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
