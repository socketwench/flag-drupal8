<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 9/30/13
 * Time: 8:59 PM
 */
namespace Drupal\flag\Export;
/**
 * Flag update class for API 2 flags -> API 3.
 */
class FlagUpdate_3 {

  public $old_api_version = 2;
  public $new_api_version = 3;

  static function update(&$flag) {
    // Change the content_type property to entity_type.
    if (isset($flag->content_type)) {
      $flag->entity_type = $flag->content_type;
      unset($flag->content_type);
    }

    // We can't convert the flag roles data to user permissions at this point
    // because the flag is disabled and hence hook_permission() doesn't see it
    // to define its permissions.
    // Instead, we copy it to import_roles, which the flag add form will handle
    // on new flags (which this flag will behave as when it is re-enabled).
    // @see flag_form()
    if (isset($flag->roles)) {
      $flag->import_roles = $flag->roles;
    }

    // Update show_on_teaser property to use new view mode settings.
    if (!empty($flag->show_on_teaser)) {
      $flag->show_in_links['teaser'] = TRUE;
      unset($flag->show_on_teaser);
    }

    // Update show_on_page property to use new view mode settings.
    if (!empty($flag->show_on_page)) {
      $flag->show_in_links['full'] = TRUE;
      unset($flag->show_on_page);
    }

    // Update show_on_comment and show_on_entity properties to use new view
    // mode settings. Since the old logic was to show on all view modes, do that.
    if (!empty($flag->show_on_entity) || !empty($flag->show_on_comment)) {
      if ($entity_info = entity_get_info($flag->entity_type)) {
        foreach ($entity_info['view modes'] as $view_mode => $value) {
          $flag->show_in_links[$view_mode] = TRUE;
        }
      }

      unset($flag->show_on_entity, $flag->show_on_comment);
    }
  }
}