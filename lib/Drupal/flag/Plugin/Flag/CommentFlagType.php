<?php
/**
 * Created by PhpStorm.
 * User: tess
 * Date: 10/28/13
 * Time: 7:07 PM
 */

namespace Drupal\flag\Plugin\Flag;

use Drupal\flag\Plugin\Flag\EntityFlagType;

/**
 * Class CommentFlagType
 * @package Drupal\flag\Plugin\Flag
 *
 * Implements a comment flag.
 *
 * @FlagType(
 *   id = "flagtype_comment",
 *   title = @Translation("Comment"),
 *   entity_type = "comment"
 * )
 */
class CommentFlagType extends EntityFlagType {

  public function defaultConfiguration() {
    $options = parent::defaultConfiguration();
    $options += array(
      'access_author' => '',
    );
    return $options;
  }

  /**
   * Options form extras for comment flags.
   */
  public function buildConfigurationForm(array $form, array &$form_state) {
    parent::buildConfigurationForm($form, $form_state);

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

  public function submitConfigurationForm(array &$form, array &$form_state) {
    $this->configuration['access_author'] = $form_state['value']['access']['access_author'];
  }

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
} 