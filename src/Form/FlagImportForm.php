<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 9/29/13
 * Time: 8:11 PM
 */

namespace Drupal\flag\Form;

use Drupal\Core\Form\FormBase;
use Drupal\flag\Handlers\AbstractFlag;

class FlagImportForm extends FormBase{

  public function getFormID() {
    return 'flag_import_form';
  }

  public function buildForm(array $form, array &$form_state) {
    $form = array();

    $form['import'] = array(
      '#title' => t('Flag import code'),
      '#type' => 'textarea',
      '#default_value' => '',
      '#rows' => 15,
      '#required' => TRUE,
      '#description' => t('Paste the code from a <a href="@export-url">flag export</a> here to import it into you site. Flags imported with the same name will update existing flags. Flags with a new name will be created.', array('@export-url' => url(FLAG_ADMIN_PATH . '/export'))),
    );
    $form['submit'] = array(
      '#value' => t('Import'),
      '#type' => 'submit',
    );

    return $form;
  }

  public function validateForm(array &$form, array &$form_state) {
    $flags = array();
    ob_start();
    eval($form_state['values']['import']);
    ob_end_clean();

    if (!isset($flags) || !is_array($flags)) {
      form_set_error('import', t('A valid list of flags could not be found in the import code.'));
      return;
    }

    // Create the flag object.
    foreach ($flags as $flag_name => $flag_info) {
      // Backward compatibility: old exported flags have their names in $flag_info
      // instead, so we use the += operator to not overwrite it.
      $flag_info += array(
        'name' => $flag_name,
      );
      $new_flag = AbstractFlag::factory_by_array($flag_info);

      // Give new flags with the same name a matching FID, which tells Flag to
      // update the existing flag, rather than creating a new one.
      if ($existing_flag = flag_get_flag($new_flag->name)) {
        $new_flag->fid = $existing_flag->fid;
      }

      if ($errors = $new_flag->validate()) {
        $message = t('The import of the %flag flag failed because the following errors were encountered during the import:', array('%flag' => $new_flag->name));
        $message_errors = array();
        foreach ($errors as $field => $field_errors) {
          foreach ($field_errors as $error) {
            $message_errors[] = $error['message'];
          }
        }
        form_set_error('import', $message . theme('item_list', array('items' => $message_errors)));
      }
      else {
        // Save the new flag for the submit handler.
        $form_state['flags'][] = $new_flag;
      }
    }
  }

  public function submitForm(array &$form, array &$form_state) {
    module_load_include('inc', 'flag', 'includes/flag.admin');

    // Build up values for the cache clear.
    $entity_types = array();
    $new = FALSE;

    foreach ($form_state['flags'] as $flag) {
      $flag->save();
      if (!empty($flag->status)) {
        $flag->enable();
      }
      if ($flag->is_new) {
        drupal_set_message(t('Flag @name has been imported.', array('@name' => $flag->name)));
        $new = TRUE;
      }
      else {
        drupal_set_message(t('Flag @name has been updated.', array('@name' => $flag->name)));
      }
      $entity_types[] = $flag->entity_type;
    }
    _flag_clear_cache($entity_types, $new);

    $form_state['redirect'] = FLAG_ADMIN_PATH;
  }

}