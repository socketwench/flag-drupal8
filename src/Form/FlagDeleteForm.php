<?php
/**
 * @file
 * Contains \Drupal\flag\Form\FlagDeleteForm.
 */

namespace Drupal\flag\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the flag delete form.
 *
 * Unlike the FlagAddForm and FlagEditForm, this class does not derive from
 * FlagFormBase. Instead, it derives directly from EntityConfirmFormBase.
 * The reason is that we only need to provide a simple yes or no page when
 * deleting a flag.
 */
class FlagDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete the Flag %label?', [
      '%label' => $this->entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('flag.list');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->delete();
    drupal_set_message(t('Flag %label was deleted.', [
     '%label' => $this->entity->label(),
    ]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
