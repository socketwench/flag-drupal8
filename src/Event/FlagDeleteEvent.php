<?php
/**
 * @file
 * Contains \Drupal\flag\Event\FlagDeleteEvent.
 */

namespace Drupal\flag\Event;

use Drupal\flag\FlagInterface;
use Drupal\flag\Event\FlagEventBase;

/**
 * Event to handle Deletion of Flag.
 */
class FlagDeleteEvent extends FlagEventBase {

  /**
   * Build the flag delete event.
   *
   * @param \Drupal\flag\FlagInterface $flag
   *   The flag to delete.
   */
  public function __construct(FlagInterface $flag) {
    parent::__construct($flag);
  }
}
