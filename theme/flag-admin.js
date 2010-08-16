// $Id$
(function ($) {

/**
 * Behavior to disable the "unflag" option if "flag" is not available.
 */
Drupal.behaviors.flagRoles = {};
Drupal.behaviors.flagRoles.attach = function(context) {
  $('#flag-roles input.flag-access', context).change(function() {
    var unflagCheckbox = $(this).parents('tr:first').find('input.unflag-access').get(0);
    if (this.checked) {
      // If "flag" is available, restore the state of the "unflag" checkbox.
      unflagCheckbox.disabled = false;
      if (typeof(unflagCheckbox.previousFlagState) != 'undefined') {
        unflagCheckbox.checked = unflagCheckbox.previousFlagState;
      }
      else {
        unflagCheckbox.checked = true;
      }
    }
    else {
      // Remember if the "unflag" option was checked or unchecked, then disable.
      unflagCheckbox.previousFlagState = unflagCheckbox.checked;
      unflagCheckbox.disabled = true;
      unflagCheckbox.checked = false;
    }
  });

  $('#flag-roles input.unflag-access', context).change(function() {
    if ($(this).parents('table:first').find('input.unflag-access:enabled:not(:checked)').size() == 0) {
      $('div.form-item-unflag-denied-text').slideUp();
    }
    else {
      $('div.form-item-unflag-denied-text').slideDown();
    }
  });

  // Hide the link options by default if needed.
  if ($('#flag-roles input.unflag-access:enabled:not(:checked)').size() == 0) {
    $('div.form-item-unflag-denied-text').css('display', 'none');
  }
};


/**
 * Behavior to make link options dependent on the link radio button.
 */
Drupal.behaviors.flagLinkOptions = {};
Drupal.behaviors.flagLinkOptions.attach = function(context) {
  $('.flag-link-options input.form-radio', context).change(function() {
    // Reveal only the fieldset whose ID is link-options-LINKTYPE,
    // where LINKTYPE is the value of the selected radio button.
    var radioButton = this;
    var $relevant   = $('fieldset#link-options-' + radioButton.value);
    var $irrelevant = $('fieldset[id^=link-options-]').not($relevant);

    $relevant.show();
    $irrelevant.hide();

    if ($relevant.size()) {
      $('#link-options-intro').show();
    }
    else {
      $('#link-options-intro').hide();
    }
  })
  // Hide the link options by default if needed.
  .filter(':checked').trigger('change');
};

/**
 * Vertical tabs integration.
 */
Drupal.behaviors.flagSummary = {};

Drupal.behaviors.flagSummary.attach = function (context) {
  $('fieldset.flag-fieldset', context).setSummary(function(context) {
    var flags = [];

    $('input.form-checkbox', context).each(function() {
      if (this.checked) {
        flags.push(this.name.replace(/flag\[([a-z0-9]+)\]/, '$1'));
      }
    });

    if (flags.length) {
      return flags.join(', ');
    }
    else {
      return Drupal.t('No flags');
    }
  });
};

})(jQuery);
