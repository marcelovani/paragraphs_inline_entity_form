/**
 * @file
 * Provides additional javascript for managing the paragraph wysiwyg dialog.
 */

(function ($) {
  'use strict';

  let saveCallback = undefined;

  $('.paragraphs-wysiwyg-add-type .form-submit').click(function () {
    this.form.elements.namedItem('selected_bundle').value = this.attributes['data-paragraph-bundle'].value;
  });

  // Store the save callback function for use when the dialog is closed. This allows the dialog to trigger the save callback when closed.
  // This also solves the issue where the embedded paragraph is not updated when the dialog is closed.
  $(window).on('dialog:beforeclose', (e, dialog, element) => {
    if (element.hasOwnProperty(0) && element[0].getAttribute('id').match(/paragraph-\d+-edit-dialog/)) {
      saveCallback = Drupal.ckeditor5.saveCallback;
    }
  });

  $(window).on('editor:dialogsave', function (e, values) {
    if (saveCallback !== undefined && typeof saveCallback === 'function') {
      saveCallback(values);
      saveCallback = undefined;
    }
  });

})(jQuery);
