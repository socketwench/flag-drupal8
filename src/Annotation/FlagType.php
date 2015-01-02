<?php
/**
 * @file
 * Contains the \Drupal\flag\Annotation\FlagType annotation plugin.
 */

namespace Drupal\flag\Annotation;

use Drupal\Component\Annotation\Plugin;


/**
 * Defines a FlagType annotation object.
 *
 * @Annotation
 */
class FlagType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The title of the plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $title;

  /**
   * The entity type the flag type supports.
   *
   * @var string
   */
  public $entity_type;

}
