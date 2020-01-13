
var ohmModal = (function() {
    var method = {},
        $overlay,
        $modal,
        $content,
        $close;
  
    // Append the html
    $overlay = $('<div class="ohm-modal-overlay"></div>');
    $modal = $('<div class="ohm-modal"></div>');
    $content = $('<div class="ohm-modal-content"></div>');
    // $close = $('<button class="ohm-modal-close">Close</button>');
  
    $modal.hide();
    $overlay.hide();
    $modal.append($content);
  
    $(document).ready(function() {
      $('body').append($overlay, $modal);
    });
  
    // Center the modal in the viewport
    method.center = function() {
      var top, left;
  
      top = Math.max($(window).height() - $modal.outerHeight(), 0) / 2;
      left = Math.max($(window).width() - $modal.outerWidth(), 0) / 2;
  
      $modal.css({
        top:top + $(window).scrollTop(),
        left:left + $(window).scrollLeft()
      });
    };
  
    // Open the modal
    method.open = function(settings) {
      $content.empty().append(settings.content);
      
      $modal.css({
        width: settings.width || 'auto',
        height: settings.height || 'auto'
      });
  
      method.center();
  
      $(window).bind('resize.modal', method.center);
  
      $modal.fadeIn(200);
      $overlay.fadeIn(200);
    };
  
    // Close the modal
    method.close = function() {
      $modal.fadeOut(300);
      $overlay.fadeOut(300, function() {
        $content.empty();
        $(window).unbind('resize.modal');
      });
    };
  
    // Click 'x' to close
    // $close.click(function(e) {
    //   e.preventDefault();
    //   method.close();
    // });
  
    // Press 'esc' to close
    $(document).keyup(function(e) {
      if (e.keyCode == 27) {
        e.preventDefault();
        method.close();
      }
    });
  
    return method;
  }());
