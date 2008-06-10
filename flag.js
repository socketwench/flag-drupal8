// $Id$
Drupal.behaviors.flag = function() {
  // Note, extra indentation left here to maintain ease of patching for D5 version.

    // Helper function for flipping the flag link contents.
    function flipLink(element, settings) {
      // Get the current status by reading the class names.
      var currentClass = $(element).attr('class');
      if (currentClass.indexOf('unflag ') == -1) {
        // Add the unflag link.
        var newLink = $(settings.unflag);
      }
      else {
        // Add the flag link.
        var newLink = $(settings.flag);
      }

      // Initially hide the message so we can fade it in.
      $('.flag-message', newLink).css('display', 'none');

      // Reattach the behavior to the new link.
      if ($('a', newLink).size() > 0) {
        $('a', newLink).bind('click', function() { return flagClick(this, settings) });
      }
      else {
        $(newLink).bind('click', function() { return flagClick(this, settings) });
      }

      if ($(element).parent('.flag-wrapper').length > 0) {
        $(element).parent().parent().empty().append(newLink);
      }
      else {
        $(element).parent().empty().append(newLink);
      }

      $('.flag-message', newLink).fadeIn();
    }

    // Click function for each Flag link.
    function flagClick(element, settings) {
      // Hide any other active messages.
      $('span.flag-message:visible').fadeOut();
      // Remove the destination parameter.
      if (Drupal.settings.flag.cleanUrl == '1') {
        var requestUrl = element.href.slice(0, element.href.lastIndexOf('?')) + '/1';
      }
      else {
        var requestUrl = element.href.slice(0, element.href.indexOf('&')) + '/1';
      }

      // Send POST request
      $.ajax({
        type: 'POST',
        url: requestUrl,
        data: { js: true },
        dataType: 'json',
        success: function (data) {
          // Display errors
          if (!data.status) {
            // Change link back
            flipLink(element, settings);
            return;
          }
        },
        error: function (xmlhttp) {
          alert('An HTTP error '+ xmlhttp.status +' occured.\n'+ element.href);
          // Change link back
          flipLink(element, settings);
        }
      });
      // Swap out the links.
      flipLink(element, settings);
      return false;
    }

    // On load, bind the click behavior for all links on the page.
    for (i in Drupal.settings.flag.flags) {
      // This bind method is a little silly. We should just be able to pass
      // in the settings as additional data to the click method, but this
      // doesn't work in jQuery 1.0.4.
      $('a.'+ 'flag-' + i).bind('click', function() {
        var matches = this.className.match(/flag-([a-z0-9_]+)/);
        var name = matches[1];
        var matches = this.href.match(/node\/([a-z0-9_]+)/);
        var nid = 'node_' + matches[1];

        return flagClick(this, Drupal.settings.flag.flags[name][nid]);
      });
    }
  // Intentional extra indention.
}