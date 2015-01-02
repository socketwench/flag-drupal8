<?php
/**
 * @file
 * Contains \Drupal\flag\Plugin\Flag\EntityFlagType.
 */

namespace Drupal\flag\Plugin\Flag;

use Drupal\flag\FlagTypeBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a flag type for all entity types.
 *
 * Base entity flag handler.
 *
 * @FlagType(
 *   id = "flagtype_entity",
 *   title = @Translation("Flag Type Entity"),
 *   deriver = "Drupal\flag\Plugin\Derivative\EntityFlagType"
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
    $options += [
      // Output the flag in the entity links.
      // This is empty for now and will get overriden for different
      // entities.
      // @see hook_entity_view().
      'show_in_links' => [],
      // Output the flag as individual pseudofields.
      'show_as_field' => TRUE,
      // Add a checkbox for the flag in the entity form.
      // @see hook_field_attach_form().
      'show_on_form' => FALSE,
      'show_contextual_link' => FALSE,
    ];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    /* Options form extras for the generic entity flag. */

    // Add checkboxes to show flag link on each entity view mode.
    $options = [];
    $defaults = [];
    $view_modes = \Drupal::entityManager()->getViewModes($this->entity_type);
    foreach ($view_modes as $name => $view_mode) {
      $options[$name] = t('Display on @name view mode', ['@name' => $view_mode['label']]);
      $defaults[$name] = $this->showInLinks($name);
    }

    $form['display']['show_in_links'] = [
      '#type' => 'checkboxes',
      '#title' => t('Display in entity links'),
      '#description' => t('Show the flag link with the other links on the entity.'),
      '#options' => $options,
      '#default_value' => $defaults,
    ];

    $form['display']['show_as_field'] = [
      '#type' => 'checkbox',
      '#title' => t('Display link as field'),
      '#description' => t('Show the flag link as a pseudofield, which can be ordered among other entity elements in the "Manage display" settings for the entity type.'),
      '#default_value' => $this->showAsField(),
    ];
    /*
    if (empty($entity_info['fieldable'])) {
      $form['display']['show_as_field']['#disabled'] = TRUE;
      $form['display']['show_as_field']['#description'] = t("This entity type is not fieldable.");
    }
    */
    $form['display']['show_on_form'] = [
      '#type' => 'checkbox',
      '#title' => t('Display checkbox on entity edit form'),
      '#default_value' => $this->showOnForm(),
      '#weight' => 5,
    ];

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
    $form['display']['show_contextual_link'] = [
      '#type' => 'checkbox',
      '#title' => t('Display in contextual links'),
      '#default_value' => $this->showContextualLink(),
      '#description' => t('Note that not all entity types support contextual links.'),
      '#access' => \Drupal::moduleHandler()->moduleExists('contextual'),
      '#weight' => 10,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();

    // Check each of the display modes for the show_in_links field.
    foreach ($form_values['show_in_links'] as $link_display) {
      if (!empty($link_display)) {
        return;
      }
    }

    // Check if the user selected display as a psudofield.
    if (!empty($form_values['show_as_field'])) {
      return;
    }

    // Check if the user selected display on the entity edit form.
    if (!empty($form_values['show_on_form'])) {
      return;
    }

    // Check if the user selected display as a contextual link.
    if (!empty($form_values['show_contextual_link'])) {
      return;
    }

    // If we're still here, no display was selected. Return a form error.
    $form_state->setErrorByName('show_as_field', 'No entity link display selected. Please select at least one link display such as \'Display link as field\'.');
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['show_in_links'] = $form_state->getValue('show_in_links');
    $this->configuration['show_as_field'] = $form_state->getValue('show_as_field');
    $this->configuration['show_on_form'] = $form_state->getValue('show_on_form');
    $this->configuration['show_contextual_link'] = $form_state->getValue('show_contextual_link');
  }

  /**
   * Return the show in links setting given a view mode.
   *
   * @param string $name
   *   The name of the view mode.
   *
   * @return mixed
   *   The name of the view mode if the flag appears in links, 0 otherwise.
   */
  public function showInLinks($name) {
    if (!empty($this->configuration['show_in_links'][$name])) {
      return $name;
    }

    return 0;
  }

  /**
   * Returns the show as field setting.
   *
   * @return bool
   *   TRUE if the flag should appear as a psudofield, FALSE otherwise.
   */
  public function showAsField() {
    return $this->configuration['show_as_field'];
  }

  /**
   * Returns the show on form setting.
   *
   * @return bool
   *   TRUE if the flag should appear on the entity form, FALSE otherwise.
   */
  public function showOnForm() {
    return $this->configuration['show_on_form'];
  }

  /**
   * Returns the show on contextual link setting.
   *
   * @return bool
   *   TRUE if the flag should appear in contextual links, FALSE otherwise.
   */
  public function showContextualLink() {
    return $this->configuration['show_contextual_link'];
  }
}
