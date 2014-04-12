<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 3/6/14
 * Time: 7:37 PM
 */

namespace Drupal\flag\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\flag\FlagInterface;


class FlaggingConfirmForm extends ConfirmFormBase {

  protected $entity;

  protected $flag;

  public function buildForm(array $form, array &$form_state,
                            $flag_id = NULL, $entity_id = NULL) {

    $flagService = \Drupal::service('flag');
    $this->flag = $flagService->getFlagByID($flag_id);
    $this->entity = $flagService->getFlaggableById($this->flag, $entity_id);
    return parent::buildForm($form, $form_state);
  }

  public function getFormID() {
    return 'flag_flagging_confirm_form';
  }

  public function getQuestion() {
    $linkType = $this->flag->getLinkTypePlugin();

    if ($this->isFlagged()) {
      return $linkType->getUnflagQuestion();
    }
    else {
      return $linkType->getFlagQuestion();
    }

  }

  public function getCancelRoute() {
    $destination = \Drupal::request()->get('destination');
    if (!empty($destination)) {
      return URL::createFromPath($destination);
    }

    return $this->entity->urlInfo();
  }

  public function getDescription() {
    if ($this->isFlagged()) {
      return $this->flag->unflag_long;
    }

    return $this->flag->flag_long;
  }

  public function getConfirmText() {
    if ($this->isFlagged()) {
      return $this->t('Unflag');
    }

    return $this->t('Flag');
  }

  protected function isFlagged() {
    return $this->flag->isFlagged($this->entity);
  }

  public function submitForm(array &$form, array &$form_state) {
    if ($this->isFlagged()) {
      \Drupal::service('flag')->unflagByObject($this->flag, $this->entity);
    }
    else {
      \Drupal::service('flag')->flagByObject($this->flag, $this->entity);
    }
  }

} 