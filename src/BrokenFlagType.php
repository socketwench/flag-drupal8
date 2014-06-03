<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/27/13
 * Time: 8:34 PM
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

  function options_form(&$form) {
    drupal_set_message(t("The module providing this flag wasn't found, or this flag type, %type, isn't valid.", array('%type' => $this->entity_type)), 'error');
    $form = array();
  }

} 