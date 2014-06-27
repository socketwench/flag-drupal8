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

  public function enable();

  public function disable();

  public function isFlagged(EntityInterface $entity, AccountInterface $account = NULL);

  public function getPermissions();

  public function isGlobal();

  public function setGlobal($isGlobal);

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