<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 11/7/13
 * Time: 11:05 PM
 */

namespace Drupal\flag;

use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

/**
 * Interface ActionLinkTypePluginInterface
 * @package Drupal\flag
 */
interface ActionLinkTypePluginInterface extends PluginFormInterface, ConfigurablePluginInterface {

  public function buildLink();

}