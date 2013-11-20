<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/20/13
 * Time: 5:56 PM
 */

namespace Drupal\flag\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Session\AccountInterface;
use Drupal\flag\FlaggingInterface;

/**
 * Class Flagging
 * @package Drupal\flag\Entity
 *
 * @EntityType(
 *  id = "flagging",
 *  label = @Translation("Flagging"),
 *  module = "flag",
 *  controllers = {
 *    "storage" = "Drupal\Core\Entity\FieldableDatabaseStorageController",
 *  },
 *  base_table = "flagging",
 *  fieldable = TRUE,
 *  entity_keys = {
 *    "id" = "id",
 *    "bundle" = "type",
 *    "uuid" = "uuid"
 *  },
 *  bundle_keys = {
 *    "bundle" = "type"
 *  }
 * )
 */
class Flagging extends ContentEntityBase implements FlaggingInterface {

  public function getFlag() {
    return $this->bundle;
  }

  public static function baseFieldDefinitions($entity_type) {
    $properties['id'] = array(
      'label' => t('Flagging ID'),
      'description' => t('The Flagging ID.'),
      'type' => 'integer_field',
      'read-only' => TRUE,
    );
    $properties['type'] = array(
      'label' => t('Type'),
      'description' => t('The flag type.'),
      'type' => 'string_field',
      'read-only' => TRUE,
    );
    $properties['uuid'] = array(
      'label' => t('UUID'),
      'description' => t('The node UUID.'),
      'type' => 'uuid_field',
      'read-only' => TRUE,
    );
    $properties['uid'] = array(
      'label' => t('User ID'),
      'description' => t('The ID of the flagging user.'),
      'type' => 'entity_reference_field',
      'settings' => array(
        'target_type' => 'user',
        'default_value' => 0,
      ),
    );
    $properties['created'] = array(
      'label' => t('Created'),
      'description' => t('The time that the flagging was created.'),
      'type' => 'integer_field',
    );

    return $properties;
  }

} 