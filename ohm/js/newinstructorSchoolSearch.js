$(document).ready(function () {
  $.typeahead({
    input: '.school-name',
    dynamic: true,
    minLength: 2,
    maxItem: 0,
    order: "asc",
    cancelButton: false,
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
        console.log(JSON.stringify(item));
      },
    },
  });
});
