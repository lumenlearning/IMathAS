$(document).ready(function () {
  var ipedsIdInput = $('.ipeds-id');
  var ipedsNameInput = $('.ipeds-name');

  // https://github.com/running-coder/jquery-typeahead
  // http://www.runningcoder.org/jquerytypeahead/documentation/
  $.typeahead({
    input: '.school-name',
    dynamic: true,
    minLength: 2,
    maxItem: 0,
    order: "asc",
    searchOnFocus: true,
    cancelButton: false,
    filter: false,
    cache: false,
    source: {
      school: {
        display: "name",
        ajax: {
          type: "POST",
          url: "/admin/ipedssearch.php",
          dataType: 'json',
          data: {
            search: "{{query}}",
            type: 'coll',
          },
        },
      },
    },
    callback: {
      onClick: function (node, a, item, event) {
        ipedsIdInput.val(item.id);
        ipedsNameInput.val(item.name);
      },
      onCancel: function () {
        ipedsIdInput.val('');
        ipedsNameInput.val('');
      }
    },
  });
});
