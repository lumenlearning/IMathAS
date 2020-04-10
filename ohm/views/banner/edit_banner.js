$(document).ready(function () {
  $('#has-start-at').change(function () {
    let checked = $(this).is(":checked");
    toggleTimestampFields('#sdate', '#stime', checked);
  });

  $('#has-end-at').change(function () {
    let checked = $(this).is(":checked");
    toggleTimestampFields('#edate', '#etime', checked);
  });
});

function toggleTimestampFields(dateId, timeId, checked) {
  if (checked) {
    $(dateId).attr('disabled', false).attr('required', true);
    $(timeId).attr('disabled', false).attr('required', true);
    let timeStr = $(timeId).val();
    if ('' === timeStr.trim()) {
      $(timeId).val('23:59:59');
    }
  } else {
    $(dateId).attr('disabled', true).attr('required', false);
    $(timeId).attr('disabled', true).attr('required', false);
  }
}
