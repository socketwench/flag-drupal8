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
 * @FlagType(
 *   id = "flagtype_base",
 *   title = @Translation("Flag Type Base")
 * )
 */
class FlagTypeBase extends PluginBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    $configuration += $this->options();
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  public funciton flag() {

  }

  /**
   * Declares the options this flag supports, and their default values.
   *
   * Derived classes should want to override this.
   *
   * @todo Rename to defaultConfiguration()?
   */
  public function options() {
    return array();
  }

  /**
   * Provides a form for setting options.
   *
   * Derived classes should want to override this.
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    return $form;
  }

/**
 * Handles the form submit for this plugin.
 *
 * @param array $form
 * @param array $form_state
 */
public function submitConfigurationForm(array &$form, array &$form_state) {
    // Override this
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