<?php
/**
 * @file
 */

namespace Drupal\flag\Form;

use Drupal\Core\Url;
use Drupal\flag\FlagInterface;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

class FlagDisableConfirmForm extends ConfirmFormBase {

  protected $flag;

  public function buildForm(array $form, FormStateInterface $form_state,
                            FlagInterface $flag = NULL) {
    $this->flag = $flag;
    return parent::buildForm($form, $form_state);
  }

  public function getFormID() {
    return 'flag_disable_confirm_form';
  }

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

  public function getDescription() {
    if ($this->flag->isEnabled()) {
      return t('Users will no longer be able to use the flag, but no data will be lost.');
    }

    return t('The flag will appear once more on configured nodes.');
  }

  public function getConfirmText() {
    if ($this->flag->isEnabled()) {
      return $this->t('Disable');
    }

    return $this->t('Enable');
  }

  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($this->flag->isEnabled()) {
      $this->flag->disable();
    }
    else {
      $this->flag->enable();
    }

    $this->flag->save();

    $form_state->setRedirect('flag.list');
  }

}