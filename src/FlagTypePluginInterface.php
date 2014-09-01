<?php
/**
 * @file
 * Contains the FlagTypePluginInterface.
 */

namespace Drupal\flag;

use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

/**
 * Provides an interface for all flag type plugins.
 *
 * @package Drupal\flag
 */
interface FlagTypePluginInterface extends PluginFormInterface, ConfigurablePluginInterface {

  /**
   * Implements access_multiple() implemented by each child class.
   *
   * @abstract
   *
   * @param array $entity_ids
   *   An array of entity IDs.
   * @param \Drupal\Core\Session\AccountInterface $account
   *   An account to test for access.
   *
   * @return array
   *   An array keyed by entity ids, whose values represent the access to the
   *   corresponding entity. The access value may be FALSE if access should be
   *   denied, or NULL (or not set) if there is no restriction to  be made. It
   *   should NOT be TRUE.
   */
  public function typeAccessMultiple($entity_ids, $account);
}
