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
 * Class FlagTypeBase
 * @package Drupal\flag\Plugin\Flag
 *
 * @Flag{
 *   id = "flagtype_base",
 *   title = @Translation("Flag Type"),
 * }
 */
class FlagTypeBase extends PluginBase{

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }
} 