<?php
/**
 * @file
 * Contains \Drupal\flag\Plugin\Flag\NodeFlagType.
 */

namespace Drupal\flag\Plugin\Flag;

use Drupal\Core\Session\AccountInterface;
use Drupal\flag\Plugin\Flag\EntityFlagType;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a flag type for all content entities.
 *
 * @FlagType(
 *   id = "flagtype_node",
 *   title = @Translation("Content"),
 *   entity_type = "node",
 *   provider = "node"
 * )
 */
class NodeFlagType extends EntityFlagType {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $options = parent::defaultConfiguration();
    // Use own display settings in the meanwhile.
    $options += [
      'i18n' => 0,
      'access_author' => '',
    ];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    /* Options form extras for node flags. */

    $form['access']['access_author'] = [
      '#type' => 'radios',
      '#title' => t('Flag access by content authorship'),
      '#options' => [
        '' => t('No additional restrictions'),
        'own' => t('Users may only flag content they own'),
        'others' => t('Users may only flag content of others'),
      ],
      '#default_value' => $this->getAccessAuthorSetting(),
      '#description' => t("Restrict access to this flag based on the user's ownership of the content. Users must also have access to the flag through the role settings."),
    ];

    // Support for i18n flagging requires Translation helpers module.
    $form['i18n'] = [
      '#type' => 'radios',
      '#title' => t('Internationalization'),
      '#options' => [
        '1' => t('Flag translations of content as a group'),
        '0' => t('Flag each translation of content separately'),
      ],
      '#default_value' => $this->getInternationalizationSetting(),
      '#description' => t('Flagging translations as a group effectively allows users to flag the original piece of content regardless of the translation they are viewing. Changing this setting will <strong>not</strong> update content that has been flagged already.'),
      '#access' => \Drupal::moduleHandler()->moduleExists('translation_helpers'),
      '#weight' => 5,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['access_author'] = $form_state->getValue('access_author');
    $this->configuration['i18n'] = $form_state->getValue('i18n');
  }

  /**
   * {@inheritdoc}
   */
  public function typeAccessMultiple(array $entity_ids, AccountInterface $account) {
    $access = [];

    // If all subtypes are allowed, we have nothing to say here.
    if (empty($this->types)) {
      return $access;
    }

    // Ensure that only flaggable node types are granted access. This avoids a
    // node_load() on every type, usually done by applies_to_entity_id().
    $result = db_select('node', 'n')->fields('n', ['nid'])
      ->condition('nid', array_keys($entity_ids), 'IN')
      ->condition('type', $this->types, 'NOT IN')
      ->execute();
    foreach ($result as $row) {
      $access[$row->nid] = FALSE;
    }

    return $access;
  }

  /**
   * Returns the flag type access author setting.
   *
   * @return string
   *   The access author setting can be one of three values:
   *   - '' = No additional restrictions.
   *   - 'own' = Users may only flag content they own.
   *   - 'others' = Users may only flag content of others.
   */
  public function getAccessAuthorSetting() {
    return $this->configuration['access_author'];
  }

  /**
   * Returns the internationalization setting for the flag type.
   *
   * @return int
   *   The internationalization setting can be one of two values:
   *   - 1 = Flag translations of content as a group.
   *   - 0 = Flag each translation of content separately.
   */
  public function getInternationalizationSetting() {
    return $this->configuration['i18n'];
  }
}
