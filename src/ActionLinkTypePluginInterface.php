<?php
/**
 * @file
 * Contains the \Drupal\flag\ActionLinkTypePluginInterface.
 */

namespace Drupal\flag;

use Drupal\Core\Entity\EntityInterface;
use Drupal\flag\FlagInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Component\Plugin\ConfigurablePluginInterface;

/**
 * Provides an interface for link type plugins.
 */
interface ActionLinkTypePluginInterface extends PluginFormInterface, ConfigurablePluginInterface {

  /**
   * Returns a Url object for the given flag action.
   *
   * @param string $action
   *   The action, flag or unflag.
   * @param \Drupal\flag\FlagInterface $flag
   *   The flag entity
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   *
   * @return \Drupal\Core\Url
   *   The URL object.
   */
  public function buildLink($action, FlagInterface $flag, EntityInterface $entity);

  /**
   * Generates a flag link as a render array.
   *
   * @param string $action
   *   The action to perform, 'flag' and 'unflag'.
   * @param FlagInterface $flag
   *   The flag entity.
   * @param EntityInterface $entity
   *   The entity for which to create a flag link.
   *
   * @return array
   *   A render array of the flag link.
   */
  public function renderLink($action, FlagInterface $flag, EntityInterface $entity);
}
