
var ohmModal = (function() {
    var method = {},
        $overlay,
        $modal,
        $content;
  
    // Append the html
    $overlay = $('<div class="ohm-modal-overlay"></div>');
    $modal = $('<div class="js-ohm-modal ohm-modal"></div>');
    $content = $('<div class="ohm-modal-content" role="dialog" aria-labelledby="dialog-title" aria-describedby="dialog-description"></div>');

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
        top: top + $(window).scrollTop(),
        left: left + $(window).scrollLeft()
      });
    };
  
    // Open the modal
    method.open = function(settings) {
      $content.empty().append(settings.content);
      
      method.forceDialogFocus(settings.focusEl); 

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

    method.forceDialogFocus = function(focusEl){
      // setTimeout to prevent JS from trying to set focus on element before fade in animation has completed
      setTimeout(function(event){
        $content.attr('tabindex', '-1');
        $(focusEl).focus();
      }, 0);
    }

    $content.on('keydown', function (event) {
      var cancel = false;
      if (event.ctrlKey || event.metaKey || event.altKey) {
        return;
      }

      console.log(event.key)
      switch(event.key) {
        case "Escape": 
          console.log("ESCAPE")
          // $modal.hide();
          // lastfocus.focus();
          // cancel = true;
          break;
        case "Tab": // TAB
          // if (e.shiftKey) {
          //   if (e.target === links[0]) {
          //     links[links.length - 1].focus();
          //     cancel = true;
          //   }
          // } else {
          //   if (e.target === links[links.length - 1]) {
          //     links[0].focus();
          //     cancel = true;
          //   }
          // }
          break;
      }
      if (cancel) {
        e.preventDefault();
      }
    });
  
    // Press 'esc' to close
    $(document).keyup(function(event) {
      if (event.key === "Escape") {
        event.preventDefault();
        method.close();
      }
    });
  
    return method;
  }());
