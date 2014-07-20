<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/10/13
 * Time: 9:44 PM
 */

namespace Drupal\flag;

use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityWithPluginBagsInterface;
use Drupal\Core\Session\AccountInterface;

interface FlagInterface extends ConfigEntityInterface, EntityWithPluginBagsInterface {

  // todo: Add getters and setters as necessary.

  /**
   * Enable the flag.
   */
  public function enable();

  /**
   * Disable the flag.
   */
  public function disable();

  /**
   * Returns whether the Entity is flagged.
   *
   * @param EntityInterface $entity
   *   The entity to be checked.
   * @param AccountInterface $account
   *   (optional) The User.
   * @return bool TRUE id the flag is found FALSE otherwise.
   */
  public function isFlagged(EntityInterface $entity, AccountInterface $account = NULL);

  /**
   * Provides permissions for this flag.
   *
   * @return
   *  An array of permissions for hook_permission().
   */
  public function getPermissions();

  /**
   * Provides Flaggable Entity Type.
   *
   */
  public function getFlaggableEntityType();

  /**
   * Returns whether this flag state should act as a single toggle to all users.
   */
  public function isGlobal();


  /**
   * Sets the flags global state.
   *
   * @param bool $isGlobal
   */
  public function setGlobal($isGlobal);

  /**
   * Set the flag type plugin.
   *
   * @param string $pluginID
   *   A string containing the flag type plugin ID.
   */

  /**
   * @param $pluginID
   */
  public function setFlagTypePlugin($pluginID);

  /**
   * Set the link type plugin.
   *
   * @param string $pluginID
   *   A string containing the link type plugin ID.
   */
  public function setlinkTypePlugin($pluginID);

  /**
   * @return \Drupal\Component\Plugin\PluginBag[]
   */
  public function getPluginBags();

  /**
   * Get the flag type plugin for this flag.
   *
   * @return FlagTypePluginInterface
   */
  public function getFlagTypePlugin();

  /**
   * Get the link type plugin for this flag.
   *
   * @return LinkTypePluginInterface
   */
  public function getLinkTypePlugin();

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