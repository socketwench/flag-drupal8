<?php
/**
 * @file
 * Contains the FlagInterface.
 */

namespace Drupal\flag;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityWithPluginBagsInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Defines the interface for Flag entities.
 *
 * @package Drupal\flag
 */
interface FlagInterface extends ConfigEntityInterface, EntityWithPluginBagsInterface {

  //@todo: Add getters and setters as necessary.

  /**
   * Enables the Flag for use.
   */
  public function enable();

  /**
   * Disables the Flag for use.
   */
  public function disable();

  /**
   * Returns true of there's a flagging for this flag and the given entity.
   *
   * @param EntityInterface $entity
   * @param AccountInterface $account
   * @return boolean
   */
  public function isFlagged(EntityInterface $entity, AccountInterface $account = NULL);

  /**
   * Returns an associative array of permissions used by flag_permission().
   *
   * Typically there are two permissions, one to flag, and one to unflag.
   * Each key of the array is the permission name. Each value is an array with
   * a single element, 'title', which provides the display name for the
   * permission.
   *
   * @return array
   *
   * @see \Drupal\flag\Entity\Flag::getPermissions()
   */
  public function getPermissions();

  /**
   * Returns true if the flag is global, false otherwise.
   *
   * Global flags disable the default behavior of a Flag. Instead of each
   * user being able to flag or unflag the entity, a global flag may be flagged
   * once for all users.
   *
   * @return boolean
   */
  public function isGlobal();

  /**
   * Sets the flag as global or not.
   *
   * @param $isGlobal
   *  TRUE to mark the flag as global, FALSE for the default behavior.
   * @see \Drupal\flag\Entity\Flag::isGlobal()
   */
  public function setGlobal($isGlobal);

  /**
   * {@inheritdoc}
   */
  public function getPluginBags();

  /**
   * User access permission for flagging actions.
   *
   * Checks whether a user has permission to flag/unflag or not.
   *
   * @param string $action
   *   An indicator flag.
   * @param AccountInterface $account
   *   (optional) An AccountInterface object.
   *
   * @return bool|null
   *   Returns a bool defining the users access permission for flagging action.
   */
  public function hasActionAccess($action, AccountInterface $account = NULL);

}