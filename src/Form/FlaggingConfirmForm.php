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
 *
 * @see \Drupal\flag\Plugin\ActionLink\ConfirmForm
 */
class FlaggingConfirmForm extends ConfirmFormBase {

  /**
   * The flaggable entity.
   *
   * @var \Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * The flag entity.
   *
   * @var \Drupal\flag\FlagInterface
   */
  protected $flag;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,
                            $flag_id = NULL, $entity_id = NULL) {

    $flag_service = \Drupal::service('flag');
    $this->flag = $flag_service->getFlagByID($flag_id);
    $this->entity = $flag_service->getFlaggableById($this->flag, $entity_id);
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
    $link_type = $this->flag->getLinkTypePlugin();

    if ($this->isFlagged()) {
      return $link_type->getUnflagQuestion();
    }

    return $link_type->getFlagQuestion();
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
      return $this->flag->getUnflagLongText();
    }

    return $this->flag->getFlagLongText();
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

  /**
   * Helper method to determine if the entity has been flagged or not.
   *
   * @return bool
   *   TRUE if the current entity is flagged, FALSE otherwise.
   */
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
