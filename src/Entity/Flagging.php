<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/20/13
 * Time: 5:56 PM
 */

namespace Drupal\flag\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
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
 * @ContentEntityType(
 *  id = "flagging",
 *  label = @Translation("Flagging"),
 *  controllers = {
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
 *    "admin-form" = "flag.edit"
 *  }
 * )
 */
class Flagging extends ContentEntityBase implements FlaggingInterface {

  public function getFlagId() {
    return $this->get('fid')->value;
  }

  public function getFlag() {
    return entity_load($this->bundle, $this->getFlagId());
  }

  public function getFlaggableType() {
    return $this->get('entity_type')->value;
  }

  public function getFlaggableId() {
    return $this->get('entity_id')->value;
  }

  public function getFlaggable() {
    return entity_load($this->getFlaggableType(), $this->getFlaggableId());
  }

  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['id'] = FieldDefinition::create('integer')
      ->setLabel(t('Flagging ID'))
      ->setDescription(t('The flagging ID.'))
      ->setReadOnly(TRUE);

    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The flagging UUID.'))
      ->setReadOnly(TRUE);

    $fields['fid'] = FieldDefinition::create('integer')
      ->setLabel(t('Flag ID'))
      ->setDescription(t('The Flag ID.'))
      ->setReadOnly(TRUE);

    $fields['entity_type'] = FieldDefinition::create('string')
      ->setLabel(t('Entity Type'))
      ->setDescription(t('The Entity Type.'));

    $fields['entity_id'] = FieldDefinition::create('string')
      ->setLabel(t('Entity ID'))
      ->setDescription(t('The Entity ID.'));

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

    $fields['created'] = FieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the flagging was created.'));

    return $fields;
  }

} 