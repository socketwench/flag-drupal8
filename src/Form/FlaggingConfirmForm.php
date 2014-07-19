<?php
/**
 * @file
 * Contains the FlaggingConfirmForm.
 */

namespace Drupal\flag\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityInterface;
use Drupal\flag\FlagInterface;


/**
 * Provides the form page for the Confirm Form link type.
 * @package Drupal\flag\Form
 * @see \Drupal\flag\Plugin\ActionLink\ConfirmForm
 */
class FlaggingConfirmForm extends ConfirmFormBase {

  /**
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * @var \Drupal\flag\Entity\Flag
   */
  protected $flag;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state,
                            $flag_id = NULL, $entity_id = NULL) {

    $flagService = \Drupal::service('flag');
    $this->flag = $flagService->getFlagByID($flag_id);
    $this->entity = $flagService->getFlaggableById($this->flag, $entity_id);
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'flag_flagging_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $linkType = $this->flag->getLinkTypePlugin();

    if ($this->isFlagged()) {
      return $linkType->getUnflagQuestion();
    }

    return $linkType->getFlagQuestion();
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    $destination = \Drupal::request()->get('destination');
    if (!empty($destination)) {
      return URL::createFromPath($destination);
    }

    $route_name = $this->entity->urlInfo();

    return new URL($route_name['canonical']);
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    if ($this->isFlagged()) {
      return $this->flag->unflag_long;
    }

    return $this->flag->flag_long;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    if ($this->isFlagged()) {
      return $this->t('Unflag');
    }

    return $this->t('Flag');
  }

  protected function isFlagged() {
    return $this->flag->isFlagged($this->entity);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    if ($this->isFlagged()) {
      \Drupal::service('flag')->unflagByObject($this->flag, $this->entity);
    }
    else {
      \Drupal::service('flag')->flagByObject($this->flag, $this->entity);
    }
  }

}