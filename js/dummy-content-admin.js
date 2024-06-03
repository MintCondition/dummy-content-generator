jQuery(document).ready(function ($) {
  // Define the base URL for the scripts
  var baseScriptUrl = dummyContent.base_url;

  // Load step1.js
  $.getScript(baseScriptUrl + "dc-step1.js").fail(function (
    jqxhr,
    settings,
    exception
  ) {
    console.error("Error loading Step 1 script.");
    console.error("jqXHR object:", jqxhr);
    console.error("Settings object:", settings);
    console.error("Exception object:", exception);
  });

  // Load step2.js
  $.getScript(baseScriptUrl + "dc-step2.js").fail(function (
    jqxhr,
    settings,
    exception
  ) {
    console.error("Error loading Step 2 script.");
    console.error("jqXHR object:", jqxhr);
    console.error("Settings object:", settings);
    console.error("Exception object:", exception);
  });

  // Load step3.js
  $.getScript(baseScriptUrl + "dc-step3.js").fail(function (
    jqxhr,
    settings,
    exception
  ) {
    console.error("Error loading Step 3 script.");
    console.error("jqXHR object:", jqxhr);
    console.error("Settings object:", settings);
    console.error("Exception object:", exception);
  });
});
