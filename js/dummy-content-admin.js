jQuery(document).ready(function ($) {
  console.log("Dummy Content Admin Script Loaded");

  // Event listener for data type selection change
  $(".data-type-select").on("change", function () {
    var dataType = $(this).val();
    var generatorSelect = $(this).closest("tr").find(".generator-select");
    var parametersCell = $(this).closest("tr").find(".parameters-cell");

    console.log("Data Type Selected:", dataType);

    if (dataType) {
      // Enable the generator select
      generatorSelect.prop("disabled", false);

      // Populate the generator select with options
      generatorSelect
        .empty()
        .append('<option value="">Select Generator</option>');
      var generators = dummyContent.data_types[dataType]["generators"];
      console.log("Available Generators:", generators);

      for (var i = 0; i < generators.length; i++) {
        generatorSelect.append(
          '<option value="' +
            generators[i]["class"] +
            '">' +
            generators[i]["label"] +
            "</option>"
        );
      }

      // Clear the parameters cell
      parametersCell.empty();
    } else {
      // Disable the generator select if no data type is selected
      generatorSelect.prop("disabled", true);
      generatorSelect
        .empty()
        .append('<option value="">Select Generator</option>');

      // Clear the parameters cell
      parametersCell.empty();
    }
  });

  // Event listener for generator selection change
  $(".generator-select").on("change", function () {
    var generatorClass = $(this).val();
    var dataType = $(this).closest("tr").find(".data-type-select").val();
    var parametersCell = $(this).closest("tr").find(".parameters-cell");

    console.log("Generator Selected:", generatorClass);

    if (generatorClass) {
      // Populate the parameters cell with fields
      parametersCell.empty();
      var generators = dummyContent.data_types[dataType]["generators"];
      var selectedGenerator = generators.find(
        (generator) => generator.class === generatorClass
      );
      console.log("Selected Generator:", selectedGenerator);

      var parameters = selectedGenerator.parameters;
      for (var parameterKey in parameters) {
        var parameter = parameters[parameterKey];
        var inputField = "";
        switch (parameter.type) {
          case "select":
            inputField =
              '<select name="parameters[' +
              $(this).closest("tr").find("td:first").text().trim() +
              "][" +
              parameterKey +
              ']">';
            parameter.options.forEach(function (option) {
              inputField +=
                '<option value="' + option + '">' + option + "</option>";
            });
            inputField += "</select>";
            break;
          case "text":
            inputField =
              '<input type="text" name="parameters[' +
              $(this).closest("tr").find("td:first").text().trim() +
              "][" +
              parameterKey +
              ']" value="' +
              (parameter.default || "") +
              '">';
            break;
          case "number":
            inputField =
              '<input type="number" name="parameters[' +
              $(this).closest("tr").find("td:first").text().trim() +
              "][" +
              parameterKey +
              ']" value="' +
              (parameter.default || "") +
              '">';
            break;
        }
        parametersCell.append(
          "<label>" + parameter.label + ": " + inputField + "</label><br>"
        );
      }
    } else {
      // Clear the parameters cell if no generator is selected
      parametersCell.empty();
    }
  });
});
