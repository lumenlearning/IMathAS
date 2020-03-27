window.onload = function () {
  $("#js-dismiss-banner").click(function (event) {
    event.preventDefault();
    let bannerType = $(this).attr("aria-controls"); // "course-banner-teacher" or "course-banner-student"
    let bannerEl = $('#' + bannerType);
    let bannerId = bannerEl.attr("data-banner-id"); // DB banner ID
    dismissNotice(bannerId, bannerEl);
  });
}

function dismissNotice(id, el) {
  $.ajax({
    method: "POST",
    url: "/ohm/dismiss_notice.php",
    data: { "notice-id": id }
  })
    .done(function (msg) {
      el.slideUp();
    })
    .fail(function (xhr, status) {
      console.log("Failed to dismiss notice. Error status: " + status);
    });
}
