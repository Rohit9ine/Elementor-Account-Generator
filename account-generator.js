jQuery(document).ready(function ($) {
  function handleGenerateButtonClick() {
    var $button = $("#generateAccountBtn");
    var $waitMsg = $("#waitMsg");
    var $errorMsg = $("#errorMsg");

    $button.hide();
    $waitMsg.show();
    $errorMsg.hide();

    $.ajax({
      url: account_generator_ajax.ajax_url,
      method: "POST",
      data: {
        action: "generate_account",
      },
      timeout: 15000, // 15 seconds
      success: function (response) {
        if (response.success) {
          location.reload(); // Refresh the page to show the reset link message immediately
        } else {
          $waitMsg.hide();
          $button.show();
          $errorMsg.text("Error: " + response.data).show();
        }
      },
      error: function (xhr, status, error) {
        $waitMsg.hide();
        $button.show();
        $errorMsg.text("Error: Unable to connect to server.").show();
      },
    });
  }

  function handleAccessNowClick() {
    $("#resetLinkMsg").hide(); // Hide the reset message immediately
    $("#passwordLabel").hide(); // Hide the password label
    $("#passwordField").hide(); // Hide the password field
    $("#copyEmailBtn").hide(); // Hide the copy button

    $.ajax({
      url: account_generator_ajax.ajax_url,
      method: "POST",
      data: {
        action: "mark_accessed",
      },
      success: function (response) {
        // Open the link in a new tab
        window.open("", "_blank");
      },
      error: function (xhr, status, error) {
        // Handle the error
      },
    });
  }

  function copyToClipboard(elementId) {
    var copyText = document.getElementById(elementId);
    var tempInput = document.createElement("input");
    tempInput.value = copyText.value;
    document.body.appendChild(tempInput);
    tempInput.select();
    document.execCommand("copy");
    document.body.removeChild(tempInput);
  }

  $(document).on("click", "#generateAccountBtn", handleGenerateButtonClick);
  $(document).on("click", "#accessNowBtn", handleAccessNowClick);
  $(document).on("click", "#emailField", function () {
    copyToClipboard("emailField");
  });
  $(document).on("click", "#passwordField", function () {
    copyToClipboard("passwordField");
  });
});
