<?php
/**
 * @file
 * Contains \Drupal\flag\Plugin\Flag\EntityFlagType.
 */

namespace Drupal\flag\Plugin\Flag;

use Drupal\flag\FlagTypeBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class EntityFlagType
 * @package Drupal\flag\Plugin\Flag
 *
 * Base entity flag handler.
 *
 * @FlagType(
 *   id = "flagtype_entity",
 *   title = @Translation("Flag Type Entity"),
 *   derivative = "Drupal\flag\Plugin\Derivative\EntityFlagType"
 * )
 */
class EntityFlagType extends FlagTypeBase {

  /**
   * The entity type defined in plugin definition.
   *
   * @var string
   */
  public $entity_type = '';

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, array $plugin_definition) {
    $this->entity_type = $plugin_definition['entity_type'];
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $options = parent::defaultConfiguration();
    $options += array(
      // Output the flag in the entity links.
      // This is empty for now and will get overriden for different
      // entities.
      // @see hook_entity_view().
      'show_in_links' => array(),
      // Output the flag as individual pseudofields.
      'show_as_field' => FALSE,
      // Add a checkbox for the flag in the entity form.
      // @see hook_field_attach_form().
      'show_on_form' => FALSE,
      'show_contextual_link' => FALSE,
    );
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    /* Options form extras for the generic entity flag. */

    // Add checkboxes to show flag link on each entity view mode.
    $options = array();
    $defaults = array();
    $view_modes = \Drupal::entityManager()->getViewModes($this->entity_type);
    foreach ($view_modes as $name => $view_mode) {
      $options[$name] = t('Display on @name view mode', array('@name' => $view_mode['label']));
      $defaults[$name] = !empty($this->show_in_links[$name]) ? $name : 0;
    }

    $form['display']['show_in_links'] = array(
      '#type' => 'checkboxes',
      '#title' => t('Display in entity links'),
      '#description' => t('Show the flag link with the other links on the entity.'),
      '#options' => $options,
      '#default_value' => $defaults,
    );

    $form['display']['show_as_field'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display link as field'),
      '#description' => t('Show the flag link as a pseudofield, which can be ordered among other entity elements in the "Manage display" settings for the entity type.'),
      '#default_value' => isset($this->show_as_field) ? $this->show_as_field : TRUE,
    );
    /*
    if (empty($entity_info['fieldable'])) {
      $form['display']['show_as_field']['#disabled'] = TRUE;
      $form['display']['show_as_field']['#description'] = t("This entity type is not fieldable.");
    }
    */
    $form['display']['show_on_form'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display checkbox on entity edit form'),
      '#default_value' => $this->showOnForm(),
      '#weight' => 5,
    );

    // We use FieldAPI to put the flag checkbox on the entity form, so therefore
    // require the entity to be fielable. Since this is a potential DX
    // headscratcher for a developer wondering where this option has gone,
    // we disable it and explain why.
    /*
    if (empty($entity_info['fieldable'])) {
      $form['display']['show_on_form']['#disabled'] = TRUE;
      $form['display']['show_on_form']['#description'] = t('This is only possible on entities which are fieldable.');
    }
    */
    $form['display']['show_contextual_link'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display in contextual links'),
      '#default_value' => $this->showContextualLink(),
      '#description' => t('Note that not all entity types support contextual links.'),
      '#access' => \Drupal::moduleHandler()->moduleExists('contextual'),
      '#weight' => 10,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['show_in_links'] = $form_state['values']['show_in_links'];
    $this->configuration['show_as_field'] = $form_state['values']['show_as_field'];
    $this->configuration['show_on_form'] = $form_state['values']['show_on_form'];
    $this->configuration['show_contextual_link'] = $form_state['values']['show_contextual_link'];
  }

  public function showInLinks() {
    return $this->configuration['show_in_links'];
  }

  public function showAsField() {
    return $this->configuration['show_as_field'];
  }

  public function showOnForm() {
    return $this->configuration['show_on_form'];
  }

  public function showContextualLink() {
    return $this->configuration['show_contextual_link'];
  }
} 