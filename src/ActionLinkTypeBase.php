<?php
/**
 * @file
 * Contains the \Drupal\flag\ActionLinkTypeBase class.
 */

namespace Drupal\flag;

use Drupal\Core\Url;
use Drupal\Core\Entity\EntityInterface;
use \Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\flag\FlagInterface;
use Drupal\flag\ActionLinkTypePluginInterface;
use Drupal\flag\FlagService;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a base class for all link types.
 *
 * Link types perform two key functions within Flag: They specify the route to
 * use when a flag link is clicked, and generate the render array to display
 * flag links.
 */
abstract class ActionLinkTypeBase extends PluginBase implements ActionLinkTypePluginInterface {

  /**
   * Build a new link type instance and sets the configuration.
   *
   * @param array $configuration
   *   The configuration array with which to initialize this plugin.
   * @param string $plugin_id
   *   The ID with which to initialize this plugin.
   * @param array $plugin_definition
   *   The plugin definition array.
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configuration += $this->defaultConfiguration();
  }

  /**
   * Returns a route name given an $action.
   *
   * @param string|null $action
   *   A string containing the action name.
   *
   * @return string
   *   A string containing a route name.
   */
  abstract public function routeName($action = NULL);

  /**
   * {@inheritdoc}
   */
  public function buildLink($action, FlagInterface $flag, EntityInterface $entity) {
    $parameters = [
      'flag_id' => $flag->id(),
      'entity_id' => $entity->id(),
    ];

    return new Url($this->routeName($action), $parameters);
  }

  /**
   * Helper method to generate a destination URL parameter.
   *
   * @return string
   *  A string containing a destination URL parameter.
   */
  protected function getDestination() {
    $current_url = Url::fromRoute('<current>');
    $route_params = $current_url->getRouteParameters();

    if (isset($route_params['destination'])) {
      return $route_params['destination'];
    }

    $current_path = ltrim($current_url->toString(), '/');
    return $current_path;
  }

  /**
   * {@inheritdoc}
   */
  public function renderLink($action, FlagInterface $flag, EntityInterface $entity) {
    $url = $this->buildLink($action, $flag, $entity);

    $url->setRouteParameter('destination', $this->getDestination());

    $render = $url->toRenderArray();
    $render['#type'] = 'link';
    $render['#attributes']['id'] = 'flag-' . $flag->id() . '-id-' . $entity->id();

    if ($action === 'unflag') {
      $render['#title'] = $flag->getUnflagShortText();
      $render['#alt'] = $flag->getUnflagLongText();
    }
    else {
      $render['#title'] = $flag->getFlagShortText();
      $render['#alt'] = $flag->getFlagLongText();
    }

    return $render;
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    return [];
  }

  /**
   * Provides a form array for the action link plugin's settings form.
   *
   * Derived classes will want to override this method.
   *
   * @param array $form
   *   The form array.
   * @param FormStateInterface $form_state
   *   The form state.
   *
   * @return array
   *   The modified form array.
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Processes the action link setting form submit.
   *
   * Derived classes will want to override this method.
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
   * Validates the action link setting form.
   *
   * Derived classes will want to override this method.
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
   * Provides the action link plugin's default configuration.
   *
   * Derived classes will want to override this method.
   *
   * @return array
   *   The plugin configuration array.
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * Provides the action link plugin's current configuration array.
   *
   * @return array
   *   An array containing the plugin's current configuration.
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * Updates the plugin's current configuration.
   *
   * @param array $configuration
   *   An array containing the plugin's configuration.
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

}
