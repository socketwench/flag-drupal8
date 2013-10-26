<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/6/13
 * Time: 10:40 AM
 */

namespace Drupal\flag\Annotation;

use Drupal\Component\Annotation\Plugin;


/**
 * Defines a Flag annotation object.
 *
 * @Annotation
 */
class Flag extends Plugin {

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
   * A class to make the plugin derivative aware.
   *
   * @var string
   *
   * @see \Drupal\Component\Plugin\Discovery\DerivativeDiscoveryDecorator
   */
  public $derivative;

}
