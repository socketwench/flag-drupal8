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
class FlagTypeBase extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public funciton flag() {

  }

  /**
   * Declares the options this flag supports, and their default values.
   *
   * Derived classes should want to override this.
   *//*
  public function options() {

  }*/

  /**
   * Provides a form for setting options.
   *
   * Derived classes should want to override this.
   */
  public function optionsForm(&$form) {

  }

  /**
   * Implements access_multiple() implemented by each child class.
   *
   * @abstract
   *
   * @return
   *  An array keyed by entity ids, whose values represent the access to the
   *  corresponding entity. The access value may be FALSE if access should be
   *  denied, or NULL (or not set) if there is no restriction to  be made. It
   *  should NOT be TRUE.
   */
  public function type_access_multiple($entity_ids, $account) {
    return array();
  }
} 