<?php
/**
 * @file
 * Contains \Drupal\flag\Plugin\views\argument\FlagViewsFlaggableArgument.
 */

namespace Drupal\flag\Plugin\views\argument;


use Drupal\views\Plugin\views\argument\Numeric;
use Drupal\Component\Utility\String;
use Drupal\Core\Database\Connection;
use Drupal\flag\FlagInterface;

/**
 * Provides an argument handler to get the title of flaggble content.
 *
 * @ViewsArgument("FlagViewsFlaggableArgument")
 */
class FlagViewsFlaggableArgument extends Numeric {

  /**
   * Database Service Object.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->database = $database;
  }

  /**
   * Helper method to retrieve the flag entity from the views relationship.
   *
   * @return FlagInterface|null
   *   The flag entity selected in the relationship.
   */
  public function getFlag() {
    // When editing a view it's possible to delete the relationship (either by
    // error or to later recreate it), so we have to guard against a missing
    // one.
    if (isset($this->view->relationship[$this->options['relationship']])) {
      return $this->view->relationship[$this->options['relationship']]->getFlag();
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function titleQuery() {
    $titles = [];

    $flag = $this->getFlag();
    $entity_type = $flag->getFlaggableEntityType();

    $def = \Drupal::entityManager()->getDefinition($entity_type);
    $entity_keys = $def->getKeys();

    $result = $this->database->select($def->getBaseTable(), 'o')
      ->fields('o', $entity_keys['label'])
      ->condition('o.' . $entity_keys['id'], $this->value, 'IN')
      ->execute();

    foreach ($result as $title) {
      $titles[] = String::checkPlain($title->$entity_keys['label']);
    }

    return $titles;
  }

}
