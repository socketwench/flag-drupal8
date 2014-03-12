<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 11/7/13
 * Time: 11:05 PM
 */

namespace Drupal\flag;

use Drupal\Core\Entity\EntityInterface;
use Drupal\flag\FlagInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

/**
 * Interface ActionLinkTypePluginInterface
 * @package Drupal\flag
 */
interface ActionLinkTypePluginInterface extends PluginFormInterface, ConfigurablePluginInterface {

  public function buildLink($action, FlagInterface $flag, EntityInterface $entity);

  public function renderLink($action, FlagInterface $flag, EntityInterface $entity);

  /**
   * @return string
   *  A string containing the route name.
   */
  public function routeName();

}