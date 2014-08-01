<?php
/**
 * @file
 * Contains the ConfirmForm link type.
 */

namespace Drupal\flag\Plugin\ActionLink;

use Drupal\flag\ActionLinkTypeBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the Confirm Form link type.
 *
 * @package Drupal\flag\Plugin\ActionLink
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

    $options['flag_confirmation'] = 'Flag this content?';
    $options['unflag_confirmation'] = 'Unflag this content?';

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['display']['settings']['link_options_confirm'] = array(
      '#type' => 'fieldset',
      '#title' => t('Options for the "Confirmation form" link type'),
      // Any "link type" provider module must put its settings fields inside
      // a fieldset whose HTML ID is link-options-LINKTYPE, where LINKTYPE is
      // the machine-name of the link type. This is necessary for the
      // radiobutton's JavaScript dependency feature to work.
      '#id' => 'link-options-confirm',
    );

    $form['display']['settings']['link_options_confirm']['flag_confirmation'] = array(
      '#type' => 'textfield',
      '#title' => t('Flag confirmation message'),
      '#default_value' => $this->configuration['flag_confirmation'],
      '#description' => t('Message displayed if the user has clicked the "flag this" link and confirmation is required. Usually presented in the form of a question such as, "Are you sure you want to flag this content?"'),
      // This will get changed to a state by flag_link_type_options_states().
      '#required' => TRUE,
    );

    $form['display']['settings']['link_options_confirm']['unflag_confirmation'] = array(
      '#type' => 'textfield',
      '#title' => t('Unflag confirmation message'),
      '#default_value' => $this->configuration['unflag_confirmation'],
      '#description' => t('Message displayed if the user has clicked the "unflag this" link and confirmation is required. Usually presented in the form of a question such as, "Are you sure you want to unflag this content?"'),
      // This will get changed to a state by flag_link_type_options_states().
      '#required' => TRUE,
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['flag_confirmation'] = $form_state['values']['flag_confirmation'];
    $this->configuration['unflag_confirmation'] = $form_state['values']['unflag_confirmation'];
  }

  /**
   * Returns the flag confirm form question when flagging.
   * @return string
   */
  public function getFlagQuestion() {
    return $this->configuration['flag_confirmation'];
  }

  /**
   * Returns the flag confirm form question when unflagging.
   * @return string
   */
  public function getUnflagQuestion() {
    return $this->configuration['unflag_confirmation'];
  }

}