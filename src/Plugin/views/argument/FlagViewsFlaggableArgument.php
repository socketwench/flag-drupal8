<?php
/**
 * @file
 * Contains FlagViewsFlaggableArgument.
 */

namespace Drupal\flag\Plugin\views\argument;


use Drupal\views\Plugin\views\argument\Numeric;
use Drupal\Component\Utility\String;

/**
 * Class FlagViewsFlaggableArgument
 * @package Drupal\flag\Plugin\views\argument
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

  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $database) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->database = $database;
  }

  public function getFlag() {
    // When editing a view it's possible to delete the relationship (either by
    // error or to later recreate it), so we have to guard against a missing
    // one.
    if (isset($this->view->relationship[$this->options['relationship']])) {
      return $this->view->relationship[$this->options['relationship']]->getFlag();
    }
  }

  public function titleQuery() {
    $titles = array();

    $flag = $this->getFlag();
    $entityType = $flag->getFlaggableEntityType();

    $def = \Drupal::entityManager()->getDefinition($entityType);
    $baseTable = $def->getBaseTable();
    $entityKeys = $def->getKeys();
    $idKey = $entityKeys['id'];

    $result = $this->database->select($def->getBaseTable(), 'o')
      ->fields('o', $entityKeys['label'])
      ->condition('o.' . $entityKeys['id'], $this->value, 'IN')
      ->execute();


    foreach ($result as $title) {
      $titles[] = String::check_plain($title->$entityKeys['label']);
    }

    return $titles;
  }

} 