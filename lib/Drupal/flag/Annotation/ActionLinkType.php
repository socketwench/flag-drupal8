<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 11/4/13
 * Time: 9:45 PM
 */

namespace Drupal\flag\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Class ActionLinkType
 * @package Drupal\action_link\Annotation
 *
 * Defines an ActionLink annotation object.
 *
 * @Annotation
 *
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
   * The plugin description
   *
   * @var string
   */
  public $description;

} 