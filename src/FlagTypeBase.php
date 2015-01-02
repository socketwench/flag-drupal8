<?php
/**
 * @file
 * Contains the \Drupal\flag\FlagTypeBase class.
 */

namespace Drupal\flag;

use Drupal\flag\FlagTypePluginInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a base class for flag type plugins.
 */
abstract class FlagTypeBase extends PluginBase implements FlagTypePluginInterface {

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
   *   The flag type's default plugin configuration.
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * Returns this flag type plugin's configuration array.
   *
   * @return array
   *   The plugin configuration array.
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Replaces the plugin's configurations with those given in the parameter.
   *
   * @param array $configuration
   *   The plugin configuration array.
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

  /**
   * Provides a form for this action link plugin settings.
   *
   * The form provided by this method is displayed by the FlagAddForm when
   * creating or editing the Flag. Derived classes should override this.
   *
   * @param array $form
   *   The form array.
   * @param FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The form array
   * @see \Drupal\flag\Form\FlagAddForm
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Handles the form submit for this action link plugin.
   *
   * Derived classes will want to override this.
   *
   * @param array $form
   *   The form array.
   * @param FormStateInterface $form_state
   *   The form state.
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Override this.
  }

  /**
   * Handles the validation for the action link plugin settings form.
   *
   * @param array $form
   *   The form array.
   * @param FormStateInterface $form_state
   *   The form state.
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Override this.
  }

  /**
   * {@inheritdoc}
   */
  public function typeAccessMultiple(array $entity_ids, AccountInterface $account) {
    return [];
  }
}
