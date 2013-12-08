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
 *   title = @Translation("Comment")
 * )
 */
class CommentFlagType extends EntityFlagType {

  public $access_author;

  public static function entityTypes() {
    return array(
      'comment' => array(
        'title' => t('Comments'),
        'description' => t('Comments are responses to node content.'),
      ),
    );
  }

  public function options() {
    $options = parent::options();
    $options += array(
      'access_author' => '',
    );
    return $options;
  }

  /**
   * Options form extras for comment flags.
   */
  public function options_form(&$form) {
    parent::options_form($form);

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
      '#default_value' => $this->access_author,
      '#description' => t("Restrict access to this flag based on the user's ownership of the content. Users must also have access to the flag through the role settings."),
    );
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