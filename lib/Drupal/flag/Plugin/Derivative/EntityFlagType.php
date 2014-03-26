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

  public function getDerivativeDefinitions($base_plugin_def) {
    $derivatives = array();
    foreach (\Drupal::entityManager()->getDefinitions() as $entity_id => $entity_info) {
      $derivatives[$entity_id] = array(
        'title' => $entity_id,
        'entity_type' => $entity_id,
      ) + $base_plugin_def;
    }

    return $derivatives;
  }
} 