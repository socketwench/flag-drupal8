<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/5/13
 * Time: 12:56 PM
 */

namespace Drupal\flag;

use Drupal\flag\FlagTypePluginInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\PluginFormInterface;

/**
 * Class FlagTypeBase
 * @package Drupal\flag\Plugin\Flag
 */
abstract class FlagTypeBase extends PluginBase implements FlagTypePluginInterface{

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration += $this->defaultConfiguration();
  }

  /**
   * Provides the default configuration values for the flag type.
   *
   * @return array
   */
  public function defaultConfiguration() {
    return array();
  }

  /**
   * Returns this flag type plugin's configuration array.
   *
   * @return array
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Replaces the plugin's configurations with those given in the parameter.
   *
   * @param array $configuration
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * Provides a form for this action link plugin settings.
   *
   * The form provided by this method is displayed by the FlagAddForm when creating
   * or editing the Flag. Derived classes should want to override this.
   *
   * @param array $form
   * @param array $form_state
   * @return array
   *   The form array
   * @see \Drupal\flag\Form\FlagAddForm
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    return $form;
  }

  /**
   * Handles the form submit for this action link plugin.
   *
   * Derived classes will want to override this.
   *
   * @param array $form
   * @param array $form_state
   */
  public function submitConfigurationForm(array &$form, array &$form_state) {
    // Override this.
  }

  /**
   * Handles the validation for the action link plugin settings form.
   *
   * @param array $form
   * @param array $form_state
   */
  public function validateConfigurationForm(array &$form, array &$form_state) {
    // Override this.
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