var request;

$('#zd-help-form').submit(function(e) {
  e.preventDefault();

  // setup some local variables
  var $form = $(this);

  // Let's select and cache all the fields
  var $inputs = $form.find("input, button, select, textarea");

  // Serialize the data in the form
  var serializedData = $form.serialize();

  // Let's disable the inputs for the duration of the Ajax request.
  // Note: we disable elements AFTER the form data has been serialized.
  // Disabled form elements will not be serialized.
  $inputs.prop("disabled", true);

  request = $.ajax({
    type: 'POST',
    url: 'ohm/create_ticket.php',
    data: serializedData,
    success: function(res) {
      console.log("response:", res);
    },
    error: function(err) {
      console.log('error:', err);
   }
  });

  // Callback handler that will be called on success (uncomment for testing)
  // request.done(function (response, textStatus, jqXHR){
  //     // Log a message to the console
  //     console.log("Hooray, it worked!");
  // });

  // Callback handler that will be called on failure (uncomment for testing)
  // request.fail(function (jqXHR, textStatus, errorThrown){
  //     // Log the error to the console
  //     console.error(
  //         "The following error occurred: " +
  //         textStatus, errorThrown
  //     );
  // });

  // Callback handler that will be called on success or fail
  request.always(function () {
      // Reenable the inputs
      $inputs.prop("disabled", false);
      $inputs.val("");
  });
});
