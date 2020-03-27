window.onload = function () {
  $("#js-dismiss-banner").click(function (event) {
    event.preventDefault();
    let bannerEl = $(this).attr("aria-controls");
    let bannerId = $(banner).attr("data-banner-id");
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
      $(el).slideUp();
    })
    .fail(function (xhr, status) {
      console.log("Failed to dismiss notice. Error status: " + status);
    });
}
