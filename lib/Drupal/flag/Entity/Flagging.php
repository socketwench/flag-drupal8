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
use Drupal\Core\Field\FieldDefinition;
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
 *    "access" = "Drupal\flag\FlaggingAccessController",
 *  },
 *  base_table = "flagging",
 *  fieldable = TRUE,
 *  bundle_entity_type = "flag",
 *  entity_keys = {
 *    "id" = "id",
 *    "bundle" = "type",
 *    "uuid" = "uuid"
 *  },
 *  bundle_keys = {
 *    "bundle" = "type"
 *  },
 *  links = {
 *    "admin-form" = "flag_edit"
 *  }
 * )
 */
class Flagging extends ContentEntityBase implements FlaggingInterface {

  public function getFlag() {
    return $this->bundle;
  }

  public static function baseFieldDefinitions($entity_type) {
    $fields['id'] = FieldDefinition::create('integer')
      ->setLabel(t('Node ID'))
      ->setDescription(t('The flagging ID.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The flagging UUID.'))
      ->setReadOnly(TRUE);

    $fields['type'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Type'))
      ->setDescription(t('The flag type.'))
      ->setSetting('target_type', 'flag_flag')
      ->setReadOnly(TRUE);

    $fields['uid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('User ID'))
      ->setDescription(t('The user ID of the flagging user.'))
      ->setSettings(array(
        'target_type' => 'user',
        'default_value' => 0,
      ));

    // @todo Convert to a "created" field in https://drupal.org/node/2145103.
    $fields['created'] = FieldDefinition::create('integer')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the flagging was created.'));

    return $fields;
  }

} 