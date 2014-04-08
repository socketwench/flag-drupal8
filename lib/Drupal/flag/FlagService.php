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
use Drupal\flag\FlagInterface;
use Drupal\Core\Entity\EntityInterface;


/**
 * Flag service.
 */
class FlagService {

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(ModuleHandlerInterface $module_handler) {
    $this->moduleHandler = $module_handler;
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

  public function getFlaggings(EntityInterface $entity, FlagInterface $flag, AccountInterface $account = NULL) {
    if($account == NULL) {
      $account = \Drupal::currentUser();
    }

    $result = \Drupal::entityQuery('flagging')
      ->condition('uid', $account->id())
      ->condition('fid', $flag->id())
      ->condition('entity_type', $entity->getEntityTypeId())
      ->condition('entity_id', $entity->id())
      ->execute();

    $flaggings = array();
    foreach ($result as $flagging_id) {
      $flaggings[$flagging_id] = entity_load('flagging', $flagging_id);
    }

    return $flaggings;
  }

  public function getFlagById($flag_id) {
    return entity_load('flag_flag', $flag_id);
  }

  public function getFlaggableById(FlagInterface $flag, $entity_id) {
    return entity_load($flag->getFlaggableEntityType(), $entity_id);
  }

  public function flagByObject(FlagInterface $flag, EntityInterface $entity, AccountInterface $account = NULL) {
    if (empty($account)) {
      $account = \Drupal::currentUser();
    }

    $flagging = entity_create('flagging', array(
      'type' => 'flag_flag',
      'uid' => $account->id(),
      'fid' => $flag->id(),
      'entity_id' => $entity->id(),
      'entity_type' => $entity->getEntityTypeId(),
    ));

    $flagging->save();

    \Drupal::entityManager()
      ->getViewBuilder($entity->getEntityTypeId())
      ->resetCache(array(
        $entity,
      ));

    return $flagging;
  }

  public function flag($flag_id, $entity_id, AccountInterface $account = NULL) {
    if (empty($account)) {
      $account = \Drupal::currentUser();
    }

    $flag = $this->getFlagById($flag_id);
    $entity = $this->getFlaggableById($flag, $entity_id);

    return $this->flagByObject($flag, $entity, $account);
  }

  public function unflagByObject(FlaggingInterface $flagging) {
    $flagging->delete();
  }

  public function unflag($flag_id, $entity_id, AccountInterface $account = NULL) {
    if (empty($account)) {
      $account = \Drupal::currentUser();
    }

    $flag = $this->getFlagById($flag_id);
    $entity = $this->getFlaggableById($flag, $entity_id);

    $out = array();
    $flaggings = $this->getFlaggings($entity, $flag);
    foreach ($flaggings as $flagging) {
      $out[] = $this->unflagByObject($flagging);
    }

    return $out;
  }

} 