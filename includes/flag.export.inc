<?php

/**
 * @file
 * Import/Export functionality provided by Flag module.
 */

/**
 * Export a flag to code.
 *
 * @param $flags
 *   An array of flag objects, or flag name.
 * @param $module
 *   Optional. The name of the module that will be created if exporting to use
 *   in hook_flag_default_flags().
 */
function flag_export_flags($flags = array(), $module = '', $indent = '') {
  module_load_include('inc', 'features', 'features.export'); // For features_var_export() (optional).
  $output = $indent . '$flags = array();' . "\n";
  foreach ($flags as $item) {
    if (is_object($item)) {
      $flag = $item;
    }
    else {
      // We got just the flag name, for example from the features
      // implementation.
      if (!($flag = flag_load($item, TRUE))) {
        continue;
      }
    }
    if (!$flag->is_compatible()) {
      drupal_set_message(t('Could not export flag %flag-name: Your flag was created by a different version of the Flag module than is now being used.', array('%flag-name' => $flag->name)), 'error');
      continue;
    }

    $flag->api_version = FLAG_API_VERSION;
    $new_flag = (array) $flag;

    if (!empty($module)) {
      // Even though Flag adds the module name itself later, we add the module
      // name here for reference by other modules (such as Features).
      $new_flag['module'] = $module;
      // Lock the flag name, as is normally desired by modules using
      // hook_flag_default_flags(), and needed by Features.
      $new_flag['locked'] = array('name');
    }
    // Allow other modules to change the exported flag.
    drupal_alter('flag_export', $new_flag);

    // Remove properties we don't export.
    $unset_properties = array(
      // Remove the flag ID.
      'fid',
      // The name is emitted as the key for the array.
      'name',
      // The entity info is just used as helper data.
      'entity_info',
      // Remove roles.
      'roles',
      // Remove errors.
      'errors',
    );
    foreach ($unset_properties as $property) {
      unset($new_flag[$property]);
    }

    $output .= $indent . '// Exported flag: "' . check_plain($flag->get_title()) . '"' . ".\n";
    $output .= $indent . '$flags[\'' . $flag->name . '\'] = ' . (function_exists('features_var_export') ? features_var_export($new_flag, $indent) : var_export($new_flag, TRUE)) . ";\n";
  }
  $output .= $indent . 'return $flags;' . "\n";
  return $output;
}

/**
 * Page for displaying an upgrade message and export form for Flag 1.x flags.
 */
function flag_update_page($flag) {
  if ($flag->is_compatible()) {
    drupal_set_message(t('The flag %name is already up-to-date with the latest Flag API and does not need upgrading.', array('%name' => $flag->name)));
    drupal_goto(FLAG_ADMIN_PATH);
  }

  drupal_set_message(t('The flag %name is currently using the Flag API version @version, which is not compatible with the current version of Flag. You can upgrade this flag by pasting the below code into <em>@module_flag_default_flags()</em> function in the @module.module file.', array('%name' => $flag->name, '@version' => $flag->api_version, '@module' => $flag->module)), 'warning');

  flag_update_export($flag);

  return drupal_get_form('flag_export_form', $flag);
}

/**
 * Update a flag before export.
 *
 * @param $flag
 *   The flag object passed by reference.
 */
function flag_update_export(&$flag) {
  // Set the API version to 1 by default: version 1 did not explicitly define
  // the API version.
  if (empty($flag->api_version)) {
    $flag->api_version = 1;
  }

  // Get all our update classes.
  // This is not terribly graceful, but the alternative is declaring our classes
  // explicitly, or registering them with the Drupal autoloader and then running
  // a database query, which seems a waste of space given we only ever need
  // these here.
  $classes = get_declared_classes();
  $update_handlers = array();
  foreach ($classes as $class) {
    // Any class whose name is of the form 'FlagUpdate_foo' is one of ours, we
    // assume. Should this prove problematic, we can add use of reflection here.
    if (substr($class, 0, 11) == 'FlagUpdate_') {
      // @todo: change this to work with the static class when we drop support
      // for PHP 5.2: see commit d5b517.
      $update_handler = new $class;
      // Cast to string, as decimals as array keys seem to be rounded down to
      // ints, WTF PHP?
      $version = (string) $update_handler->old_api_version;

      $update_handlers[$version] = $update_handler;
    }
  }
  // Sort the classes by old version number.
  uksort($update_handlers, 'version_compare');

  // Work through each update handler.
  foreach ($update_handlers as $old_api_version => $update_handler) {
    // Skip update classes that are older than our current flag.
    if (version_compare($old_api_version, $flag->api_version, '<')) {
      continue;
    }

    // Run the update and change the API version on the flag.
    $update_handler->update($flag);
    $flag->api_version = $update_handler->new_api_version;
  }
}
