<?php
/**
 * @file
 * Contains the FlagService class.
 */

namespace Drupal\flag;

use Drupal\Core\Session\AccountInterface;
use Drupal\flag\Entity\Flag;
use Drupal\flag\Entity\Flagging;
use Drupal\flag\Event\FlagEvents;
use Drupal\flag\Event\FlaggingEvent;
use Drupal\flag\FlagInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\flag\FlagTypePluginManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityManagerInterface;

/**
 * Flag service.
 */
class FlagService {

  /**
   * @var FlagTypePluginManager
   */
  private $flagTypeMgr;

  /**
   * @var EventDispatcherInterface
   */
  private $eventDispatcher;

  /*
   * @var QueryFactory
   */
  private $entityQueryMgr;

  /**
   * @var AccountInterface
   */
  private $currentUser;

  /*
   * @var EntityManagerInterface
   * */
  private $entityMgr;

  /**
   * Constructor.
   *
   * @param FlagTypePluginManager $flagType
   *   The flag type plugin manager.
   * @param EventDispatcherInterface $eventDispatcher
   *   The event dispatcher service.
   * @param QueryFactory $entityQuery
   *   The entity query factory.
   * @param AccountInterface $currentUser
   *   The current user.
   * @param EntityManagerInterface $entityManager
   *   The entity manager.
   */
  public function __construct(FlagTypePluginManager $flagType,
                              EventDispatcherInterface $eventDispatcher,
                              QueryFactory $entityQuery,
                              AccountInterface $currentUser,
                              EntityManagerInterface $entityManager) {
    $this->flagTypeMgr = $flagType;
    $this->eventDispatcher = $eventDispatcher;
    $this->entityQueryMgr = $entityQuery;
    $this->currentUser = $currentUser;
    $this->entityMgr = $entityManager;
  }

  /**
   * Get a flag type definition.
   *
   * @param string $entity_type
   *   (optional) The entity type to get the definition for, or NULL to return
   *   all flag types.
   *
   * @return array
   *   The flag type definition array.
   *
   * @see hook_flag_type_info()
   */
  public function fetchDefinition($entity_type = NULL) {
    //@todo Add caching, PLS!

    if (!empty($entity_type)) {
      return $this->flagTypeMgr->getDefinition($entity_type);
    }

    return $this->flagTypeMgr->getDefinitions();
  }

  /**
   * List all flags available.
   *
   * If node type or account are entered, a list of all possible flags will be
   * returned.
   *
   * @param string $entity_type
   *   (optional) The type of entity for which to load the flags. Usually 'node'.
   * @param string $bundle
   *   (optional) The bundle for which to load the flags.
   * @param AccountInterface $account
   *   (optional) The user account to filter available flags. If not set, all
   *   flags for the given entity and bundle will be returned.
   *
   * @return array
   *   An array of the structure [fid] = flag_object.
   */
  public function getFlags($entity_type = NULL, $bundle = NULL, AccountInterface $account = NULL) {
    $query = $this->entityQueryMgr->get('flag');

    if ($entity_type != NULL) {
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

  /**
   * Get all flaggings for the given entity, flag, and optionally, user.
   *
   * @param EntityInterface $entity
   *   The flaggable entity.
   * @param FlagInterface $flag
   *   The flag entity.
   * @param AccountInterface $account
   *   Optional. The account of the flagging user. If NULL, flaggings for any
   *   user will be returned.
   * @return array
   *   An array of flaggings.
   */
  public function getFlaggings(EntityInterface $entity, FlagInterface $flag, AccountInterface $account = NULL) {
    if ($account == NULL) {
      $account = $this->currentUser;
    }

    $result = $this->entityQueryMgr->get('flagging')
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
   * Load the flag entity given the ID.
   *
   * @param int $flag_id
   *   The ID of the flag to load.
   * @return FlagInterface|null
   *   The flag entity.
   */
  public function getFlagById($flag_id) {
    return entity_load('flag', $flag_id);
  }

  /**
   * Loads the flaggable entity given the flag entity and entity ID.
   *
   * @param FlagInterface $flag
   *   The flag entity.
   * @param int $entity_id
   *   The ID of the flaggable entity.
   * @return EntityInterface|null
   *   The flaggable entity object.
   */
  public function getFlaggableById(FlagInterface $flag, $entity_id) {
    return entity_load($flag->getFlaggableEntityType(), $entity_id);
  }

  /**
   * Flags the given entity given the flag and entity objects.
   *
   * @param FlagInterface $flag
   *   The flag entity.
   * @param EntityInterface $entity
   *   The entity to flag.
   * @param AccountInterface $account
   *   Optional. The account of the user flagging the entity. If not given,
   *   the current user is used.
   * @return FlaggingInterface|null
   *   The flagging.
   */
  public function flagByObject(FlagInterface $flag, EntityInterface $entity, AccountInterface $account = NULL) {
    if (empty($account)) {
      $account = $this->currentUser;
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

    $this->entityMgr
      ->getViewBuilder($entity->getEntityTypeId())
      ->resetCache(array(
        $entity,
      ));

    $this->eventDispatcher->dispatch(FlagEvents::ENTITY_FLAGGED, new FlaggingEvent($flag, $entity, 'flag'));

    return $flagging;
  }

  /**
   * Flags an entity given the flag ID and entity ID.
   *
   * @api
   *
   * @param int $flag_id
   *   The ID of the flag.
   * @param int $entity_id
   *   The ID of the entity to flag.
   * @param AccountInterface $account
   *   Optional. The account of user flagging the entity. If not given, the
   *   current user is used.
   * @return FlaggingInterface|null
   *   The flagging entity.
   */
  public function flag($flag_id, $entity_id, AccountInterface $account = NULL) {
    if (empty($account)) {
      $account = $this->currentUser;
    }

    $flag = $this->getFlagById($flag_id);
    $entity = $this->getFlaggableById($flag, $entity_id);

    return $this->flagByObject($flag, $entity, $account);
  }

  /**
   * Unflags an entity given the flag ID and entity ID.
   *
   * @api
   *
   * @param int $flag_id
   *   The ID of the flag.
   * @param int $entity_id
   *   The ID of the flagged entity to unflag.
   * @param AccountInterface $account
   *   Optional. The account of the user that created the flagging.
   * @return array
   *   An array of flagging IDs to delete.
   */
  public function unflag($flag_id, $entity_id, AccountInterface $account = NULL) {
    if (empty($account)) {
      $account = $this->currentUser;
    }

    $flag = $this->getFlagById($flag_id);
    $entity = $this->getFlaggableById($flag, $entity_id);

    $this->decrementFlagCounts($flag, $entity);

    return $this->unflagByObject($flag, $entity, $account);
  }

  /**
   * Unflags the given entity for the given flag.
   *
   * @param FlagInterface $flag
   *   The flag being unflagged.
   * @param EntityInterface $entity
   *   The entity to unflag.
   * @param AccountInterface $account
   *   Optional. The account of the user that created the flagging.
   * @return array
   *   An array of flagging IDs to delete.
   */
  public function unflagByObject(FlagInterface $flag, EntityInterface $entity, AccountInterface $account = NULL) {
    $this->eventDispatcher->dispatch(FlagEvents::ENTITY_UNFLAGGED, new FlaggingEvent($flag, $entity, 'unflag'));

    $out = array();
    $flaggings = $this->getFlaggings($entity, $flag, $account);
    foreach ($flaggings as $flagging) {
      $out[] = $flagging->id();

      $this->unflagByFlagging($flagging);
    }

    return $out;
  }

  /**
   * Deletes the given flagging.
   *
   * @param FlaggingInterface $flagging
   *   The flagging to delete.
   */
  public function unflagByFlagging(FlaggingInterface $flagging) {
    $flagging->delete();
  }

  /**
   * Increments count of flagged entities.
   *
   * @param FlagInterface $flag
   *    The flag to increment.
   * @param EntityInterface $entity
   *    The flaggable entity.
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
   *    The flag to decrement.
   * @param EntityInterface $entity
   *   The flaggable entity.
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
