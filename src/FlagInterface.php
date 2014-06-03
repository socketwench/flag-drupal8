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
use Drupal\Core\Session\AccountInterface;

interface FlagInterface extends ConfigEntityInterface {

  // todo: Add getters and setters as necessary.

  public function enable();

  public function disable();

  public function isFlagged(EntityInterface $entity, AccountInterface $account = NULL);

  public function getPermissions();

  public function isGlobal();

  public function setGlobal($isGlobal);
}