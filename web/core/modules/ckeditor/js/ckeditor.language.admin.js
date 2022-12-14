/**
* DO NOT EDIT THIS FILE.
* See the following change record for more information,
* https://www.drupal.org/node/2815083
* @preserve
**/
(function ($, Drupal) {
  Drupal.behaviors.ckeditorLanguageSettingsSummary = {
    attach: function attach() {
      $('#edit-editor-settings-plugins-language').drupalSetSummary(function (context) {
        var $selected = $('#edit-editor-settings-plugins-language-language-list-type option:selected');
        if ($selected.length) {
          return $selected[0].textContent;
        }
        return '';
      });
    }
  };
})(jQuery, Drupal);