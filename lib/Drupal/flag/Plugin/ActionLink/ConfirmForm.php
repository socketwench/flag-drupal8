<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 3/4/14
 * Time: 9:51 PM
 */

namespace Drupal\flag\Plugin\ActionLink;

use Drupal\flag\ActionLinkTypeBase;

/**
 * Class ConfirmForm
 * @package Drupal\flag\Plugin\ActionLink
 *
 * @ActionLinkType(
 *  id = "confirm",
 * label = @Translation("Confirm Form"),
 * description = "Redirects the user to a confirmation form."
 * )
 */
class ConfirmForm extends ActionLinkTypeBase {

  public function routeName($action = NULL) {
    if ($action == 'unflag') {
      return 'flag_confirm_unflag';
    }

    return 'flag_confirm_flag';
  }

  public function defaultConfiguration() {
    $options = parent::defaultConfiguration();

    $options += array(
      'flag_confirmation' => '',
      'unflag_confirmation' => '',
    );

    return $options;
  }

  public function buildConfigurationForm(array $form, array &$form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);


    $form['display']['link_options_confirm'] = array(
      '#type' => 'fieldset',
      '#title' => t('Options for the "Confirmation form" link type'),
      // Any "link type" provider module must put its settings fields inside
      // a fieldset whose HTML ID is link-options-LINKTYPE, where LINKTYPE is
      // the machine-name of the link type. This is necessary for the
      // radiobutton's JavaScript dependency feature to work.
      '#id' => 'link-options-confirm',
      '#weight' => 21,
    );

    $form['display']['link_options_confirm']['flag_confirmation'] = array(
      '#type' => 'textfield',
      '#title' => t('Flag confirmation message'),
      '#default_value' => $this->configuration['flag_confirmation'],
      '#description' => t('Message displayed if the user has clicked the "flag this" link and confirmation is required. Usually presented in the form of a question such as, "Are you sure you want to flag this content?"'),
      // This will get changed to a state by flag_link_type_options_states().
      '#required' => TRUE,
    );

    $form['display']['link_options_confirm']['unflag_confirmation'] = array(
      '#type' => 'textfield',
      '#title' => t('Unflag confirmation message'),
      '#default_value' => $this->configuration['unflag_confirmation'],
      '#description' => t('Message displayed if the user has clicked the "unflag this" link and confirmation is required. Usually presented in the form of a question such as, "Are you sure you want to unflag this content?"'),
      // This will get changed to a state by flag_link_type_options_states().
      '#required' => TRUE,
    );

    return $form;
  }

  public function submitConfigurationForm(array &$form, array &$form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['flag_confirmation'] = $form_state['values']['flag_confirmation'];
    $this->configuration['unflag_confirmation'] = $form_state['values']['unflag_confirmation'];
  }

  public function getFlagQuestion() {
    return $this->configuration['flag_confirmation'];
  }

  public function getUnflagQuestion() {
    return $this->configuration['unflag_confirmation'];
  }

} 