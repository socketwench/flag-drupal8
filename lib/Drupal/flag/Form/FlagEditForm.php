<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 1/3/14
 * Time: 11:01 PM
 */

namespace Drupal\flag\Form;

use Drupal\flag\Form\FlagFormBase;

class FlagEditForm extends FlagFormBase {

  protected function actions(array $form, array &$form_state) {
    $actions = parent::actions($form, $form_state);
    $actions['submit']['#value'] = t('Save Flag');
    return $actions;
  }

} 