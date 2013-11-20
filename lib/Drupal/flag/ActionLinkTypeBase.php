<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 11/5/13
 * Time: 10:01 PM
 */

namespace Drupal\flag;

use Drupal\core\Plugin\PluginBase;
use Drupal\action_link\ActionLinkTypePluginInterface;

class ActionLinkTypeBase extends PluginBase implements ActionLinkTypePluginInterface {

  /**
   * @param array $configuration
   * @param string $plugin_id
   * @param array $plugin_definition
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->setConfiguration($configuration);
  }

  public function buildLink() {
    return "";
  }

  public function buildConfigurationForm(array $form, array &$form_state) {
    return $form;
  }

  public function submitConfigurationForm(array &$form, array &$form_state) {

  }

  public function validateConfigurationForm(array &$form, array &$form_state) {

  }

  public function defaultConfiguration() {
    return array();
  }

  public function getConfiguration() {
    return $configuration;
  }

  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration;
  }

} 