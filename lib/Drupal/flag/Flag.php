<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/28/13
 * Time: 9:15 PM
 */

namespace Drupal\flag;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Flag service.
 */
class Flag { //@todo Rename to FlagService?

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
  }

  public function flag($action, $flag_name, $entity_id, $account = NULL, $permissions_check = FALSE) {

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
  public function fetchDefinition($entity_type = NULL) {
    if(!empty($entity_type)){
      return \Drupal::service('plugin.manager.flag.flagtype')->getDefinition($entity_type);
    }

    return \Drupal::service('plugin.manager.flag.flagtype')->getDefinitions();
  }

  /**
   * List all flags available.
   *
   * If node type or account are entered, a list of all possible flags will be
   * returned.
   *
   * @param $entity_type
   *   (optional) The type of entity for which to load the flags. Usually 'node'.
   * @param $bundle
   *   (optional) The bundle for which to load the flags.
   * @param $account
   *   (optional) The user account to filter available flags. If not set, all
   *   flags for the given entity and bundle will be returned.
   *
   * @return
   *   An array of the structure [fid] = flag_object.
   */
  public function getFlags($entity_type = NULL, $bundle = NULL, AccountInterface $account = NULL) {
    $query = \Drupal::entityQuery('flag_flag');

    if($entity_type != NULL) {
      $query->condition('entity_type', $entity_type);
    }

    if ($bundle != NULL) {
      $query->condition("types.$bundle", $bundle);
    }

    $result = $query->execute();

    $flags = entity_load_multiple('flag_flag', $result);

    if ($account == NULL) {
      return $flags;
    }

    $filtered_flags = array();
    foreach ($flags as $flag) {
      if ($flag->canFlag($account) || $flag->canUnflag($account)) {
        $filtered_flags[] = $flag;
      }
    }

    return $filtered_flags;
  }

} 