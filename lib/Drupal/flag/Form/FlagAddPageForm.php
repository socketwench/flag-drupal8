<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 9/28/13
 * Time: 3:03 PM
 */

namespace Drupal\flag\Form;

use Drupal\Core\Form\FormBase;
use Drupal\flag\Handlers\AbstractFlag;

class FlagAddPageForm extends FormBase{

   /**
    * {@inheritdoc}
    */
   public function getFormID() {
     return 'flag_add_page';
   }

  public function buildForm(array $form, array &$form_state) {
    $types = array();
    // @todo Use \Drupal::service() to get a list of FlagType plugins.
    //  print_r(\Drupal::service('plugin.manager.flag.flagtype')->getDefinitions());

    foreach (flag_fetch_definition() as $type => $info) {
      $types[$type] = $info['title'] . '<div class="description">' . $info['description'] . '</div>';
    }

    $form['type'] = array(
      '#type' => 'radios',
      '#title' => t('Flag type'),
      '#default_value' => 'node',
      '#description' => t('The type of object this flag will affect. This cannot be changed once the flag is created.'),
      '#required' => TRUE,
      '#options' => $types,
    );

    $form['actions'] = array(
      '#type' => 'actions',
    );

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Continue'),
    );

    return $form;
  }

  public function validateForm(array &$form, array &$form_state) {
    $flag = AbstractFlag::factory_by_entity_type($form_state['values']['type']);
    if (get_class($flag) == 'BrokenFlag') {
      form_set_error('type', t("This flag type, %type, isn't valid.", array('%type' => $form_state['values']['type'])));
    }
  }

  public function submitForm(array &$form, array &$form_state) {
    $form_state['redirect'] = FLAG_ADMIN_PATH . '/add/' . $form_state['values']['type'];
  }
} 