<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/28/13
 * Time: 9:15 PM
 */

namespace Drupal\flag;

/**
 * Static service container wrapper for Flag.
 */
class Flag {

  public static function flag($action, $flag_name, $entity_id, $account = NULL, $permissions_check = FALSE) {

  }

  /**
   * Get a flag type definition.
   *
   * @param $entity_type
   *   (optional) The entity type to get the definition for, or NULL to return
   *   all flag types.
   *
   * @return
   *   The flag type definition array.
   *
   * @see hook_flag_type_info()
   */
  public static function flag_fetch_definition($entity_type = NULL) {
    if(!empty($entity_type)){
      return \Drupal::service('plugin.manager.flag.flagtype')->getDefinition($entity_type);
    }

    return \Drupal::service('plugin.manager.flag.flagtype')->getDefinitions();
  }

} 