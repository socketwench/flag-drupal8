<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 9/26/13
 * Time: 3:21 PM
 */

namespace Drupal\flag\Handlers;

/**
 * A dummy flag to be used where the real implementation can't be found.
 */
class BrokenFlag extends AbstractFlag {
  function options_form(&$form) {
    drupal_set_message(t("The module providing this flag wasn't found, or this flag type, %type, isn't valid.", array('%type' => $this->entity_type)), 'error');
    $form = array();
  }
}
