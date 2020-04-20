$(document).ready(function () {
  $('#start-immediately').change(function () {
    let checked = $(this).is(":checked");
    toggleTimestampFields('#sdate', '#stime', checked);
  });

  $('#never-ending').change(function () {
    let checked = $(this).is(":checked");
    toggleTimestampFields('#edate', '#etime', checked);
  });

  $('#js-cancel-button').click(function (e) {
    e.preventDefault();
    window.location.href = "?";
  });
});

function toggleTimestampFields(dateId, timeId, checked) {
  if (checked) {
    $(dateId).attr('disabled', true).attr('required', false);
    $(timeId).attr('disabled', true).attr('required', false);
  } else {
    $(dateId).attr('disabled', false).attr('required', true);
    $(timeId).attr('disabled', false).attr('required', true);
    let timeStr = $(timeId).val();
    if ('' === timeStr.trim()) {
      $(timeId).val('23:59:59');
    }
  }
}
