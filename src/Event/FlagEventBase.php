<?php
/**
 * @file
 * Contains \Drupal\flag\Event\FlagEventBase.
 */

namespace Drupal\flag\Event;

use Drupal\flag\FlagInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Base Event from which other flag event are defined.
 */

abstract class FlagEventBase extends Event {

  /**
   * The Flag in question.
   *
   * @var \Drupal\flag\FlagInterface
   */
  protected $flag;

  /**
   * Build the flag event class.
   *
   * @param \Drupal\flag\FlagInterface $flag
   *   The flag to be acted upon.
   */
  public function __construct(FlagInterface $flag) {
    $this->flag = $flag;
  }

}
