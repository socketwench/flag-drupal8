<?php
/**
 * @file
 * Contains \Drupal\flag\Plugin\Flag\CommentFlagType.
 */

namespace Drupal\flag\Plugin\Flag;

use Drupal\flag\Plugin\Flag\EntityFlagType;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CommentFlagType
 * @package Drupal\flag\Plugin\Flag
 *
 * Implements a comment flag.
 *
 * @FlagType(
 *   id = "flagtype_comment",
 *   title = @Translation("Comment"),
 *   entity_type = "comment",
 *   provider = "comment"
 * )
 */
class CommentFlagType extends EntityFlagType {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $options = parent::defaultConfiguration();
    $options += array(
      'access_author' => '',
    );
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form = parent::buildConfigurationForm($form, $form_state);

    /* Options form extras for comment flags. */

    $form['access']['access_author'] = array(
      '#type' => 'radios',
      '#title' => t('Flag access by content authorship'),
      '#options' => array(
        '' => t('No additional restrictions'),
        'comment_own' => t('Users may only flag own comments'),
        'comment_others' => t('Users may only flag comments by others'),
        'node_own' => t('Users may only flag comments of nodes they own'),
        'node_others' => t('Users may only flag comments of nodes by others'),
      ),
      '#default_value' => $this->configuration['access_author'],
      '#description' => t("Restrict access to this flag based on the user's ownership of the content. Users must also have access to the flag through the role settings."),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    parent::submitConfigurationForm($form, $form_state);
    $this->configuration['access_author'] = $form_state['values']['access_author'];
  }

  /**
   * {@inheritdoc}
   */
  public function type_access_multiple($entity_ids, $account) {
    $access = array();

    // If all subtypes are allowed, we have nothing to say here.
    if (empty($this->types)) {
      return $access;
    }

    // Ensure node types are granted access. This avoids a
    // node_load() on every type, usually done by applies_to_entity_id().
    $query = db_select('comment', 'c');
    $query->innerJoin('node', 'n', 'c.nid = n.nid');
    $result = $query
      ->fields('c', array('cid'))
      ->condition('c.cid', $entity_ids, 'IN')
      ->condition('n.type', $this->types, 'NOT IN')
      ->execute();
    foreach ($result as $row) {
      $access[$row->nid] = FALSE;
    }

    return $access;
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessAuthorSetting() {
    return $this->configuration['access_author'];
  }
} 