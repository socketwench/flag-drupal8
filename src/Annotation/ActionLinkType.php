<?php
/**
 * @file
 * Contains the \Drupal\flag\Annotation\ActionLinkType annotation plugin.
 */

namespace Drupal\flag\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines an ActionLink annotation object.
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
