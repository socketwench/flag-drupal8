<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 1/13/14
 * Time: 8:23 PM
 */

namespace Drupal\flag\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;

class FlagDeleteForm extends EntityConfirmFormBase {

  public function getQuestion() {
    return t('Are you sure you want to delete the Flag %label?', array(
      '%label' => $this->entity->label()
    ));
  }

  public function getConfirmText() {
    return t('Delete');
  }

  public function getCancelRoute() {
    return new URL('flag.list');
  }

  public function submit(array $form, array &$form_state) {
    $this->entity->delete();
    drupal_set_message(t('Flag %label was deleted.', array(
     '%label' => $this->entity->label(),
    )));

    $form_state['redirect_route']['route_name'] = 'flag.list';
  }

} 