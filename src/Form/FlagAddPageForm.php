<?php
/**
 * @file
 * Contains the FlagAddPageForm class.
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
 * @package Drupal\flag\Form
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

    $form['flag_basic_info'] = [
      '#type' => 'fieldset',
      '#title' => t('Flag basic info'),
      '#collapsable' => FALSE,
      '#weight' => -10,
    ];
    $form['flag_basic_info']['label'] = [
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#description' => t('A short, descriptive title for this flag. It will be used in administrative interfaces to refer to this flag, and in page titles and menu items of some <a href="@insite-views-url">views</a> this module provides (theses are customizable, though). Some examples could be <em>Bookmarks</em>, <em>Favorites</em>, or <em>Offensive</em>.', array('@insite-views-url' => Url::fromRoute('views_ui.list'))),
      '#maxlength' => 255,
      '#required' => TRUE,
      '#weight' => -3,
    ];
    $form['flag_basic_info']['id'] = [
      '#type' => 'machine_name',
      '#title' => t('Machine name'),
      '#description' => t('The machine-name for this flag. It may be up to 32 characters long and may only contain lowercase letters, underscores, and numbers. It will be used in URLs and in all API calls.'),
      '#weight' => -2,
      '#machine_name' => [
          'exists' => [$this, 'exists'],
        'source' => ['flag_basic_info', 'label'],
      ],
    ];

    $form['flag_type_info'] = [
      '#type' => 'fieldset',
      '#title' => t('Type and Action'),
      '#attributes' => [
        'class' => ['container-inline'],
      ],
    ];

    $form['flag_type_info']['flag_entity_type'] = [
      '#type' => 'select',
      '#title' => t('Flag'),
      '#options' => \Drupal::service('plugin.manager.flag.flagtype')->getAllFlagTypes(),
      '#default_value' => 'flagtype_node',
    ];

    $form['flag_type_info']['flag_link_type'] = [
      '#type' => 'select',
      '#title' => t('using'),
      '#options' => \Drupal::service('plugin.manager.flag.linktype')->getAllLinkTypes(),
      '#default_value' => 'reload',
    ];

    $types = [];

    $plugins = \Drupal::service('plugin.manager.flag.flagtype')->getDefinitions();

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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /*
    $flag = AbstractFlag::factory_by_entity_type($form_state->getValue('type'));
    if (get_class($flag) == 'BrokenFlag') {
      form_set_error('type',
                     t("This flag type, %type, isn't valid.",
                     array('%type' => $form_state->getValue('type'))));
    }
    */
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $form_state->setRedirect('flag.add', [
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
