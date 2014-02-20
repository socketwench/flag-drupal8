<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 9/28/13
 * Time: 10:23 PM
 */

namespace Drupal\flag\Form;

use Drupal\flag\Form\FlagFormBase;

class FlagAddForm extends FlagFormBase {

  protected function getRoleDefault($selction) {
    if ($selction == 0) {
      return array_keys(user_roles());
    }

    return array($selection);
  }

  public function buildForm(array $form, array &$form_state, $entity_type = NULL) {
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

  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Create Flag');
    return $actions;
  }
}