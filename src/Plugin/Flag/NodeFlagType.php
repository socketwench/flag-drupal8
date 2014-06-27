<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/28/13
 * Time: 6:58 PM
 */

namespace Drupal\flag\Plugin\Flag;

use Drupal\flag\Plugin\Flag\EntityFlagType;

/**
 * Class NodeFlagType
 * @package Drupal\flag\Plugin\Flag
 *
 * Implements a node flag type.
 *
 * @FlagType(
 *   id = "flagtype_node",
 *   title = @Translation("Content"),
 *   entity_type = "node",
 *   provider = "node"
 * )
 */
class NodeFlagType extends EntityFlagType {

  public function defaultConfiguration() {
    $options = parent::defaultConfiguration();
    // Use own display settings in the meanwhile.
    $options += array(
      'i18n' => 0,
      'access_author' => '',
    );
    return $options;
  }

  /**
   * Options form extras for node flags.
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['access']['access_author'] = array(
      '#type' => 'radios',
      '#title' => t('Flag access by content authorship'),
      '#options' => array(
        '' => t('No additional restrictions'),
        'own' => t('Users may only flag content they own'),
        'others' => t('Users may only flag content of others'),
      ),
      '#default_value' => $this->configuration['access_author'],
      '#description' => t("Restrict access to this flag based on the user's ownership of the content. Users must also have access to the flag through the role settings."),
    );

    // Support for i18n flagging requires Translation helpers module.
    $form['i18n'] = array(
      '#type' => 'radios',
      '#title' => t('Internationalization'),
      '#options' => array(
        '1' => t('Flag translations of content as a group'),
        '0' => t('Flag each translation of content separately'),
      ),
      //'#default_value' => $this->i18n,
      '#description' => t('Flagging translations as a group effectively allows users to flag the original piece of content regardless of the translation they are viewing. Changing this setting will <strong>not</strong> update content that has been flagged already.'),
      '#access' => module_exists('translation_helpers'),
      '#weight' => 5,
    );

    return $form;
  }

  public function submitConfigurationForm(array &$form, array &$form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['access_author'] = $form_state['values']['access_author'];
    $this->configuration['i18n'] = $form_state['values']['i18n'];
  }

  function type_access_multiple($entity_ids, $account) {
    $access = array();

    // If all subtypes are allowed, we have nothing to say here.
    if (empty($this->types)) {
      return $access;
    }

    // Ensure that only flaggable node types are granted access. This avoids a
    // node_load() on every type, usually done by applies_to_entity_id().
    $result = db_select('node', 'n')->fields('n', array('nid'))
      ->condition('nid', array_keys($entity_ids), 'IN')
      ->condition('type', $this->types, 'NOT IN')
      ->execute();
    foreach ($result as $row) {
      $access[$row->nid] = FALSE;
    }

    return $access;
  }

  function getAccessAuthorSetting() {
    return $this->configuration['access_author'];
  }

  function getInternationalizationSetting() {
    $this->configuration['i18n'];
  }
} 