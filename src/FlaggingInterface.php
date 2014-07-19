<?php
/**
 * @file
 * Contains the FlaggingInterface.
 */

namespace Drupal\flag;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * The interface for flagging entities.
 *
 * @package Drupal\flag
 */
interface FlaggingInterface extends ContentEntityInterface {

  /**
   * Returns the Flag content entity related to this flagging.
   *
   * @return \Drupal\flag\FlagInterface
   */
  public function getFlag();

  /**
   * Returns the flaggable entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity object.
   */
  public function getFlaggable();

}
