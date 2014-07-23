<?php
/**
 * @file
 * Contains the BrokenFlagType class.
 */

namespace Drupal\flag;

use Drupal\flag\Plugin\Flag\FlagTypeBase;

/**
 * Class BrokenFlagType
 * @package Drupal\flag\Plugin\Flag
 *
 * A dummy Flag Type to be used where the real implementation can't be found.
 */
class BrokenFlagType extends FlagTypeBase {

  public function options_form(&$form) {
    drupal_set_message(t("The module providing this flag wasn't found, or this flag type, %type, isn't valid.", array('%type' => $this->entity_type)), 'error');
    $form = array();
  }

} 