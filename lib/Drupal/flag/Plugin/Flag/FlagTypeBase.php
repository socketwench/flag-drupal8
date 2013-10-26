<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/5/13
 * Time: 12:56 PM
 */

namespace Drupal\flag\Plugin\Flag;

use Drupal\core\Plugin\PluginBase;

/**
 * Class FlagBase
 * @package Drupal\flag\Plugin\Flag
 *
 * @Flag{
 *   id = "flag_base",
 *   title = @Translation("Flag"),
 *   derivative = "Drupal\flag\Plugin\Derivative\FlagBase"
 * }
 */
class FlagBase extends PluginBase{

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }
} 