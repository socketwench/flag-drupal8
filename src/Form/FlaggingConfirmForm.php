<?php
/**
 * @file
 * Contains \Drupal\flag\Form\FlaggingConfirmForm.
 */

namespace Drupal\flag\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

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
   * @var \Drupal\flag\FlagInterface
   */
  protected $flag;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,
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
  public function getCancelUrl() {
    $destination = \Drupal::request()->get('destination');
    if (!empty($destination)) {
      return Url::createFromPath($destination);
    }

    return $this->entity->urlInfo();
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
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($this->isFlagged()) {
      \Drupal::service('flag')->unflagByObject($this->flag, $this->entity);
    }
    else {
      \Drupal::service('flag')->flagByObject($this->flag, $this->entity);
    }
  }

}
