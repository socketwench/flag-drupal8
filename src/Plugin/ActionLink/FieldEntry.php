<?php
/**
 * @file
 * Contains the FieldEntry link type.
 */

namespace Drupal\flag\Plugin\ActionLink;

use Drupal\flag\ActionLinkTypeBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class FieldEntry
 * @package Drupal\flag\Plugin\ActionLink
 *
 * @ActionLinkType(
 *  id = "field_entry",
 *  label = @Translation("Field Entry Form"),
 *  description = "Redirects the user to a field entry form."
 * )
 */
class FieldEntry extends ActionLinkTypeBase {

  /**
   * {@inheritdoc}
   */
  public function routeName($action = NULL) {
    if ($action == 'unflag') {
      return 'flag.field_entry.edit';
    }

    return 'flag.field_entry';
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $options = parent::defaultConfiguration();

    $options['flag_confirmation'] = 'Enter flagging details';
    $options['edit_flagging'] = 'Edit flagging details';
    $options['unflag_confirmation'] = 'Unflag this content?';

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['display']['settings']['link_options_field'] = [
      '#type' => 'fieldset',
      '#title' => t('Options for the "Field entry" link type'),
      // Any "link type" provider module must put its settings fields inside
      // a fieldset whose HTML ID is link-options-LINKTYPE, where LINKTYPE is
      // the machine-name of the link type. This is necessary for the
      // radiobutton's JavaScript dependency feature to work.
      '#id' => 'link-options-field_entry',
    ];

    $form['display']['settings']['link_options_field']['flag_confirmation'] = [
      '#type' => 'textfield',
      '#title' => t('Flag confirmation message'),
      '#default_value' => $this->configuration['flag_confirmation'],
      '#description' => t('Message displayed if the user has clicked the "flag this" link and field entry is required. Usually presented in the form such as, "Please enter the flagging details."'),
      // This will get changed to a state by flag_link_type_options_states().
      '#required' => TRUE,
    ];

    $form['display']['settings']['link_options_field']['flagging_edit_title'] = [
      '#type' => 'textfield',
      '#title' => t('Enter flagging details message'),
      '#default_value' => $this->configuration['edit_flagging'],
      '#description' => t('Message displayed if the user has clicked the "Edit flag" link. Usually presented in the form such as, "Please enter the flagging details."'),
      // This will get changed to a state by flag_link_type_options_states().
      '#required' => TRUE,
    ];

    $form['display']['settings']['link_options_field']['unflag_confirmation'] = [
      '#type' => 'textfield',
      '#title' => t('Unflag confirmation message'),
      '#default_value' => $this->configuration['unflag_confirmation'],
      '#description' => t('Message displayed if the user has clicked the "delete flag" link in the field entry form. Usually presented in the form of a question such as, "Are you sure you want to unflag this content?"'),
      // This will get changed to a state by flag_link_type_options_states().
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    $form_values = $form_state->getValues();

    if ($form_state->getValue('link_type') == 'field_entry') {
      if (empty($form_values['flag_confirmation'])) {
        $form_state->setErrorByName('flag_confirmation', 'A flag confirmation message is required when using the field entry link type.');
      }
      if (empty($form_values['flagging_edit_title'])) {
        $form_state->setErrorByName('flagging_edit_title', 'An edit flagging details message is required when using the field entry link type.');
      }
      if (empty($form_values['unflag_confirmation'])) {
        $form_state->setErrorByName('unflag_confirmation', 'An unflag confirmation message is required when using the field entry link type.');
      }
    }

    if (!preg_match('/^[a-z_][a-z0-9_]*$/', $form_state->getValue('id'))) {
      $form_state->setErrorByName('label', 'The flag name may only contain lowercase letters, underscores, and numbers.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);

    $this->configuration['flag_confirmation'] = $form_state->getValue('flag_confirmation');
    $this->configuration['edit_flagging'] = $form_state->getValue('flagging_edit_title');
    $this->configuration['unflag_confirmation'] = $form_state->getValue('unflag_confirmation');
  }

  /**
   * Returns the flag confirm form question when flagging.
   *
   * We're copying the confirm form link type interface here so we can take
   * advantage of the existing confirm form code without duplicating the class.
   *
   * @return string
   *   A string containing the flag question to display.
   */
  public function getFlagQuestion() {
    return $this->configuration['flag_confirmation'];
  }

  /**
   * Returns the edit flagging details form title.
   *
   * @return string
   *   A string containing the edit flagging details title to display.
   */
  public function getEditFlaggingTitle() {
    return $this->configuration['edit_flagging'];
  }

  /**
   * Returns the flag confirm form question when unflagging.
   *
   * We're copying the confirm form link type interface here so we can take
   * advantage of the existing confirm form code without duplicating the class.
   *
   * @return string
   *   A string containing the unflag question to display.
   */
  public function getUnflagQuestion() {
    return $this->configuration['unflag_confirmation'];
  }
}
