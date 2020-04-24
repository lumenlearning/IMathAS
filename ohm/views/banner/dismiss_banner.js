window.onload = function () {
  $(".course-banner-close-button").click(function (event) {
    event.preventDefault();
    let bannerType = $(this).attr("aria-controls"); // "course-banner-teacher-{id}" or "course-banner-student-{id}"
    let bannerEl = $('#' + bannerType);
    let bannerId = bannerEl.attr("data-banner-id"); // DB banner ID
    dismissNotice(bannerId, bannerEl);
  });
}

function dismissNotice(id, el) {
  $.ajax({
    method: "POST",
    url: "/ohm/dismiss_banner.php",
    data: { "banner-id": id }
  })
    .done(function (msg) {
      el.slideUp();
    })
    .fail(function (xhr, status) {
      console.log("Failed to dismiss banner. Error status: " + status);
    });
}
