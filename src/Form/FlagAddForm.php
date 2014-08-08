<?php
/**
 * @file
 * Contains the FlagAddForm class.
 */

namespace Drupal\flag\Form;

use Drupal\flag\Form\FlagFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the flag add form.
 *
 * Like FlagEditForm, this class derives from FlagFormBase. This class modifies
 * the base class behavior in two key ways: It alters the text of the submit
 * button, and form where default values are loaded.
 *
 * @package Drupal\flag\Form
 * @see \Drupal\flag\Form\FlagFormBase
 */
class FlagAddForm extends FlagFormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $entity_type = NULL) {
    //@todo Check all non-form_* params with check_plain().

    $tempstore = \Drupal::service('user.tempstore')->get('flag');
    $step1_form = $tempstore->get('FlagAddPage');

    $flag = $this->entity;
    $flag->label = $step1_form['label'];
    $flag->id = $step1_form['id'];

    $flag->setFlagTypePlugin($step1_form['flag_entity_type']);
    $flag->setLinkTypePlugin($step1_form['flag_link_type']);

    // Mark the flag as new.
    $flag->is_new = TRUE;

    $form = parent::buildForm($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Create Flag');
    return $actions;
  }
}