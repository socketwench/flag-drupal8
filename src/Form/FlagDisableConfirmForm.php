<?php
/**
 * @file
 * Contains the \Drupal\flag\Form\FlagDisableConfirmForm class.
 */

namespace Drupal\flag\Form;

use Drupal\Core\Url;
use Drupal\flag\FlagInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the flag enable/disable confirmation form.
 */
class FlagDisableConfirmForm extends ConfirmFormBase {

  protected $flag;

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state,
                            FlagInterface $flag = NULL) {
    $this->flag = $flag;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'flag_disable_confirm_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    if ($this->flag->isEnabled()) {
      return t('Disable flag @name?', array('@name' => $this->flag->label()));
    }

    return t('Enable flag @name?', array('@name' => $this->flag->label()));
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
  public function getDescription() {
    if ($this->flag->isEnabled()) {
      return t('Users will no longer be able to use the flag, but no data will be lost.');
    }

    return t('The flag will appear once more on configured nodes.');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    if ($this->flag->isEnabled()) {
      return $this->t('Disable');
    }

    return $this->t('Enable');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Toggle the flag state.
    if ($this->flag->isEnabled()) {
      $this->flag->disable();
    }
    else {
      $this->flag->enable();
    }

    // Invalidate the flaggable render cache.
    \Drupal::entityManager()
      ->getViewBuilder($this->flag->entity_type)
      ->resetCache();

    // Save The flag entity.
    $this->flag->save();

    // Redirect to the flag admin page.
    $form_state->setRedirect('flag.list');
  }

}
