<?php
/**
 * @file
 * Provides the FlagPermissions class.
 */

namespace Drupal\flag;

/**
 * Provides dynamic permissions for defined flags.
 *
 * @package Drupal\flag
 */
class FlagPermissions {

  /**
   * Returns an array of dynamic flag permissions.
   *
   * @return array
   *   An array of permissions.
   *
   * @see Drupal\flag\FlagInterface::getPermissions().
   */
  public function permissions() {
    $permissions = [];

    // Get a list of flags from the FlagService.
    $flags = \Drupal::service('flag')->getFlags();

    // Provide flag and unflag permissions for each flag.
    foreach ($flags as $flag_name => $flag) {
      $permissions += $flag->getPermissions();
    }

    return $permissions;
  }
}
