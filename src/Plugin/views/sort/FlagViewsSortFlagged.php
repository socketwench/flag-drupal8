<?php

/**
 * @file
 * Contains the flagged content sort handler.
 */

namespace Drupal\flag\Plugin\views\sort;

use Drupal\views\Plugin\views\sort\SortPluginBase;

/**
 * Class FlagViewsSortFlagged
 *
 * @ViewsSort("flag_sort")
 */
class FlagViewsSortFlagged extends SortPluginBase {

  /**
   * Provide a list of options for the default sort form.
   *
   * Should be overridden by classes that don't override sort_form.
   */
  function sortOptions() {
    return array(
      'ASC' => t('Unflagged first'),
      'DESC' => t('Flagged first'),
    );
  }

  /**
   * Display whether or not the sort order is ascending or descending
   */
  function adminSummary() {
    if (!empty($this->options['exposed'])) {
      return t('Exposed');
    }

    // Get the labels defined in sortOptions().
    $sort_options = $this->sortOptions();
    return $sort_options[strtoupper($this->options['order'])];
  }

  public function query() {
    $this->ensureMyTable();

    $this->query->addOrderBy(NULL, "$this->tableAlias.uid", $this->options['order']);
  }
}