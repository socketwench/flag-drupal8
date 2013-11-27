<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 11/23/13
 * Time: 7:01 PM
 */

namespace Drupal\flag\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DerivativeBase;

class EntityFlagType extends DerivativeBase {
/*
  public function __construct($base_plugin_id, EntityStorageControllerInterface $storageController) {
    return
  }
*/

  public function getDerivativeDefinitions(array $base_plugin_def) {
    $derivatives = array();
    foreach (entity_get_info() as $entity_id => $entity_info) {
      $derivatives[$entity_id] = array(
        'title' => $entity_id . " flag type",
        'entity' => $entity_id,
      ) + $base_plugin_def;
    }

    return $derivatives;
  }
} 