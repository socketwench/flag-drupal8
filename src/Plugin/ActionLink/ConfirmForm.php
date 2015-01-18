<?php
/**
 * @file
 * Contains the \Drupal\flag\Plugin\ActionLink\ConfirmForm link type.
 */

namespace Drupal\flag\Plugin\ActionLink;

use Drupal\flag\ActionLinkTypeBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Confirm Form link type.
 *
 * @ActionLinkType(
 *  id = "confirm",
 * label = @Translation("Confirm Form"),
 * description = "Redirects the user to a confirmation form."
 * )
 */
class ConfirmForm extends ActionLinkTypeBase {

  /**
   * {@inheritdoc}
   */
  public function routeName($action = NULL) {
    if ($action == 'unflag') {
      return 'flag.confirm_unflag';
    }

    return 'flag.confirm_flag';
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $options = parent::defaultConfiguration();

    $options += [
      'flag_confirmation' => 'Flag this content?',
      'unflag_confirmation' => 'Unflag this content?',
    ];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['display']['settings']['link_options_confirm'] = [
      '#type' => 'fieldset',
      '#title' => t('Options for the "Confirmation form" link type'),
      // Any "link type" provider module must put its settings fields inside
      // a fieldset whose HTML ID is link-options-LINKTYPE, where LINKTYPE is
      // the machine-name of the link type. This is necessary for the
      // radiobutton's JavaScript dependency feature to work.
      '#id' => 'link-options-confirm',
    ];

    $form['display']['settings']['link_options_confirm']['flag_confirmation'] = [
      '#type' => 'textfield',
      '#title' => t('Flag confirmation message'),
      '#default_value' => $this->getFlagQuestion(),
      '#description' => t('Message displayed if the user has clicked the "flag this" link and confirmation is required. Usually presented in the form of a question such as, "Are you sure you want to flag this content?"'),
      '#description_display' => 'after',
      // This will get changed to a state by flag_link_type_options_states().
      '#required' => TRUE,
    ];

    $form['display']['settings']['link_options_confirm']['unflag_confirmation'] = [
      '#type' => 'textfield',
      '#title' => t('Unflag confirmation message'),
      '#default_value' => $this->getUnflagQuestion(),
      '#description' => t('Message displayed if the user has clicked the "unflag this" link and confirmation is required. Usually presented in the form of a question such as, "Are you sure you want to unflag this content?"'),
      '#description_display' => 'after',
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

    if ($form_values['link_type'] == 'confirm') {
      if (empty($form_values['flag_confirmation'])) {
        $form_state->setErrorByName('flag_confirmation', 'A flag confirmation message is required when using the confirmation link type.');
      }
      if (empty($form_values['unflag_confirmation'])) {
        $form_state->setErrorByName('unflag_confirmation', 'An unflag confirmation message is required when using the confirmation link type.');
      }
    }

    if (!preg_match('/^[a-z_][a-z0-9_]*$/', $form_values['id'])) {
      $form_state->setErrorByName('label', 'The flag name may only contain lowercase letters, underscores, and numbers.');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['flag_confirmation'] = $form_state->getValue('flag_confirmation');
    $this->configuration['unflag_confirmation'] = $form_state->getValue('unflag_confirmation');
  }

  /**
   * Returns the flag confirm form question when flagging.
   *
   * @return string
   *   A string containing the flag question to display.
   */
  public function getFlagQuestion() {
    return $this->configuration['flag_confirmation'];
  }

  /**
   * Returns the flag confirm form question when unflagging.
   *
   * @return string
   *   A string containing the unflag question to display.
   */
  public function getUnflagQuestion() {
    return $this->configuration['unflag_confirmation'];
  }

}
