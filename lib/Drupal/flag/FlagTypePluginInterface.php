<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 12/15/13
 * Time: 3:20 PM
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