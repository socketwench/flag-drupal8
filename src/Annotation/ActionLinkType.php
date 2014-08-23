<?php
/**
 * @file
 * Contains the ActionLinkType annotation plugin.
 */

namespace Drupal\flag\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an ActionLink annotation object.
 *
 * @package Drupal\action_link\Annotation
 *
 * @Annotation
 */
class ActionLinkType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * The plugin description.
   *
   * @var string
   */
  public $description;

}
