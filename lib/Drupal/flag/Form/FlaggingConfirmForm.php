<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 3/6/14
 * Time: 7:37 PM
 */

namespace Drupal\flag\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\EntityInterface;
use Drupal\flag\FlagInterface;


class FlaggingConfirmForm extends ConfirmFormBase {

  protected $action;

  protected $entity;

  protected $flag;

  // @todo add parameters for the entity, flag
  public function buildForm(array $form, array &$form_state,
                            $action, FlagInterface $flag, EntityInterface $entity) {

    $this->action = $action;
    $this->flag = $flag;
    $this->entity = $entity;
  }

  public function getFormID() {
    return 'flag_flagging_confirm_form';
  }

  public function getQuestion() {
    $linkType = $this->flag->getLinkTypePlugin();

    if ($action == 'unflag') {
      return $linkType->getUnflagQuestion();
    }
    else {
      return $linkType->getFlagQuestion();
    }

  }

  public function getCancelRoute() {
    return $this->entity->urlInfo();
  }

  public function getDescription() {
    return $this->t('');
  }

  public function getConfirmText() {
    return $this->t('Flag');
  }

  public function submitForm(array &$form, array &$form_state) {

  }

} 