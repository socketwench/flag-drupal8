<?php
/**
 * @file
 * Contains the FlagTypePluginInterface.
 */

namespace Drupal\flag;

use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

/**
 * Interface FlagTypePluginInterface
 * @package Drupal\flag
 */
interface FlagTypePluginInterface extends PluginFormInterface, ConfigurablePluginInterface {
  public function type_access_multiple($entity_ids, $account);
} 