<?php
/**
 * @file
 * Contains the \Drupal\flag\FlagTypePluginInterface.
 */

namespace Drupal\flag;

use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides an interface for all flag type plugins.
 */
interface FlagTypePluginInterface extends PluginFormInterface, ConfigurablePluginInterface {

  /**
   * Implements access_multiple() implemented by each child class.
   *
   * @abstract
   *
   * @param array $entity_ids
   *   An array of entity IDs.
   * @param AccountInterface $account
   *   An account to test for access.
   *
   * @return array
   *   An array keyed by entity ids, whose values represent the access to the
   *   corresponding entity. The access value may be FALSE if access should be
   *   denied, or NULL (or not set) if there is no restriction to  be made. It
   *   should NOT be TRUE.
   */
  public function typeAccessMultiple(array $entity_ids, AccountInterface $account);
}
