<?php
/**
 * @file
 * Contains the \Drupal\flag\Form\FlagAddPageForm class.
 */

namespace Drupal\flag\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides the flag add page.
 *
 * Flags are created in a two step process. This form provides a simple form
 * that allows the administrator to select key values that are necessary to
 * initialize the flag entity. Most importantly, this includes the FlagType.
 *
 * @see \Drupal\flag\FlagTypeBase
 */
class FlagAddPageForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'flag_add_page';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['flag_entity_type'] = [
      '#type' => 'radios',
      '#title' => t('Flag Type'),
      '#required' => TRUE,
      '#description' => t('The type of object this flag will affect. This cannot be changed once the flag is created.'),
      '#default_value' => 'flagtype_node',
      '#options' => \Drupal::service('plugin.manager.flag.flagtype')->getAllFlagTypes(),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => t('Continue'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('entity.flag.add_form', [
      'entity_type' => $form_state->getValue('flag_entity_type')
    ]);

    $tempstore = \Drupal::service('user.tempstore')->get('flag');
    $tempstore->set('FlagAddPage', $form_state->getValues());
  }

  /**
   * Determines if the flag already exists.
   *
   * @param string $id
   *   The flag ID
   *
   * @return bool
   *   TRUE if the flag exists, FALSE otherwise.
   */
  public function exists($id) {
    // @todo: Make this injected like ActionFormBase::exists().
    return \Drupal::entityManager()->getStorage('flag')->load($id);
  }
}
