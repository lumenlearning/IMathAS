$(document).ready(function () {
  $("#js-dismiss-banner").click(function () {
    let bannerId = $(".ohm-course-banner").attr('data-banner-id');
    dismissNotice(bannerId);
  });
});

function dismissNotice(id) {
  $.ajax({
    method: "POST",
    url: "/ohm/dismiss_notice.php",
    data: {"notice-id": id}
  })
    .done(function (msg) {
      $(".ohm-course-banner").slideUp();
    })
    .fail(function (xhr, status) {
      console.log("Failed to dismiss notice. Error status: " + status);
    });
}
