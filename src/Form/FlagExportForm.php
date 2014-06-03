<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 9/29/13
 * Time: 8:51 PM
 */

namespace Drupal\flag\Form;

use Drupal\Core\Form\FormBase;
use Drupal\flag\Handlers\AbstractFlag;


class FlagExportForm extends FormBase {

  public function getFormID() {
    return 'flag_export_form';
  }

  public function buildForm(array $form, array &$form_state) {
    // If we were passed a flag, use it as the list of flags to export.
    /*
    if ($flag) {
      $flags = array($flag);
    }
    */

    // Display a list of flags to export.
    if (!isset($flags)) {
      if (isset($form_state['values']['flags'])) {
        $flags = array();
        foreach ($form_state['values']['flags'] as $flag_name) {
          if ($flag_name && $flag = flag_get_flag($flag_name)) {
            $flags[] = $flag;
          }
        }
      }
      else {
        $form['flags'] = array(
          '#type' => 'checkboxes',
          '#title' => t('Flags to export'),
          '#options' => drupal_map_assoc(array_keys(flag_get_flags())),
          '#description' => t('Exporting your flags is useful for moving flags from one site to another, or when including your flag definitions in a module.'),
        );
        $form['submit'] = array(
          '#type' => 'submit',
          '#value' => t('Export'),
        );
      }
    }

    // @todo: Move to another controller
    if (isset($flags)) {
      $code = flag_export_flags($flags);

      // Link to the Features page if module is present, otherwise link to the
      // Drupal project page.
      $features_link = module_exists('features') ? url('admin/build/features') : url('http://drupal.org/project/features');

      $form['export'] = array(
        '#type' => 'textarea',
        '#title' => t('Flag exports'),
        '#description' => t('Use the exported code to later <a href="@import-flag">import</a> it. Exports can be included in modules using <a href="http://drupal.org/node/305086#default-flags">hook_flag_default_flags()</a> or using the <a href="@features-url">Features</a> module.', array('@import-flag' => url(FLAG_ADMIN_PATH . '/import'), '@features-url' => $features_link)),
        '#value' => $code,
        '#rows' => 15,
      );
    }

    return $form;

  }

  public function submitForm(array &$form, array &$form_state) {
    $form_state['rebuild'] = TRUE;
  }

}