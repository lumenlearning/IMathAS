$( document ).ready(function() {
  var showClass = 'show';
  var placeholder = "";
  $("input").focus(function(){
    placeholder = $(this).attr('placeholder');
    var label = $(this).prev('label');
    label.addClass(showClass);
    $(this).removeAttr('placeholder');
  })
  $("input").blur(function(){
    var label = $(this).prev('label');
    if(this.value != ''){
      label.addClass(showClass);
      $(this).removeAttr('placeholder');
    }
    else {
      label.removeClass(showClass);
      $(this).attr('placeholder',placeholder);
    }
  })
});
