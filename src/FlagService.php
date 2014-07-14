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
use Drupal\flag\Entity\Flag;
use Drupal\flag\Entity\Flagging;
use Drupal\flag\Event\FlagEvents;
use Drupal\flag\Event\FlaggingEvent;
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
    $query = \Drupal::entityQuery('flag');

    if($entity_type != NULL) {
      $query->condition('entity_type', $entity_type);
    }

    if ($bundle != NULL) {
      $query->condition("types.$bundle", $bundle);
    }

    $result = $query->execute();

    $flags = entity_load_multiple('flag', $result);

    if ($account == NULL) {
      return $flags;
    }

    $filtered_flags = array();
    foreach ($flags as $flag) {
      if ($flag->hasActionAccess('flag ' . $flag->id(), $account) || $flag->hasActionAccess('unflag ' . $flag->id(), $account)) {
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

  /**
   * @todo Should not work like this, instead of the ID, the object itself should be passed along!
   */
  public function getFlagById($flag_id) {
    return entity_load('flag', $flag_id);
  }

  /**
   * @todo Should not work like this, instead of the ID, the object itself should be passed along!
   */
  public function getFlaggableById(FlagInterface $flag, $entity_id) {
    return entity_load($flag->getFlaggableEntityType(), $entity_id);
  }

  public function flagByObject(FlagInterface $flag, EntityInterface $entity, AccountInterface $account = NULL) {
    if (empty($account)) {
      $account = \Drupal::currentUser();
    }

    $flagging = entity_create('flagging', array(
      'type' => 'flag',
      'uid' => $account->id(),
      'fid' => $flag->id(),
      'entity_id' => $entity->id(),
      'entity_type' => $entity->getEntityTypeId(),
    ));

    $flagging->save();

    $this->incrementFlagCounts($flag, $entity);

    \Drupal::entityManager()
      ->getViewBuilder($entity->getEntityTypeId())
      ->resetCache(array(
        $entity,
      ));

    \Drupal::service('event_dispatcher')
      ->dispatch(FlagEvents::ENTITY_FLAGGED, new FlaggingEvent($flag, $entity, 'flag'));

    return $flagging;
  }

  /**
   *
   * @api
   *
   * @param $flag_id
   * @param $entity_id
   * @param AccountInterface $account
   * @return EntityInterface
   */
  public function flag($flag_id, $entity_id, AccountInterface $account = NULL) {
    if (empty($account)) {
      $account = \Drupal::currentUser();
    }

    $flag = $this->getFlagById($flag_id);
    $entity = $this->getFlaggableById($flag, $entity_id);

    return $this->flagByObject($flag, $entity, $account);
  }

  /**
   *
   * @api
   *
   * @param $flag_id
   * @param $entity_id
   * @param AccountInterface $account
   * @return array
   */
  public function unflag($flag_id, $entity_id, AccountInterface $account = NULL) {
    if (empty($account)) {
      $account = \Drupal::currentUser();
    }

    $flag = $this->getFlagById($flag_id);
    $entity = $this->getFlaggableById($flag, $entity_id);

    $this->decrementFlagCounts($flag, $entity);

    return $this->unflagByObject($flag, $entity, $account);
  }


  public function unflagByObject(FlagInterface $flag, EntityInterface $entity, AccountInterface $account = NULL) {
    \Drupal::service('event_dispatcher')
      ->dispatch(FlagEvents::ENTITY_UNFLAGGED, new FlaggingEvent($flag, $entity, 'unflag'));

    $out = array();
    $flaggings = $this->getFlaggings($entity, $flag);
    foreach ($flaggings as $flagging) {
      $out[] = $this->unflagByFlagging($flagging);
    }

    return $out;
  }

  public function unflagByFlagging(FlaggingInterface $flagging) {
    $flagging->delete();
  }

  /**
   * Increments count of flagged entities.
   *
   * @param FlagInterface $flag
   * @param EntityInterface $entity
   */
  protected function incrementFlagCounts(FlagInterface $flag, EntityInterface $entity) {
    db_merge('flag_counts')
      ->key(array(
        'fid' => $flag->id(),
        'entity_id' => $entity->id(),
        'entity_type' => $entity->getEntityTypeId(),
      ))
      ->fields(array(
        'last_updated' => REQUEST_TIME,
        'count' => 1,
      ))
      ->expression('count', 'count + :inc', array(':inc' => 1))
      ->execute();
  }

  /**
   * Reverts incrementation of count of flagged entities.
   *
   * @param FlagInterface $flag
   * @param EntityInterface $entity
   */
  protected function decrementFlagCounts(FlagInterface $flag, EntityInterface $entity) {
    $count_result = db_select('flag_counts')
      ->fields(NULL, array('fid', 'entity_id', 'entity_type', 'count'))
      ->condition('fid', $flag->id())
      ->condition('entity_id', $entity->id())
      ->condition('entity_type', $entity->getEntityTypeId())
      ->execute()
      ->fetchAll();
    if (count($count_result) == 1) {
      db_delete('flag_counts')
        ->condition('fid', $flag->id())
        ->condition('entity_id', $entity->id())
        ->condition('entity_type', $entity->getEntityTypeId())
        ->execute();
    }
    else {
      db_update('flag_counts')
        ->expression('count', 'count - 1')
        ->condition('fid', $flag->id())
        ->condition('entity_id', $entity->id())
        ->condition('entity_id', $entity->id())
        ->execute();
    }
  }

}
