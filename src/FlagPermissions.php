<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 9/23/14
 * Time: 5:02 PM
 */

namespace Drupal\flag;


class FlagPermissions {
  public function permissions() {
    $permissions = [];

    $flags = \Drupal::service('flag')->getFlags();
    // Provide flag and unflag permissions for each flag.
    foreach ($flags as $flag_name => $flag) {
      $permissions += $flag->getPermissions();
    }

    return $permissions;
  }
}
